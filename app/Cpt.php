<?php
declare(strict_types=1);

final class Cpt
{
    public static function activeTypes(): array
    {
        return Database::all('SELECT * FROM post_types WHERE status = "active" ORDER BY sort_order, name');
    }

    public static function publicTypes(): array
    {
        return Database::all('SELECT * FROM post_types WHERE status = "active" AND public = 1 ORDER BY sort_order, name');
    }

    public static function type(string $slug): ?array
    {
        return Database::one('SELECT * FROM post_types WHERE slug = ? AND status = "active"', [$slug]);
    }

    public static function typeById(int $id): ?array
    {
        return Database::one('SELECT * FROM post_types WHERE id = ?', [$id]);
    }

    public static function fields(int $typeId): array
    {
        return Database::all('SELECT * FROM post_type_fields WHERE post_type_id = ? ORDER BY sort_order, id', [$typeId]);
    }

    public static function published(string $typeSlug): array
    {
        $type = self::type($typeSlug);
        if (!$type) {
            return [];
        }
        $rows = Database::all(
            'SELECT * FROM cpt_entries WHERE post_type_id = ? AND status = "published" AND deleted_at IS NULL
             ORDER BY sort_order, id',
            [(int) $type['id']]
        );
        foreach ($rows as &$r) {
            $r['_type'] = $type;
            $r['_fields'] = json_decode($r['fields_json'] ?: '{}', true) ?: [];
        }
        return $rows;
    }

    public static function entry(string $typeSlug, string $entrySlug): ?array
    {
        $type = self::type($typeSlug);
        if (!$type) {
            return null;
        }
        $row = Database::one(
            'SELECT * FROM cpt_entries WHERE post_type_id = ? AND slug = ? AND deleted_at IS NULL',
            [(int) $type['id'], $entrySlug]
        );
        if (!$row) {
            return null;
        }
        $row['_type'] = $type;
        $row['_fields'] = json_decode($row['fields_json'] ?: '{}', true) ?: [];
        return $row;
    }

    public static function field(array $entry, string $name, mixed $default = ''): mixed
    {
        $fields = $entry['_fields'] ?? (json_decode($entry['fields_json'] ?? '{}', true) ?: []);
        return $fields[$name] ?? $default;
    }

    public static function permalink(array $entry, ?array $type = null): string
    {
        $type = $type ?? ($entry['_type'] ?? self::typeById((int) $entry['post_type_id']));
        if (!$type || !(int) $type['public']) {
            return '#';
        }
        return path_url($type['slug'] . '/' . $entry['slug']);
    }

    public static function archiveUrl(array $type): string
    {
        return path_url($type['slug']);
    }

    public static function decode(array $row): array
    {
        $row['_fields'] = json_decode($row['fields_json'] ?: '{}', true) ?: [];
        return $row;
    }

    public static function saveFieldsFromRequest(array $fieldDefs): array
    {
        $out = [];
        foreach ($fieldDefs as $f) {
            $name = $f['name'];
            $type = $f['type'];
            if ($type === 'checkbox') {
                $out[$name] = Request::bool('f_' . $name) ? '1' : '0';
                continue;
            }
            if ($type === 'repeater') {
                $raw = $_POST['f_' . $name] ?? [];
                $out[$name] = is_array($raw) ? array_values($raw) : [];
                continue;
            }
            if ($type === 'gallery') {
                $raw = Request::str('f_' . $name);
                $out[$name] = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $raw) ?: [])));
                continue;
            }
            if ($type === 'richtext') {
                $out[$name] = Html::allowedHtml((string) ($_POST['f_' . $name] ?? ''));
                continue;
            }
            $out[$name] = Request::str('f_' . $name);
        }
        return $out;
    }

    public static function validateRequired(array $fieldDefs, array $values): array
    {
        $errors = [];
        foreach ($fieldDefs as $f) {
            if (!(int) $f['is_required']) {
                continue;
            }
            $v = $values[$f['name']] ?? '';
            $empty = $v === '' || $v === [] || $v === null;
            if ($empty) {
                $errors[] = $f['label'] . ' is required.';
            }
        }
        return $errors;
    }

    /** Slugs that ship with the site and must never be deleted. */
    public static function systemSlugs(): array
    {
        return ['courses', 'campus', 'faculty'];
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $col = Database::all("SHOW COLUMNS FROM post_types LIKE 'is_system'");
            if (!$col) {
                Database::pdo()->exec(
                    'ALTER TABLE post_types ADD COLUMN is_system TINYINT(1) NOT NULL DEFAULT 0 AFTER status'
                );
            }
            $in = implode(',', array_fill(0, count(self::systemSlugs()), '?'));
            Database::query(
                'UPDATE post_types SET is_system = 1 WHERE slug IN (' . $in . ')',
                self::systemSlugs()
            );
            $st = Database::one("SHOW COLUMNS FROM post_types LIKE 'status'");
            $type = strtolower((string) ($st['Type'] ?? ''));
            if ($st && !str_contains($type, 'archived')) {
                Database::pdo()->exec(
                    "ALTER TABLE post_types MODIFY status ENUM('active','inactive','archived') NOT NULL DEFAULT 'active'"
                );
            }
            $vid = Database::all("SHOW COLUMNS FROM testimonials LIKE 'video_url'");
            if (!$vid) {
                Database::pdo()->exec(
                    'ALTER TABLE testimonials ADD COLUMN video_url VARCHAR(255) NULL AFTER initials'
                );
            }
            self::ensureAiType();
        } catch (Throwable $e) {
            error_log('Cpt::ensureSchema: ' . $e->getMessage());
        }
    }

    public static function ensureAiType(): void
    {
        if (self::type('ai')) {
            return;
        }
        $id = Database::insert('post_types', [
            'name' => 'AI',
            'singular_name' => 'AI programme',
            'slug' => 'ai',
            'description' => 'AI landing pages: banner, content, video testimonials and campuses.',
            'has_archive' => 1,
            'public' => 1,
            'template_mode' => 'both',
            'archive_title' => 'AI programmes',
            'archive_intro' => 'AI-focused programmes at VR Junior College, Hyderabad.',
            'sort_order' => 4,
            'status' => 'active',
            'is_system' => 0,
        ]);
        Database::insert('post_type_fields', [
            'post_type_id' => $id,
            'name' => 'duration',
            'label' => 'Duration',
            'type' => 'text',
            'is_required' => 0,
            'sort_order' => 1,
        ]);
        Database::insert('post_type_fields', [
            'post_type_id' => $id,
            'name' => 'who_for',
            'label' => 'Who it is for',
            'type' => 'textarea',
            'is_required' => 0,
            'sort_order' => 2,
        ]);
        if (class_exists('Templates')) {
            Templates::ensureAiLanding();
        }
    }

    public static function isSystem(array $type): bool
    {
        if ((int) ($type['is_system'] ?? 0) === 1) {
            return true;
        }
        return in_array((string) ($type['slug'] ?? ''), self::systemSlugs(), true);
    }

    public static function entries(int $typeId, bool $includeTrashed = false): array
    {
        $sql = 'SELECT * FROM cpt_entries WHERE post_type_id = ?';
        if (!$includeTrashed) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $sql .= ' ORDER BY sort_order, id';
        return Database::all($sql, [$typeId]);
    }

    /**
     * Menus, page sections, and other records that point at this type or its entries.
     *
     * @return array{header_blocking:bool,locations:list<array<string,mixed>>,menu_items:list<array<string,mixed>>}
     */
    public static function references(array $type): array
    {
        $typeId = (int) $type['id'];
        $slug = (string) $type['slug'];
        $entries = self::entries($typeId, true);
        $entryIds = array_map(static fn ($e) => (int) $e['id'], $entries);
        $locations = [];
        $menuItems = [];
        $headerBlocking = false;

        $items = Database::all(
            'SELECT mi.*, m.slug AS menu_slug, m.name AS menu_name
             FROM menu_items mi JOIN menus m ON m.id = mi.menu_id
             ORDER BY m.id, mi.sort_order, mi.id'
        );
        $byId = [];
        foreach ($items as $it) {
            $byId[(int) $it['id']] = $it;
        }

        $archivePath = '/' . trim($slug, '/') . '/';

        foreach ($items as $it) {
            $hit = false;
            $lt = (string) $it['link_type'];
            $oid = (int) ($it['object_id'] ?? 0);
            if ($lt === 'cpt_archive' && $oid === $typeId) {
                $hit = true;
            } elseif ($lt === 'cpt_entry' && in_array($oid, $entryIds, true)) {
                $hit = true;
            } else {
                $url = (string) ($it['url'] ?? '');
                $path = parse_url($url, PHP_URL_PATH);
                $path = is_string($path) && $path !== '' ? $path : $url;
                $norm = '/' . trim((string) $path, '/');
                $norm = $norm === '/' ? '/' : $norm . '/';
                if ($norm === $archivePath || str_starts_with($norm, $archivePath)) {
                    $hit = true;
                }
            }
            if (!$hit) {
                continue;
            }
            $menuItems[] = $it;
            $parent = !empty($it['parent_id']) ? ($byId[(int) $it['parent_id']] ?? null) : null;
            $detail = ($it['menu_name'] ?? 'Menu');
            if ($parent) {
                $detail .= ' → ' . $parent['label'] . ' dropdown';
            }
            $detail .= ' → ' . $it['label'];
            $isHeader = ($it['menu_slug'] ?? '') === 'header' && (int) $it['is_active'] === 1;
            if ($isHeader) {
                $headerBlocking = true;
            }
            $locations[] = [
                'kind' => 'menu',
                'header' => $isHeader,
                'detail' => $detail,
            ];
        }

        $like = '%/' . $slug . '%';
        foreach (Database::all(
            'SELECT s.id, s.owner_type, s.owner_id, s.type FROM content_sections s WHERE s.content_json LIKE ?',
            [$like]
        ) as $sec) {
            $where = $sec['type'] . ' section';
            if ($sec['owner_type'] === 'page') {
                $p = Database::one('SELECT title, slug FROM pages WHERE id = ?', [(int) $sec['owner_id']]);
                $where = ($p['title'] ?? 'Page') . ' → ' . $where;
            } elseif ($sec['owner_type'] === 'cpt') {
                $e = Database::one('SELECT title FROM cpt_entries WHERE id = ?', [(int) $sec['owner_id']]);
                $where = ($e['title'] ?? 'Entry') . ' → ' . $where;
            }
            $locations[] = ['kind' => 'content', 'header' => false, 'detail' => $where];
        }

        if ($entryIds && Database::all("SHOW TABLES LIKE 'snippet_targets'")) {
            $in = implode(',', array_fill(0, count($entryIds), '?'));
            $n = Database::one(
                'SELECT COUNT(*) c FROM snippet_targets WHERE target_type = "cpt" AND target_id IN (' . $in . ')',
                $entryIds
            );
            if ((int) ($n['c'] ?? 0) > 0) {
                $locations[] = [
                    'kind' => 'snippet',
                    'header' => false,
                    'detail' => (int) $n['c'] . ' code snippet target(s)',
                ];
            }
        }

        return [
            'header_blocking' => $headerBlocking,
            'locations' => $locations,
            'menu_items' => $menuItems,
        ];
    }

    public static function archive(array $type): void
    {
        $id = (int) $type['id'];
        Database::update('post_types', ['status' => 'archived'], 'id = ?', [$id]);
        foreach (self::references($type)['menu_items'] as $it) {
            Database::update('menu_items', ['is_active' => 0], 'id = ?', [(int) $it['id']]);
        }
        Cache::flush();
    }

    public static function restore(array $type): void
    {
        $id = (int) $type['id'];
        Database::update('post_types', ['status' => 'active'], 'id = ?', [$id]);
        foreach (self::references($type)['menu_items'] as $it) {
            Database::update('menu_items', ['is_active' => 1], 'id = ?', [(int) $it['id']]);
        }
        Cache::flush();
    }

    public static function purge(array $type, string $redirectTo = '/'): array
    {
        $id = (int) $type['id'];
        $slug = (string) $type['slug'];
        $entries = self::entries($id, true);
        $entryIds = array_map(static fn ($e) => (int) $e['id'], $entries);
        $live = array_values(array_filter($entries, static fn ($e) => $e['deleted_at'] === null));
        $to = $redirectTo !== '' ? $redirectTo : '/';
        if (!str_starts_with($to, '/') && !str_starts_with($to, 'http')) {
            $to = '/' . $to;
        }
        $to = path_url($to);

        $note = 'Deleted post type “' . $type['name'] . '”';
        if ((int) ($type['public'] ?? 0) === 1 || (int) ($type['has_archive'] ?? 0) === 1) {
            Redirects::save($slug, $to, 301, $note . ' (archive)');
            foreach ($entries as $e) {
                Redirects::save($slug . '/' . $e['slug'], $to, 301, $note . ' (entry)');
            }
        }

        self::rewriteUrls($slug, array_column($entries, 'slug'), $to);

        $refs = self::references($type);
        foreach ($refs['menu_items'] as $it) {
            $mid = (int) $it['id'];
            Database::query('UPDATE menu_items SET parent_id = NULL WHERE parent_id = ?', [$mid]);
            Database::delete('menu_items', 'id = ?', [$mid]);
        }

        if ($entryIds) {
            $in = implode(',', array_fill(0, count($entryIds), '?'));
            Database::query('DELETE FROM content_sections WHERE owner_type = "cpt" AND owner_id IN (' . $in . ')', $entryIds);
            Database::query('DELETE FROM content_revisions WHERE owner_type = "cpt" AND owner_id IN (' . $in . ')', $entryIds);
            Database::query('DELETE FROM preview_tokens WHERE owner_type = "cpt" AND owner_id IN (' . $in . ')', $entryIds);
            Database::query('DELETE FROM seo_metadata WHERE entity_type = "cpt" AND entity_id IN (' . $in . ')', $entryIds);
            if (Database::all("SHOW TABLES LIKE 'snippet_targets'")) {
                Database::query('DELETE FROM snippet_targets WHERE target_type = "cpt" AND target_id IN (' . $in . ')', $entryIds);
            }
        }
        Database::delete('seo_metadata', 'entity_type = ? AND entity_id = ?', ['post_type', $id]);

        Database::delete('post_types', 'id = ?', [$id]);
        Cache::flush();

        return [
            'entries' => count($live),
            'redirects' => 1 + count($entries),
        ];
    }

    private static function rewriteUrls(string $typeSlug, array $entrySlugs, string $to): void
    {
        $needles = [];
        foreach ($entrySlugs as $es) {
            $needles[] = path_url($typeSlug . '/' . $es);
        }
        $needles[] = path_url($typeSlug);
        usort($needles, static fn ($a, $b) => strlen($b) <=> strlen($a));

        $swap = static function (string $html) use ($needles, $to): string {
            $out = $html;
            foreach ($needles as $n) {
                $out = str_replace([rtrim(BASE_URL, '/') . $n, $n], $to, $out);
            }
            return $out;
        };

        foreach (Database::all(
            'SELECT id, content_json FROM content_sections WHERE content_json LIKE ?',
            ['%/' . $typeSlug . '%']
        ) as $row) {
            $next = $swap((string) $row['content_json']);
            if ($next !== $row['content_json']) {
                Database::update('content_sections', ['content_json' => $next], 'id = ?', [(int) $row['id']]);
            }
        }
        foreach (Database::all(
            'SELECT id, body_html FROM blog_posts WHERE body_html LIKE ?',
            ['%/' . $typeSlug . '%']
        ) as $row) {
            $next = $swap((string) ($row['body_html'] ?? ''));
            if ($next !== ($row['body_html'] ?? '')) {
                Database::update('blog_posts', ['body_html' => $next], 'id = ?', [(int) $row['id']]);
            }
        }
    }
}
