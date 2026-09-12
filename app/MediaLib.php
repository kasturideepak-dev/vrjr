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
        return self::storeResult($f, $folder)['row'] ?? null;
    }

    /** @return array{ok:bool,error?:string,row?:array} */
    public static function storeResult(array $f, string $folder = 'general'): array
    {
        $err = (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'error' => 'No file selected.'];
        }
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return ['ok' => false, 'error' => 'File is too large for the server limit.'];
        }
        if ($err !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed (code ' . $err . ').'];
        }
        $tmp = (string) ($f['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'Invalid upload.'];
        }
        $max = (int) app_config('uploads_max_bytes', 8 * 1024 * 1024);
        $size = (int) ($f['size'] ?? 0);
        if ($size < 1 || filesize($tmp) < 1) {
            return ['ok' => false, 'error' => 'Empty file.'];
        }
        if ($size > $max) {
            return ['ok' => false, 'error' => 'File exceeds ' . (int) round($max / 1048576) . ' MB.'];
        }
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file($tmp) ?: '';
        $map = self::allowedMimes();
        if (!isset($map[$mime])) {
            return ['ok' => false, 'error' => 'Only JPEG, PNG, WebP, GIF, or PDF files are allowed.'];
        }
        $ext = $map[$mime];
        $orig = basename((string) ($f['name'] ?? 'file'));
        $orig = preg_replace('/[^\w.\- ()]+/', '_', $orig) ?: ('file.' . $ext);
        $folder = preg_replace('/[^a-z0-9_-]+/i', '', $folder) ?: 'general';
        $dirRel = '/uploads/' . date('Y/m');
        $dir = ROOT . '/public' . $dirRel;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['ok' => false, 'error' => 'Could not create the uploads folder.'];
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            return ['ok' => false, 'error' => 'Could not save the file.'];
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
            'original_name' => $orig,
            'mime' => $mime,
            'extension' => $ext,
            'size_bytes' => (int) filesize($dest),
            'width' => $w,
            'height' => $h,
            'folder' => $folder,
            'uploaded_by' => Auth::id(),
        ]);
        $row = Database::one('SELECT * FROM media WHERE id = ?', [$id]);
        if (!$row) {
            return ['ok' => false, 'error' => 'Saved file but could not record it.'];
        }
        return ['ok' => true, 'row' => $row];
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
