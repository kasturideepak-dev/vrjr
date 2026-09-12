<?php
declare(strict_types=1);

final class AdminSnippets
{
    public static function index(): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $q = Request::str('q');
        $type = Request::str('type');
        $placement = Request::str('placement');
        $sql = 'SELECT s.*, u.name AS editor_name
                FROM code_snippets s
                LEFT JOIN users u ON u.id = s.updated_by
                WHERE 1=1';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND (s.name LIKE ? OR s.notes LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if (in_array($type, ['html', 'css', 'javascript'], true)) {
            $sql .= ' AND s.type = ?';
            $params[] = $type;
        }
        if (in_array($placement, ['header', 'body_start', 'footer'], true)) {
            $sql .= ' AND s.placement = ?';
            $params[] = $placement;
        }
        $sql .= ' ORDER BY s.priority ASC, s.name ASC';
        $rows = Database::all($sql, $params);
        $counts = [];
        if ($rows) {
            $ids = array_map(static fn ($r) => (int) $r['id'], $rows);
            $in = implode(',', array_fill(0, count($ids), '?'));
            foreach (Database::all('SELECT snippet_id, COUNT(*) c FROM snippet_targets WHERE snippet_id IN (' . $in . ') GROUP BY snippet_id', $ids) as $c) {
                $counts[(int) $c['snippet_id']] = (int) $c['c'];
            }
        }
        View::admin('snippets/index', [
            'title' => 'Code snippets',
            'rows' => $rows,
            'counts' => $counts,
            'q' => $q,
            'type' => $type,
            'placement' => $placement,
        ]);
    }

    public static function form(?string $id = null): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $row = null;
        if ($id) {
            $row = Snippets::one((int) $id);
            if (!$row) {
                http_response_code(404);
                View::admin('errors/404', ['title' => 'Not found']);
                return;
            }
        }
        View::admin('snippets/form', [
            'title' => $row ? 'Edit snippet' : 'New snippet',
            'row' => $row,
            'catalog' => Snippets::targetCatalog(),
            'selected' => self::selectedKeys($row['targets'] ?? []),
        ]);
    }

    public static function save(?string $id = null): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $id = $id ? (int) $id : Request::int('id');
        $existing = $id > 0 ? Snippets::one($id) : null;
        if ($id > 0 && !$existing) {
            View::flash('error', 'Snippet not found.');
            View::redirect('/admin/snippets/');
        }

        $name = Request::str('name');
        $type = Request::str('type');
        $placement = Request::str('placement');
        $scope = Request::str('scope') === 'specific' ? 'specific' : 'global';
        $code = isset($_POST['code']) && is_string($_POST['code']) ? $_POST['code'] : '';
        $notes = isset($_POST['notes']) && is_string($_POST['notes']) ? $_POST['notes'] : '';
        $priority = Request::int('priority', 10);
        $wantActive = Request::bool('is_active');

        if ($name === '') {
            View::flash('error', 'Name is required.');
            View::redirect($id > 0 ? '/admin/snippets/' . $id . '/' : '/admin/snippets/new/');
        }
        if (!in_array($type, ['html', 'css', 'javascript'], true)) {
            $type = 'html';
        }
        if (!in_array($placement, ['header', 'body_start', 'footer'], true)) {
            $placement = 'header';
        }
        if (strlen($code) > 524288) {
            View::flash('error', 'Code is over 512 KB.');
            View::redirect($id > 0 ? '/admin/snippets/' . $id . '/' : '/admin/snippets/new/');
        }

        $activatedOnce = $existing ? (int) $existing['activated_once'] === 1 : false;
        $warn = '';
        if ($wantActive && !$activatedOnce && !self::firstActivationConfirmed()) {
            $wantActive = false;
            $warn = 'Saved as inactive. Type “activate” or tick the confirmation box to go live the first time.';
        }

        $wasActive = $existing ? (int) $existing['is_active'] === 1 : false;
        $data = [
            'name' => $name,
            'type' => $type,
            'code' => $code,
            'placement' => $placement,
            'scope' => $scope,
            'is_active' => $wantActive ? 1 : 0,
            'priority' => $priority,
            'notes' => $notes !== '' ? $notes : null,
            'updated_by' => Auth::id(),
        ];
        if ($wantActive) {
            $data['activated_once'] = 1;
        }

        if ($existing) {
            Database::update('code_snippets', $data, 'id = ?', [$id]);
        } else {
            $data['created_by'] = Auth::id();
            $data['is_active'] = $wantActive ? 1 : 0;
            $data['activated_once'] = $wantActive ? 1 : 0;
            $id = Database::insert('code_snippets', $data);
        }

        $targets = $scope === 'specific' ? Snippets::parseTargetInput($_POST['targets'] ?? []) : [];
        Snippets::saveTargets($id, $targets);

        $meta = ['name' => $name, 'type' => $type, 'placement' => $placement, 'active' => $wantActive ? 1 : 0];
        Audit::log($existing ? 'snippet.updated' : 'snippet.created', 'snippet', $id, $meta);
        if ($wantActive && !$wasActive) {
            Audit::log('snippet.activated', 'snippet', $id, ['name' => $name]);
        } elseif (!$wantActive && $wasActive) {
            Audit::log('snippet.deactivated', 'snippet', $id, ['name' => $name]);
        }

        View::flash($warn !== '' ? 'error' : 'success', $warn !== '' ? $warn : 'Snippet saved.');
        View::redirect('/admin/snippets/' . $id . '/');
    }

    public static function toggle(string $id): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $row = Snippets::one((int) $id);
        if (!$row) {
            View::flash('error', 'Snippet not found.');
            View::redirect('/admin/snippets/');
        }
        $on = (int) $row['is_active'] === 1;
        if ($on) {
            Database::update('code_snippets', [
                'is_active' => 0,
                'updated_by' => Auth::id(),
            ], 'id = ?', [(int) $id]);
            Audit::log('snippet.deactivated', 'snippet', (int) $id, ['name' => $row['name']]);
            View::flash('success', 'Snippet deactivated.');
            View::redirect('/admin/snippets/');
        }
        if ((int) $row['activated_once'] !== 1) {
            View::flash('error', 'First activation must be confirmed in the editor — type “activate” there.');
            View::redirect('/admin/snippets/' . (int) $id . '/');
        }
        Database::update('code_snippets', [
            'is_active' => 1,
            'updated_by' => Auth::id(),
        ], 'id = ?', [(int) $id]);
        Audit::log('snippet.activated', 'snippet', (int) $id, ['name' => $row['name']]);
        View::flash('success', 'Snippet activated.');
        View::redirect('/admin/snippets/');
    }

    public static function duplicate(): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $id = Request::int('id');
        $row = Snippets::one($id);
        if (!$row) {
            View::flash('error', 'Snippet not found.');
            View::redirect('/admin/snippets/');
        }
        $newId = Database::insert('code_snippets', [
            'name' => 'Copy of ' . $row['name'],
            'type' => $row['type'],
            'code' => $row['code'],
            'placement' => $row['placement'],
            'scope' => $row['scope'],
            'is_active' => 0,
            'activated_once' => 0,
            'priority' => (int) $row['priority'],
            'notes' => $row['notes'],
            'origin_key' => null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        $targets = [];
        foreach ($row['targets'] ?? [] as $t) {
            $targets[] = ['type' => $t['target_type'], 'id' => (int) $t['target_id']];
        }
        Snippets::saveTargets($newId, $targets);
        Audit::log('snippet.duplicated', 'snippet', $newId, ['from' => $id, 'name' => $row['name']]);
        View::flash('success', 'Snippet duplicated as inactive. Review it before activating.');
        View::redirect('/admin/snippets/' . $newId . '/');
    }

    public static function delete(): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $id = Request::int('id');
        $row = Snippets::one($id);
        if (!$row) {
            View::flash('error', 'Snippet not found.');
            View::redirect('/admin/snippets/');
        }
        Database::delete('code_snippets', 'id = ?', [$id]);
        Audit::log('snippet.deleted', 'snippet', $id, ['name' => $row['name']]);
        View::flash('success', 'Snippet deleted.');
        View::redirect('/admin/snippets/');
    }

    public static function preview(string $id): void
    {
        Auth::requireSuper();
        Snippets::boot();
        $row = Snippets::one((int) $id);
        if (!$row) {
            View::flash('error', 'Snippet not found.');
            View::redirect('/admin/snippets/');
        }
        $url = Snippets::previewUrl($row);
        View::redirect($url);
    }

    private static function firstActivationConfirmed(): bool
    {
        $typed = strtolower(trim((string) ($_POST['confirm_activate'] ?? '')));
        if ($typed === 'activate') {
            return true;
        }
        return Request::bool('confirm_live');
    }

    private static function selectedKeys(array $targets): array
    {
        $keys = [];
        foreach ($targets as $t) {
            $keys[] = $t['target_type'] . ':' . (int) $t['target_id'];
        }
        return $keys;
    }
}
