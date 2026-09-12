<?php
declare(strict_types=1);

final class Menu
{
    public static function items(string $slug): array
    {
        $menu = Database::one('SELECT id FROM menus WHERE slug = ?', [$slug]);
        if (!$menu) {
            return [];
        }
        $rows = Database::all(
            'SELECT * FROM menu_items WHERE menu_id = ? AND is_active = 1 ORDER BY sort_order, id',
            [(int) $menu['id']]
        );
        foreach ($rows as &$row) {
            $row['url'] = self::resolve($row);
        }
        return $rows;
    }

    public static function resolve(array $item): string
    {
        $type = $item['link_type'] ?? 'custom';
        $oid = (int) ($item['object_id'] ?? 0);
        return match ($type) {
            'page' => self::pageUrl($oid) ?: ($item['url'] ?: '#'),
            'cpt_archive' => self::archiveUrl($oid) ?: ($item['url'] ?: '#'),
            'cpt_entry' => self::entryUrl($oid) ?: ($item['url'] ?: '#'),
            'blog_index' => path_url('/blog/'),
            'blog_post' => self::blogUrl($oid) ?: ($item['url'] ?: '#'),
            default => $item['url'] ?: '#',
        };
    }

    public static function pageUrl(int $id): ?string
    {
        $p = Database::one('SELECT slug FROM pages WHERE id = ? AND deleted_at IS NULL', [$id]);
        if (!$p) {
            return null;
        }
        return $p['slug'] === '/' ? '/' : path_url($p['slug']);
    }

    public static function archiveUrl(int $typeId): ?string
    {
        $t = Database::one('SELECT slug, public, has_archive FROM post_types WHERE id = ?', [$typeId]);
        if (!$t || !(int) $t['public']) {
            return null;
        }
        return path_url($t['slug']);
    }

    public static function entryUrl(int $id): ?string
    {
        $e = Database::one(
            'SELECT e.slug, t.slug AS type_slug, t.public FROM cpt_entries e
             JOIN post_types t ON t.id = e.post_type_id WHERE e.id = ? AND e.deleted_at IS NULL',
            [$id]
        );
        if (!$e || !(int) $e['public']) {
            return null;
        }
        return path_url($e['type_slug'] . '/' . $e['slug']);
    }

    public static function blogUrl(int $id): ?string
    {
        $p = Database::one('SELECT slug FROM blog_posts WHERE id = ? AND deleted_at IS NULL', [$id]);
        return $p ? path_url('blog/' . $p['slug']) : null;
    }

    public static function linksTo(string $linkType, string $objectType, int $objectId): array
    {
        return Database::all(
            'SELECT mi.*, m.name AS menu_name FROM menu_items mi
             JOIN menus m ON m.id = mi.menu_id
             WHERE mi.link_type = ? AND (mi.object_type = ? OR mi.object_type IS NULL) AND mi.object_id = ?',
            [$linkType, $objectType, $objectId]
        );
    }
}
