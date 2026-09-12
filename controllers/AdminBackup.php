<?php
declare(strict_types=1);

final class AdminBackup
{
    public static function index(): void
    {
        Auth::requirePerm('backups.view');
        View::admin('backup/index', [
            'title' => 'Backups',
            'rows' => Database::all('SELECT b.*, u.name AS user_name FROM backups b LEFT JOIN users u ON u.id = b.created_by ORDER BY b.id DESC LIMIT 50'),
            's' => Settings::all(),
            'lastCron' => Database::one('SELECT * FROM cron_runs ORDER BY id DESC LIMIT 1'),
        ]);
    }

    public static function create(): void
    {
        Auth::requirePerm('backups.create');
        $row = Backup::create('manual', Request::str('notes'));
        View::flash('success', 'Backup created: ' . ($row['filename'] ?? ''));
        View::redirect('/admin/backup/');
    }

    public static function download(): void
    {
        Auth::requirePerm('backups.view');
        $row = Database::one('SELECT * FROM backups WHERE id = ?', [Request::int('id')]);
        if (!$row || !is_file($row['disk_path'])) {
            View::flash('error', 'File missing.');
            View::redirect('/admin/backup/');
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $row['filename'] . '"');
        header('Content-Length: ' . filesize($row['disk_path']));
        readfile($row['disk_path']);
        exit;
    }

    public static function restore(): void
    {
        Auth::requirePerm('backups.restore');
        if (Request::str('confirm') !== 'RESTORE') {
            View::flash('error', 'Type RESTORE to confirm.');
            View::redirect('/admin/backup/');
        }
        Backup::restore(Request::int('id'));
        View::flash('success', 'Backup restored. A safety copy was made first.');
        View::redirect('/admin/backup/');
    }
}
