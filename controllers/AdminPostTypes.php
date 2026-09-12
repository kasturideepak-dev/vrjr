<?php
declare(strict_types=1);

final class AdminPostTypes
{
    public static function index(): void
    {
        Auth::requirePerm('post_types.view');
        Cpt::ensureSchema();
        $rows = Database::all(
            'SELECT t.*, (SELECT COUNT(*) FROM cpt_entries e WHERE e.post_type_id = t.id AND e.deleted_at IS NULL) AS entry_count
             FROM post_types t ORDER BY t.sort_order, t.name'
        );
        View::admin('post-types/index', ['title' => 'Post types', 'rows' => $rows]);
    }

    public static function form(?string $id = null): void
    {
        Cpt::ensureSchema();
        $row = $id ? Database::one('SELECT * FROM post_types WHERE id = ?', [(int) $id]) : null;
        Auth::requirePerm($row ? 'post_types.edit' : 'post_types.create');
        $fields = $row ? Cpt::fields((int) $row['id']) : [];
        View::admin('post-types/form', [
            'title' => $row ? ('Edit post type: ' . $row['name']) : 'Add new post type',
            'row' => $row,
            'fields' => $fields,
            'fieldTypes' => Fields::types(),
        ]);
    }

    public static function save(): void
    {
        $id = Request::int('id');
        Cpt::ensureSchema();
        Auth::requirePerm($id ? 'post_types.edit' : 'post_types.create');
        $name = Request::str('name');
        $singular = Request::str('singular_name') ?: $name;
        $slugIn = Request::str('slug') ?: $name;
        if (Slug::isReserved($slugIn)) {
            self::fail('That slug is a reserved system route.');
        }
        $old = $id ? Database::one('SELECT * FROM post_types WHERE id = ?', [$id]) : null;
        $slug = Slug::uniquePostType($slugIn, $id ?: null);
        if (Slug::firstSegmentTaken($slug, ['post_type_id' => $id ?: 0]) && Request::bool('public')) {
            $page = Database::one('SELECT id FROM pages WHERE deleted_at IS NULL AND slug = ?', [$slug]);
            if ($page) {
                self::fail('A page already uses /' . $slug . '/. Choose a different archive slug.');
            }
        }
        $data = [
            'name' => $name,
            'singular_name' => $singular,
            'slug' => $slug,
            'description' => Request::str('description'),
            'has_archive' => Request::bool('has_archive') ? 1 : 0,
            'public' => Request::bool('public') ? 1 : 0,
            'template_mode' => in_array(Request::str('template_mode'), ['fields', 'builder', 'both'], true) ? Request::str('template_mode') : 'both',
            'archive_title' => Request::str('archive_title') ?: $name,
            'archive_intro' => Request::str('archive_intro'),
            'supports_featured_image' => Request::bool('supports_featured_image') ? 1 : 0,
            'supports_excerpt' => Request::bool('supports_excerpt') ? 1 : 0,
            'supports_editor' => Request::bool('supports_editor') ? 1 : 0,
            'sort_order' => Request::int('sort_order'),
        ];
        if ($old) {
            $posted = Request::str('status');
            if ($posted === 'inactive' || $posted === 'active' || $posted === 'archived') {
                $data['status'] = $posted;
            }
        } else {
            $data['status'] = 'active';
            $data['is_system'] = 0;
        }
        if ($id) {
            Database::update('post_types', $data, 'id = ?', [$id]);
            if ($old && $old['slug'] !== $slug) {
                Redirects::onSlugChange($old['slug'], $slug, 'Post type prefix change');
                foreach (Database::all('SELECT slug FROM cpt_entries WHERE post_type_id = ? AND deleted_at IS NULL AND status = "published"', [$id]) as $e) {
                    Redirects::onSlugChange($old['slug'] . '/' . $e['slug'], $slug . '/' . $e['slug'], 'Post type prefix change');
                }
            }
            Audit::log('post_type.updated', 'post_type', $id);
        } else {
            $id = Database::insert('post_types', $data);
            Database::insert('seo_metadata', [
                'entity_type' => 'post_type',
                'entity_id' => $id,
                'seo_title' => $name . ' | VR Junior College',
                'canonical_url' => url($slug),
                'robots' => 'index,follow',
            ]);
            Audit::log('post_type.created', 'post_type', $id);
        }
        self::saveFieldDefs($id);
        Cache::flush();
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'id' => $id, 'slug' => $slug, 'redirect' => '/admin/post-types/' . $id . '/']);
        }
        View::flash('success', 'Post type saved. Staff can add entries from Content → ' . $name . '.');
        View::redirect('/admin/post-types/' . $id . '/');
    }

    private static function saveFieldDefs(int $typeId): void
    {
        $names = $_POST['field_name'] ?? [];
        $labels = $_POST['field_label'] ?? [];
        $types = $_POST['field_type'] ?? [];
        $req = $_POST['field_required'] ?? [];
        $help = $_POST['field_help'] ?? [];
        $opts = $_POST['field_options'] ?? [];
        $ids = $_POST['field_id'] ?? [];
        $keep = [];
        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            $fname = Slug::make((string) ($names[$i] ?? $label));
            $fname = preg_replace('/[^a-z0-9_]+/', '_', $fname) ?: 'field';
            $row = [
                'post_type_id' => $typeId,
                'name' => $fname,
                'label' => $label,
                'type' => (string) ($types[$i] ?? 'text'),
                'is_required' => ((string) ($req[$i] ?? '0') === '1') ? 1 : 0,
                'help_text' => trim((string) ($help[$i] ?? '')),
                'options_json' => self::optionsJson((string) ($opts[$i] ?? ''), (string) ($types[$i] ?? 'text')),
                'sort_order' => (int) $i,
            ];
            $fid = (int) ($ids[$i] ?? 0);
            if ($fid) {
                Database::update('post_type_fields', $row, 'id = ? AND post_type_id = ?', [$fid, $typeId]);
                $keep[] = $fid;
            } else {
                $keep[] = Database::insert('post_type_fields', $row);
            }
        }
        $existing = Database::all('SELECT id FROM post_type_fields WHERE post_type_id = ?', [$typeId]);
        foreach ($existing as $e) {
            if (!in_array((int) $e['id'], $keep, true)) {
                Database::delete('post_type_fields', 'id = ?', [(int) $e['id']]);
            }
        }
    }

    private static function optionsJson(string $raw, string $type): ?string
    {
        $raw = trim($raw);
        if ($type === 'select') {
            $opts = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $raw) ?: [])));
            return Html::json(['choices' => $opts]);
        }
        if ($type === 'repeater') {
            $subs = [];
            foreach (preg_split("/\r\n|\n|\r/", $raw) ?: [] as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('|', $line));
                $subs[] = [
                    'name' => Slug::make($parts[0] ?? 'item'),
                    'label' => $parts[0] ?? 'Item',
                    'type' => $parts[1] ?? 'text',
                ];
            }
            return Html::json(['subfields' => $subs]);
        }
        return $raw !== '' ? Html::json(['raw' => $raw]) : null;
    }

    public static function confirmDelete(string $id): void
    {
        Auth::requirePerm('post_types.delete');
        Cpt::ensureSchema();
        $type = Database::one('SELECT * FROM post_types WHERE id = ?', [(int) $id]);
        if (!$type) {
            View::flash('error', 'Post type not found.');
            View::redirect('/admin/post-types/');
        }
        $entries = Cpt::entries((int) $type['id']);
        $refs = Cpt::references($type);
        $pages = Database::all(
            'SELECT id, title, slug FROM pages WHERE deleted_at IS NULL AND status = "published" ORDER BY title'
        );
        View::admin('post-types/confirm-delete', [
            'title' => 'Delete ' . $type['name'],
            'type' => $type,
            'entries' => $entries,
            'refs' => $refs,
            'pages' => $pages,
            'isSystem' => Cpt::isSystem($type),
        ]);
    }

    public static function delete(): void
    {
        Auth::requirePerm('post_types.delete');
        Cpt::ensureSchema();
        $id = Request::int('id');
        $t = Database::one('SELECT * FROM post_types WHERE id = ?', [$id]);
        if (!$t) {
            self::fail('Post type not found.');
        }
        if (Cpt::isSystem($t)) {
            self::forbidSystem($t);
        }
        $entries = Cpt::entries($id);
        $count = count($entries);
        $refs = Cpt::references($t);
        if (!empty($refs['header_blocking'])) {
            View::flash('error', 'Remove “' . $t['name'] . '” from the header menu before permanently deleting it.');
            View::redirect('/admin/post-types/' . $id . '/confirm-delete/');
        }
        if ($count > 0) {
            $typed = trim((string) ($_POST['confirm_name'] ?? ''));
            if (strcasecmp($typed, (string) $t['name']) !== 0) {
                View::flash('error', 'Type the post type name (“' . $t['name'] . '”) to confirm permanent deletion.');
                View::redirect('/admin/post-types/' . $id . '/confirm-delete/');
            }
        }
        $to = Request::str('redirect_to') ?: '/';
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $result = Cpt::purge($t, $to);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('post_type.delete: ' . $e->getMessage());
            self::fail('Could not delete that post type. Nothing was removed.');
        }
        Audit::log('post_type.deleted', 'post_type', $id, [
            'name' => $t['name'],
            'slug' => $t['slug'],
            'entries' => $result['entries'],
            'redirect_to' => $to,
        ]);
        View::flash('success', 'Deleted “' . $t['name'] . '” and ' . (int) $result['entries'] . ' ' . ((int) $result['entries'] === 1 ? 'entry' : 'entries') . '. Old URLs redirect to ' . $to . '.');
        View::redirect('/admin/post-types/');
    }

    public static function archive(): void
    {
        Auth::requirePerm('post_types.edit');
        Cpt::ensureSchema();
        $id = Request::int('id');
        $t = Database::one('SELECT * FROM post_types WHERE id = ?', [$id]);
        if (!$t) {
            self::fail('Post type not found.');
        }
        if (Cpt::isSystem($t)) {
            self::forbidSystem($t);
        }
        Cpt::archive($t);
        $n = count(Cpt::entries($id));
        Audit::log('post_type.archived', 'post_type', $id, [
            'name' => $t['name'],
            'slug' => $t['slug'],
            'entries' => $n,
        ]);
        View::flash('success', 'Archived “' . $t['name'] . '”. It is hidden from the site and can be restored from this list.');
        View::redirect('/admin/post-types/');
    }

    public static function restore(): void
    {
        Auth::requirePerm('post_types.edit');
        Cpt::ensureSchema();
        $id = Request::int('id');
        $t = Database::one('SELECT * FROM post_types WHERE id = ?', [$id]);
        if (!$t) {
            self::fail('Post type not found.');
        }
        Cpt::restore($t);
        Audit::log('post_type.restored', 'post_type', $id, [
            'name' => $t['name'],
            'slug' => $t['slug'],
        ]);
        View::flash('success', 'Restored “' . $t['name'] . '”. Entries and public URLs are available again.');
        View::redirect('/admin/post-types/');
    }

    private static function forbidSystem(array $t): never
    {
        $msg = '“' . $t['name'] . '” is a system post type and cannot be deleted or archived.';
        if (Request::wantsJson()) {
            View::json(['ok' => false, 'error' => $msg], 403);
        }
        http_response_code(403);
        View::admin('errors/403', ['title' => 'Forbidden']);
        exit;
    }

    private static function fail(string $msg): never
    {
        if (Request::wantsJson()) {
            View::json(['ok' => false, 'error' => $msg], 422);
        }
        View::flash('error', $msg);
        View::redirect('/admin/post-types/');
    }
}
