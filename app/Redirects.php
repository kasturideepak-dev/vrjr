<?php
declare(strict_types=1);

final class Redirects
{
    public static function normalize(string $path): string
    {
        $path = strtolower(trim($path));
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $p = parse_url($path, PHP_URL_PATH) ?: '/';
            $path = $p;
        }
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : ltrim($path, '/');
    }

    public static function find(string $lookup): ?array
    {
        $lookup = self::normalize($lookup);
        return Database::one('SELECT * FROM redirects WHERE is_active = 1 AND from_path = ?', [$lookup]);
    }

    public static function save(string $from, string $to, int $code = 301, string $note = ''): void
    {
        $from = self::normalize($from);
        $to = trim($to);
        if ($from === '' || $from === ltrim($to, '/')) {
            return;
        }
        $exists = Database::one('SELECT id FROM redirects WHERE from_path = ?', [$from]);
        if ($exists) {
            Database::update('redirects', [
                'to_path' => $to,
                'status_code' => $code,
                'is_active' => 1,
                'note' => $note !== '' ? $note : null,
            ], 'id = ?', [(int) $exists['id']]);
            return;
        }
        Database::insert('redirects', [
            'from_path' => $from,
            'to_path' => $to,
            'status_code' => $code,
            'is_active' => 1,
            'note' => $note !== '' ? $note : null,
        ]);
    }

    public static function onSlugChange(string $oldPath, string $newPath, string $note = 'Slug change'): void
    {
        $old = self::normalize($oldPath);
        $new = path_url($newPath);
        if ($old === '' || $old === ltrim($new, '/')) {
            return;
        }
        self::save($old, $new, 301, $note);
        Database::query(
            'UPDATE redirects SET to_path = ? WHERE to_path = ? OR to_path = ?',
            [$new, '/' . $old, '/' . $old . '/']
        );
    }

    public static function hit(int $id): void
    {
        Database::query('UPDATE redirects SET hits = hits + 1 WHERE id = ?', [$id]);
    }
}
