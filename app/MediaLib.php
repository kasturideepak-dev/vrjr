<?php
declare(strict_types=1);

final class MediaLib
{
    public static function allowedMimes(): array
    {
        return [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
        ];
    }

    public static function store(array $f, string $folder = 'general'): ?array
    {
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $max = (int) app_config('uploads_max_bytes', 8 * 1024 * 1024);
        if (($f['size'] ?? 0) > $max) {
            return null;
        }
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file($f['tmp_name']) ?: '';
        $map = self::allowedMimes();
        if (!isset($map[$mime])) {
            return null;
        }
        $ext = $map[$mime];
        $dirRel = '/uploads/' . date('Y/m');
        $dir = ROOT . '/public' . $dirRel;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            return null;
        }
        if (str_starts_with($mime, 'image/')) {
            ImageOptimizer::optimize($dest, $mime);
        }
        $w = $h = null;
        if (str_starts_with($mime, 'image/')) {
            $info = @getimagesize($dest);
            if ($info) {
                $w = $info[0];
                $h = $info[1];
            }
        }
        $id = Database::insert('media', [
            'disk_path' => $dest,
            'public_path' => $dirRel . '/' . $name,
            'original_name' => $f['name'],
            'mime' => $mime,
            'extension' => $ext,
            'size_bytes' => (int) filesize($dest),
            'width' => $w,
            'height' => $h,
            'folder' => $folder,
            'uploaded_by' => Auth::id(),
        ]);
        return Database::one('SELECT * FROM media WHERE id = ?', [$id]);
    }

    public static function normalizeFiles(array $files): array
    {
        $out = [];
        if (!isset($files['name'])) {
            return $out;
        }
        if (is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                $out[] = [
                    'name' => $name,
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
            }
        } else {
            $out[] = $files;
        }
        return $out;
    }

    public static function delete(int $id): void
    {
        $row = Database::one('SELECT * FROM media WHERE id = ?', [$id]);
        if (!$row) {
            return;
        }
        $uploads = realpath(ROOT . '/public/uploads') ?: '';
        $full = realpath($row['disk_path']) ?: (is_file(ROOT . '/public' . $row['public_path']) ? realpath(ROOT . '/public' . $row['public_path']) : '');
        if ($full && $uploads && str_starts_with($full, $uploads)) {
            @unlink($full);
            $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $full);
            if ($webp && is_file($webp)) {
                @unlink($webp);
            }
        }
        Database::delete('media', 'id = ?', [$id]);
    }

    public static function usageCount(int $id): int
    {
        $row = Database::one('SELECT COUNT(*) c FROM media_usage WHERE media_id = ?', [$id]);
        return (int) ($row['c'] ?? 0);
    }
}
