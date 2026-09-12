<?php
declare(strict_types=1);

final class Slug
{
    public static function make(string $text): string
    {
        $s = strtolower(trim($text));
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
        $s = trim($s, '-');
        return $s !== '' ? $s : 'item';
    }

    public static function normalizePath(string $path): string
    {
        $path = strtolower(trim($path));
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : $path . '/';
    }

    public static function isReserved(string $slug): bool
    {
        $slug = strtolower(trim($slug, '/'));
        if ($slug === '') {
            return false;
        }
        $app = app_config();
        if (in_array($slug, $app['reserved_slugs'], true)) {
            return true;
        }
        return false;
    }

    /** First URL segment collisions: pages, public CPT archives, reserved. */
    public static function reservedRoutes(): array
    {
        $list = app_config('reserved_slugs', []);
        foreach (Database::all('SELECT slug FROM pages WHERE deleted_at IS NULL') as $p) {
            $s = trim($p['slug'], '/');
            if ($s !== '' && $s !== '/') {
                $list[] = explode('/', $s)[0];
            }
        }
        foreach (Database::all('SELECT slug FROM post_types WHERE status = "active" AND public = 1') as $t) {
            $list[] = $t['slug'];
        }
        return array_values(array_unique($list));
    }

    public static function firstSegmentTaken(string $slug, array $except = []): bool
    {
        $slug = strtolower(trim($slug, '/'));
        if ($slug === '' || $slug === '/') {
            return false;
        }
        $first = explode('/', $slug)[0];
        if (self::isReserved($first) && empty($except['allow_reserved'])) {
            return true;
        }
        $pageExcept = $except['page_id'] ?? 0;
        $sql = 'SELECT id FROM pages WHERE deleted_at IS NULL AND (slug = ? OR slug = ?)';
        $params = [$first, $first . '/'];
        if ($pageExcept) {
            $sql .= ' AND id <> ?';
            $params[] = $pageExcept;
        }
        if (Database::one($sql, $params)) {
            return true;
        }
        $typeExcept = $except['post_type_id'] ?? 0;
        $tsql = 'SELECT id FROM post_types WHERE status = "active" AND public = 1 AND slug = ?';
        $tparams = [$first];
        if ($typeExcept) {
            $tsql .= ' AND id <> ?';
            $tparams[] = $typeExcept;
        }
        return (bool) Database::one($tsql, $tparams);
    }

    public static function uniqueInTable(string $table, string $slug, ?int $ignoreId = null, string $idCol = 'id'): string
    {
        $base = self::make($slug);
        $try = $base;
        $i = 2;
        while (true) {
            $sql = "SELECT `$idCol` FROM `$table` WHERE slug = ?";
            $params = [$try];
            if ($ignoreId) {
                $sql .= " AND `$idCol` <> ?";
                $params[] = $ignoreId;
            }
            if (!Database::one($sql, $params)) {
                return $try;
            }
            $try = $base . '-' . $i;
            $i++;
        }
    }

    public static function uniquePage(string $slug, ?int $ignoreId = null): string
    {
        if ($slug === '/') {
            return '/';
        }
        $base = self::make($slug);
        $try = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM pages WHERE slug = ? AND deleted_at IS NULL';
            $params = [$try];
            if ($ignoreId) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            $taken = Database::one($sql, $params) || self::firstSegmentTaken($try, ['page_id' => $ignoreId ?? 0, 'allow_reserved' => false]);
            if (!$taken) {
                return $try;
            }
            $try = $base . '-' . $i;
            $i++;
        }
    }

    public static function uniqueEntry(int $typeId, string $slug, ?int $ignoreId = null): string
    {
        $base = self::make($slug);
        $try = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT id FROM cpt_entries WHERE post_type_id = ? AND slug = ? AND deleted_at IS NULL';
            $params = [$typeId, $try];
            if ($ignoreId) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            if (!Database::one($sql, $params)) {
                return $try;
            }
            $try = $base . '-' . $i;
            $i++;
        }
    }

    public static function uniquePostType(string $slug, ?int $ignoreId = null): string
    {
        $base = self::make($slug);
        $try = $base;
        $i = 2;
        while (true) {
            if (self::isReserved($try)) {
                $try = $base . '-' . $i;
                $i++;
                continue;
            }
            $sql = 'SELECT id FROM post_types WHERE slug = ?';
            $params = [$try];
            if ($ignoreId) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            $page = Database::one('SELECT id FROM pages WHERE deleted_at IS NULL AND slug = ?', [$try]);
            if (!Database::one($sql, $params) && !$page) {
                return $try;
            }
            $try = $base . '-' . $i;
            $i++;
        }
    }
}
