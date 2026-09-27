<?php
declare(strict_types=1);

final class AdminMenus
{
    public static function index(): void
    {
        Auth::requirePerm('menus.view');
        $menus = Database::all('SELECT * FROM menus ORDER BY id');
        $id = Request::int('id') ?: (int) ($menus[0]['id'] ?? 0);
        $items = $id ? Database::all('SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort_order, id', [$id]) : [];
        $pages = Database::all('SELECT id, title, slug FROM pages WHERE deleted_at IS NULL AND status = "published" ORDER BY title');
        $types = Cpt::publicTypes();
        $entries = Database::all(
            'SELECT e.id, e.title, t.slug AS type_slug, t.name AS type_name FROM cpt_entries e
             JOIN post_types t ON t.id = e.post_type_id
             WHERE e.deleted_at IS NULL AND e.status = "published" AND t.public = 1 ORDER BY t.name, e.title'
        );
        View::admin('menus/index', [
            'title' => 'Menus',
            'menus' => $menus,
            'menuId' => $id,
            'items' => $items,
            'pages' => $pages,
            'types' => $types,
            'entries' => $entries,
        ]);
    }

    public static function save(): void
    {
        Auth::requirePerm('menus.edit');
        $menuId = Request::int('menu_id');

        // Remove items the user deleted in the builder.
        foreach (($_POST['deleted'] ?? []) as $delId) {
            $delId = (int) $delId;
            if ($delId > 0) {
                Database::delete('menu_items', 'id = ? AND menu_id = ?', [$delId, $menuId]);
            }
        }

        $ids = $_POST['item_id'] ?? [];
        $labels = $_POST['label'] ?? [];
        $types = $_POST['link_type'] ?? [];
        $urls = $_POST['url'] ?? [];
        $oids = $_POST['object_id'] ?? [];
        $otypes = $_POST['object_type'] ?? [];
        $parents = $_POST['parent_id'] ?? [];
        $actives = $_POST['is_active'] ?? [];

        $defaultObjectType = static function (string $lt): ?string {
            return match ($lt) {
                'page' => 'page',
                'cpt_entry' => 'cpt',
                'cpt_archive' => 'post_type',
                'blog_post' => 'blog',
                default => null,
            };
        };

        // Submit order == visual order, so the array index is the sort order.
        foreach ($ids as $i => $iid) {
            $label = trim((string) ($labels[$i] ?? ''));
            if ($label === '') {
                continue; // skip empty rows
            }
            $lt = (string) ($types[$i] ?? 'custom');
            $ot = trim((string) ($otypes[$i] ?? '')) ?: $defaultObjectType($lt);
            $data = [
                'label' => $label,
                'url' => trim((string) ($urls[$i] ?? '')),
                'link_type' => $lt,
                'object_type' => $ot,
                'object_id' => ($oids[$i] ?? '') === '' ? null : (int) $oids[$i],
                'sort_order' => $i,
                'parent_id' => ($parents[$i] ?? '') === '' ? null : (int) $parents[$i],
                'is_active' => (int) ($actives[$i] ?? 1) === 1 ? 1 : 0,
            ];
            if ((int) $iid) {
                Database::update('menu_items', $data, 'id = ? AND menu_id = ?', [(int) $iid, $menuId]);
            } else {
                $data['menu_id'] = $menuId;
                Database::insert('menu_items', $data);
            }
        }

        Cache::flush();
        Audit::log('menu.updated', 'menu', $menuId);
        if (Request::wantsJson()) {
            View::json(['ok' => true]);
        }
        View::flash('success', 'Menu saved. Linked items follow slug changes automatically.');
        View::redirect('/admin/menus/?id=' . $menuId);
    }

    public static function itemDelete(): void
    {
        Auth::requirePerm('menus.edit');
        Database::delete('menu_items', 'id = ?', [Request::int('id')]);
        Cache::flush();
        View::redirect('/admin/menus/');
    }
}
