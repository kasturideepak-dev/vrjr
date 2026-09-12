<?php
declare(strict_types=1);

final class AdminMedia
{
    public static function index(): void
    {
        Auth::requirePerm('media.view');
        $q = Request::str('q');
        $sql = 'SELECT * FROM media';
        $params = [];
        if ($q !== '') {
            $sql .= ' WHERE original_name LIKE ? OR alt_text LIKE ? OR title LIKE ?';
            $params = ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%'];
        }
        $sql .= ' ORDER BY id DESC LIMIT 300';
        $rows = Database::all($sql, $params);
        foreach ($rows as &$r) {
            $r['usage'] = MediaLib::usageCount((int) $r['id']);
        }
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'rows' => $rows]);
        }
        View::admin('media/index', ['title' => 'Media library', 'rows' => $rows, 'q' => $q]);
    }

    public static function upload(): void
    {
        Auth::requirePerm('media.upload');
        if (empty($_FILES['files'])) {
            if (Request::wantsJson()) {
                View::json(['ok' => false, 'error' => 'No files'], 422);
            }
            View::flash('error', 'No files received.');
            View::redirect('/admin/media/');
        }
        $stored = [];
        foreach (MediaLib::normalizeFiles($_FILES['files']) as $f) {
            $row = MediaLib::store($f, Request::str('folder') ?: 'general');
            if ($row) {
                $stored[] = $row;
            }
        }
        Audit::log('media.uploaded', 'media', 0, ['count' => count($stored)]);
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'files' => $stored]);
        }
        View::flash('success', count($stored) . ' file(s) uploaded.');
        View::redirect('/admin/media/');
    }

    public static function update(): void
    {
        Auth::requirePerm('media.upload');
        $id = Request::int('id');
        Database::update('media', [
            'alt_text' => Request::str('alt_text'),
            'title' => Request::str('title'),
            'caption' => Request::str('caption'),
        ], 'id = ?', [$id]);
        if (Request::wantsJson()) {
            View::json(['ok' => true]);
        }
        View::flash('success', 'Media updated.');
        View::redirect('/admin/media/');
    }

    public static function delete(): void
    {
        Auth::requirePerm('media.delete');
        $ids = $_POST['ids'] ?? [Request::int('id')];
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $id) {
            MediaLib::delete((int) $id);
            Audit::log('media.deleted', 'media', (int) $id);
        }
        if (Request::wantsJson()) {
            View::json(['ok' => true]);
        }
        View::flash('success', 'File(s) removed.');
        View::redirect('/admin/media/');
    }
}
