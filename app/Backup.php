<?php
declare(strict_types=1);

final class Backup
{
    public static function dir(): string
    {
        $d = ROOT . '/storage/backups';
        if (!is_dir($d)) {
            mkdir($d, 0775, true);
        }
        return $d;
    }

    public static function create(string $kind = 'manual', string $notes = ''): array
    {
        $stamp = date('Ymd-His');
        $name = 'vrj-backup-' . $stamp . '.zip';
        $zipPath = self::dir() . '/' . $name;
        $sqlPath = self::dir() . '/dump-' . $stamp . '.sql';
        self::dumpSql($sqlPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Could not create zip archive');
        }
        $zip->addFile($sqlPath, 'database.sql');
        self::addDir($zip, ROOT . '/public/uploads', 'uploads');
        $zip->close();
        @unlink($sqlPath);

        $size = (int) filesize($zipPath);
        $id = Database::insert('backups', [
            'filename' => $name,
            'disk_path' => $zipPath,
            'size_bytes' => $size,
            'kind' => $kind,
            'notes' => $notes !== '' ? $notes : null,
            'created_by' => Auth::id(),
        ]);
        Audit::log('backup.created', 'backup', $id, ['file' => $name, 'size' => $size]);
        self::prune();
        return Database::one('SELECT * FROM backups WHERE id = ?', [$id]) ?: [];
    }

    public static function restore(int $id): void
    {
        $row = Database::one('SELECT * FROM backups WHERE id = ?', [$id]);
        if (!$row || !is_file($row['disk_path'])) {
            throw new RuntimeException('Backup file missing');
        }
        self::create('pre-restore', 'Automatic safety copy before restore #' . $id);

        $tmp = ROOT . '/storage/tmp/restore-' . bin2hex(random_bytes(4));
        mkdir($tmp, 0775, true);
        $zip = new ZipArchive();
        if ($zip->open($row['disk_path']) !== true) {
            throw new RuntimeException('Could not open archive');
        }
        $zip->extractTo($tmp);
        $zip->close();

        $sqlFile = $tmp . '/database.sql';
        if (!is_file($sqlFile)) {
            throw new RuntimeException('Archive has no database.sql');
        }
        self::importSql(file_get_contents($sqlFile) ?: '');

        $upFrom = $tmp . '/uploads';
        $upTo = ROOT . '/public/uploads';
        if (is_dir($upFrom)) {
            self::copyDir($upFrom, $upTo);
        }
        self::rmDir($tmp);
        Cache::flush();
        Audit::log('backup.restored', 'backup', $id);
    }

    public static function prune(): void
    {
        $keep = (int) Settings::get('backup_retention', '14');
        if ($keep < 1) {
            $keep = 14;
        }
        $rows = Database::all('SELECT * FROM backups WHERE kind <> "pre-restore" ORDER BY id DESC');
        $i = 0;
        foreach ($rows as $r) {
            $i++;
            if ($i > $keep) {
                if (is_file($r['disk_path'])) {
                    @unlink($r['disk_path']);
                }
                Database::delete('backups', 'id = ?', [(int) $r['id']]);
            }
        }
        $old = Database::all('SELECT * FROM backups WHERE kind = "pre-restore" AND created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)');
        foreach ($old as $r) {
            if (is_file($r['disk_path'])) {
                @unlink($r['disk_path']);
            }
            Database::delete('backups', 'id = ?', [(int) $r['id']]);
        }
    }

    public static function dumpSql(string $path): void
    {
        $pdo = Database::pdo();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
        $out = "-- VRJ CMS dump " . date('c') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n";
        foreach ($tables as $t) {
            $name = $t[0];
            if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
                continue;
            }
            $create = $pdo->query('SHOW CREATE TABLE `' . $name . '`')->fetch();
            $out .= "\nDROP TABLE IF EXISTS `$name`;\n" . $create['Create Table'] . ";\n";
            $rows = $pdo->query('SELECT * FROM `' . $name . '`');
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $cols = array_map(fn ($c) => '`' . $c . '`', array_keys($row));
                $vals = [];
                foreach ($row as $v) {
                    if ($v === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = $pdo->quote((string) $v);
                    }
                }
                $out .= 'INSERT INTO `' . $name . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n";
            }
        }
        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($path, $out);
    }

    private static function importSql(string $sql): void
    {
        $pdo = Database::pdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $st = '';
        foreach (preg_split("/\n/", $sql) ?: [] as $line) {
            $trim = ltrim($line);
            if ($trim === '' || str_starts_with($trim, '--')) {
                continue;
            }
            $st .= $line . "\n";
            if (str_ends_with(rtrim($line), ';')) {
                $pdo->exec($st);
                $st = '';
            }
        }
        if (trim($st) !== '') {
            $pdo->exec($st);
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    private static function addDir(ZipArchive $zip, string $dir, string $local): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $full = $file->getPathname();
            $rel = $local . '/' . ltrim(str_replace($dir, '', $full), '/');
            $zip->addFile($full, $rel);
        }
    }

    private static function copyDir(string $from, string $to): void
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($it as $file) {
            $rel = ltrim(str_replace($from, '', $file->getPathname()), '/');
            $dest = $to . '/' . $rel;
            if ($file->isDir()) {
                if (!is_dir($dest)) {
                    mkdir($dest, 0775, true);
                }
            } else {
                if (!is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0775, true);
                }
                copy($file->getPathname(), $dest);
            }
        }
    }

    private static function rmDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }
        @rmdir($dir);
    }
}
