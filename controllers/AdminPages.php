<?php
declare(strict_types=1);

final class AdminPages
{
    public static function index(): void
    {
        Auth::requirePerm('pages.view');
        $status = Request::str('status');
        $q = Request::str('q');
        $trash = Request::str('trash') === '1';
        $sql = 'SELECT p.*, u.name AS editor FROM pages p LEFT JOIN users u ON u.id = p.updated_by WHERE ';
        $params = [];
        $sql .= $trash ? 'p.deleted_at IS NOT NULL' : 'p.deleted_at IS NULL';
        if ($status !== '') {
            $sql .= ' AND p.status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $sql .= ' AND (p.title LIKE ? OR p.slug LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY p.updated_at DESC';
        View::admin('pages/index', [
            'title' => $trash ? 'Pages — Trash' : 'Pages',
            'rows' => Database::all($sql, $params),
            'status' => $status,
            'q' => $q,
            'trash' => $trash,
        ]);
    }

    public static function createForm(): void
    {
        Auth::requirePerm('pages.create');
        Templates::ensureStarters();
        $templates = Database::all('SELECT * FROM page_templates ORDER BY name');
        View::admin('pages/create', ['title' => 'New page', 'templates' => $templates]);
    }

    public static function create(): void
    {
        Auth::requirePerm('pages.create');
        $title = Request::str('title');
        $slugIn = Request::str('slug') ?: $title;
        if (Slug::isReserved($slugIn) || Slug::firstSegmentTaken($slugIn)) {
            $msg = 'That slug is reserved or collides with an existing route.';
            if (Request::wantsJson()) {
                View::json(['ok' => false, 'error' => $msg], 422);
            }
            View::flash('error', $msg);
            View::redirect('/admin/pages/new/');
        }
        $slug = Slug::uniquePage($slugIn);
        $id = Database::insert('pages', [
            'type' => Request::str('type') ?: 'standard',
            'template_id' => Request::int('template_id') ?: null,
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        Database::insert('seo_metadata', [
            'entity_type' => 'page',
            'entity_id' => $id,
            'seo_title' => $title,
            'canonical_url' => url($slug === '/' ? '/' : $slug),
            'robots' => 'index,follow',
        ]);
        $tid = Request::int('template_id');
        if ($tid) {
            Templates::apply('page', $id, $tid);
        }
        Audit::log('page.created', 'page', $id);
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'id' => $id, 'redirect' => '/admin/pages/' . $id . '/']);
        }
        View::redirect('/admin/pages/' . $id . '/');
    }

    public static function edit(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $page = Database::one('SELECT * FROM pages WHERE id = ?', [(int) $id]);
        if (!$page) {
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        View::admin('pages/builder', [
            'title' => 'Edit: ' . $page['title'],
            'page' => $page,
            'ownerType' => 'page',
            'ownerId' => (int) $id,
            'sections' => Content::sections('page', (int) $id),
            'seo' => Database::one('SELECT * FROM seo_metadata WHERE entity_type="page" AND entity_id=?', [(int) $id]) ?: [],
            'revisions' => self::revisionRows((int) $id),
            'focusSec' => Request::int('sec'),
            'registry' => SectionRegistry::all(),
            'sectionTemplates' => Database::all('SELECT * FROM section_templates ORDER BY name'),
            'publicPath' => $page['slug'] === '/' ? '/' : path_url($page['slug']),
            'menuLinks' => Menu::linksTo('page', 'page', (int) $id),
        ]);
    }

    public static function save(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $page = Database::one('SELECT * FROM pages WHERE id = ? AND deleted_at IS NULL', [(int) $id]);
        if (!$page) {
            View::redirect('/admin/pages/');
        }
        $oldSlug = $page['slug'];
        $slugIn = Request::str('slug') ?: $page['slug'];
        if ($slugIn !== '/' && Slug::isReserved($slugIn)) {
            $msg = 'Reserved slug.';
            if (Request::wantsJson()) {
                View::json(['ok' => false, 'error' => $msg], 422);
            }
            View::flash('error', $msg);
            View::redirect('/admin/pages/' . $id . '/');
        }
        if ($slugIn !== '/' && Slug::firstSegmentTaken($slugIn, ['page_id' => (int) $id])) {
            $msg = 'That URL collides with another page or a post-type archive.';
            if (Request::wantsJson()) {
                View::json(['ok' => false, 'error' => $msg], 422);
            }
            View::flash('error', $msg);
            View::redirect('/admin/pages/' . $id . '/');
        }
        $slug = $slugIn === '/' ? '/' : Slug::uniquePage($slugIn, (int) $id);
        $status = Request::str('status') ?: $page['status'];
        if (!in_array($status, ['draft', 'published', 'scheduled', 'unpublished'], true)) {
            $status = $page['status'];
        }
        $scheduled = Request::str('scheduled_at') ?: null;
        Database::update('pages', [
            'title' => Request::str('title'),
            'slug' => $slug,
            'type' => Request::str('type') ?: $page['type'],
            'status' => $status,
            'scheduled_at' => $status === 'scheduled' ? $scheduled : null,
            'updated_by' => Auth::id(),
        ], 'id = ?', [(int) $id]);

        if ($oldSlug !== $slug && $page['status'] === 'published') {
            Redirects::onSlugChange($oldSlug, $slug === '/' ? '/' : $slug, 'Page slug change');
        }

        Content::saveSectionsFromPost('page', (int) $id);
        $seo = Content::seoFromRequest();
        $seo = Content::fillCanonical($seo, $slug === '/' ? '/' : $slug);
        Database::upsertSeo('page', (int) $id, $seo);
        Content::snapshot('page', (int) $id, false, 'Draft saved');
        Audit::log('page.updated', 'page', (int) $id);
        $focus = Request::int('focus_sec');
        $back = '/admin/pages/' . $id . '/' . ($focus ? ('?sec=' . $focus) : '');
        if (Request::str('after') === 'preview') {
            $url = Content::previewUrl('page', (int) $id, $slug === '/' ? '/' : $slug);
            if (Request::wantsJson()) {
                View::json(['ok' => true, 'message' => 'Saved — opening preview.', 'redirect' => $url]);
            }
            View::redirect($url);
        }
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'message' => 'Draft saved.', 'slug' => $slug]);
        }
        View::flash('success', 'Draft saved.');
        View::redirect($back);
    }

    public static function addSection(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $sid = Content::addSection('page', (int) $id, Request::str('type'), Request::int('section_template_id') ?: null);
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'redirect' => '/admin/pages/' . $id . '/?sec=' . $sid]);
        }
        View::redirect('/admin/pages/' . $id . '/?sec=' . $sid);
    }

    public static function toggleVisible(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $sid = Request::int('section_id');
        $sec = Database::one('SELECT * FROM content_sections WHERE id = ? AND owner_type="page" AND owner_id = ?', [$sid, (int) $id]);
        if (!$sec) {
            View::json(['ok' => false], 404);
        }
        $on = Request::str('visible') === '1' ? 1 : 0;
        Database::update('content_sections', ['is_visible' => $on], 'id = ?', [$sid]);
        View::json(['ok' => true, 'visible' => $on]);
    }

    public static function deleteSection(string $id): void
    {
        Auth::requirePerm('pages.edit');
        Database::delete('content_sections', 'id = ? AND owner_type = ? AND owner_id = ?', [Request::int('section_id'), 'page', (int) $id]);
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'redirect' => '/admin/pages/' . $id . '/']);
        }
        View::redirect('/admin/pages/' . $id . '/');
    }

    public static function duplicateSection(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $sec = Database::one('SELECT * FROM content_sections WHERE id = ? AND owner_type="page" AND owner_id = ?', [Request::int('section_id'), (int) $id]);
        $nid = 0;
        if ($sec) {
            $nid = Database::insert('content_sections', [
                'owner_type' => 'page',
                'owner_id' => (int) $id,
                'type' => $sec['type'],
                'content_json' => $sec['content_json'],
                'sort_order' => (int) $sec['sort_order'] + 1,
                'is_visible' => 1,
            ]);
        }
        $to = '/admin/pages/' . $id . '/' . ($nid ? ('?sec=' . $nid) : '');
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'redirect' => $to]);
        }
        View::redirect($to);
    }

    public static function reorder(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $order = $_POST['order'] ?? [];
        if (is_string($order)) {
            $order = json_decode($order, true) ?: [];
        }
        foreach ($order as $i => $sid) {
            Database::update('content_sections', ['sort_order' => (int) $i], 'id = ? AND owner_type="page" AND owner_id = ?', [(int) $sid, (int) $id]);
        }
        View::json(['ok' => true]);
    }

    public static function publish(string $id): void
    {
        Auth::requirePerm('pages.publish');
        Content::publish('page', (int) $id);
        Audit::log('page.published', 'page', (int) $id);
        View::flash('success', 'Page published.');
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'message' => 'Published']);
        }
        View::redirect('/admin/pages/' . $id . '/');
    }

    public static function unpublish(string $id): void
    {
        Auth::requirePerm('pages.publish');
        Database::update('pages', ['status' => 'unpublished', 'updated_by' => Auth::id()], 'id = ?', [(int) $id]);
        Database::query('UPDATE content_revisions SET is_live = 0 WHERE owner_type="page" AND owner_id = ?', [(int) $id]);
        Cache::flush();
        Audit::log('page.unpublished', 'page', (int) $id);
        View::flash('success', 'Page unpublished. Direct visits now 404.');
        View::redirect('/admin/pages/' . $id . '/');
    }

    public static function preview(string $id): void
    {
        Auth::requirePerm('pages.view');
        $page = Database::one('SELECT slug FROM pages WHERE id = ?', [(int) $id]);
        $url = Content::previewUrl('page', (int) $id, $page['slug'] === '/' ? '/' : $page['slug']);
        View::redirect($url);
    }

    public static function revisionPreview(string $id, string $rid): void
    {
        Auth::requirePerm('pages.view');
        $page = Database::one('SELECT * FROM pages WHERE id = ?', [(int) $id]);
        $rev = Database::one('SELECT * FROM content_revisions WHERE id = ? AND owner_type="page" AND owner_id = ?', [(int) $rid, (int) $id]);
        if (!$page || !$rev) {
            http_response_code(404);
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        $snap = json_decode($rev['snapshot_json'], true) ?: [];
        PublicSite::renderFromSnapshot($page, $snap, true);
    }

    public static function restoreRev(string $id): void
    {
        Auth::requirePerm('pages.edit');
        $rev = Database::one('SELECT * FROM content_revisions WHERE id = ? AND owner_type="page" AND owner_id = ?', [Request::int('revision_id'), (int) $id]);
        if (!$rev) {
            View::redirect('/admin/pages/' . $id . '/');
        }
        $snap = json_decode($rev['snapshot_json'], true) ?: [];
        Database::delete('content_sections', 'owner_type = ? AND owner_id = ?', ['page', (int) $id]);
        foreach ($snap['sections'] ?? [] as $i => $s) {
            Database::insert('content_sections', [
                'owner_type' => 'page',
                'owner_id' => (int) $id,
                'type' => $s['type'],
                'content_json' => Html::json($s['content'] ?? []),
                'sort_order' => $i,
                'is_visible' => $s['is_visible'] ?? 1,
            ]);
        }
        Content::snapshot('page', (int) $id, false, 'Restored revision #' . (int) $rev['id']);
        View::flash('success', 'Revision restored into the draft. Preview before publishing.');
        View::redirect('/admin/pages/' . $id . '/');
    }

    private static function revisionRows(int $pageId): array
    {
        $rows = Database::all(
            'SELECT r.id, r.is_live, r.note, r.created_at, r.snapshot_json, u.name AS user_name
             FROM content_revisions r LEFT JOIN users u ON u.id = r.created_by
             WHERE r.owner_type="page" AND r.owner_id = ? ORDER BY r.id DESC LIMIT 20',
            [$pageId]
        );
        $prevTypes = null;
        $out = [];
        foreach (array_reverse($rows) as $r) {
            $snap = json_decode($r['snapshot_json'] ?: '{}', true) ?: [];
            $secs = $snap['sections'] ?? [];
            $types = array_column($secs, 'type');
            $change = 'Saved';
            if ($prevTypes === null) {
                $change = 'Initial';
            } elseif (count($types) > count($prevTypes)) {
                $change = 'Added a section';
            } elseif (count($types) < count($prevTypes)) {
                $change = 'Removed a section';
            } elseif ($types !== $prevTypes) {
                $change = 'Reordered or swapped sections';
            } else {
                $change = 'Updated content';
            }
            if (!empty($r['is_live'])) {
                $change = 'Published';
            }
            if (str_starts_with((string) ($r['note'] ?? ''), 'Restored')) {
                $change = 'Restored';
            }
            $r['section_count'] = count($secs);
            $r['type_labels'] = array_values(array_unique(array_map([SectionRegistry::class, 'label'], $types)));
            $r['change'] = $change;
            unset($r['snapshot_json']);
            $out[] = $r;
            $prevTypes = $types;
        }
        return array_reverse($out);
    }

    public static function trash(): void
    {
        Auth::requirePerm('pages.delete');
        $id = Request::int('id');
        $page = Database::one('SELECT * FROM pages WHERE id = ?', [$id]);
        if ($page && $page['slug'] === '/') {
            View::flash('error', 'The homepage cannot be deleted.');
            View::redirect('/admin/pages/');
        }
        $links = Menu::linksTo('page', 'page', $id);
        if ($links && !Request::bool('confirm_links')) {
            View::flash('error', 'This page is linked from a menu. Confirm to move it to trash.');
            View::redirect('/admin/pages/' . $id . '/');
        }
        Database::update('pages', ['deleted_at' => date('Y-m-d H:i:s'), 'status' => 'unpublished'], 'id = ?', [$id]);
        Cache::flush();
        Audit::log('page.trashed', 'page', $id);
        View::flash('success', 'Page moved to trash.');
        View::redirect('/admin/pages/');
    }

    public static function restore(): void
    {
        Auth::requirePerm('pages.edit');
        $id = Request::int('id');
        Database::update('pages', ['deleted_at' => null, 'status' => 'draft'], 'id = ?', [$id]);
        Audit::log('page.restored', 'page', $id);
        View::flash('success', 'Page restored as a draft.');
        View::redirect('/admin/pages/');
    }

    public static function destroy(): void
    {
        Auth::requirePerm('pages.delete');
        $id = Request::int('id');
        $page = Database::one('SELECT * FROM pages WHERE id = ?', [$id]);
        if ($page && $page['slug'] === '/') {
            View::flash('error', 'The homepage cannot be deleted.');
            View::redirect('/admin/pages/');
        }
        Database::delete('pages', 'id = ?', [$id]);
        Cache::flush();
        Audit::log('page.deleted', 'page', $id);
        View::flash('success', 'Page permanently deleted.');
        View::redirect('/admin/pages/?trash=1');
    }

    public static function saveSectionTemplate(string $id): void
    {
        Auth::requirePerm('templates.create');
        $sec = Database::one('SELECT * FROM content_sections WHERE id = ? AND owner_type="page" AND owner_id = ?', [Request::int('section_id'), (int) $id]);
        if ($sec) {
            Templates::saveSectionAsTemplate($sec, Request::str('name'));
            View::flash('success', 'Saved as a section template. Find it under Templates → Section templates.');
        }
        View::redirect('/admin/pages/' . $id . '/#builder');
    }

    public static function savePageTemplate(string $id): void
    {
        Auth::requirePerm('templates.create');
        $page = Database::one('SELECT * FROM pages WHERE id = ?', [(int) $id]);
        $sections = Content::sections('page', (int) $id);
        $pack = [];
        foreach ($sections as $s) {
            $pack[] = ['type' => $s['type'], 'content' => json_decode($s['content_json'], true)];
        }
        Database::insert('page_templates', [
            'slug' => Slug::uniqueInTable('page_templates', Request::str('name') ?: $page['title']),
            'name' => Request::str('name') ?: $page['title'],
            'page_type' => $page['type'],
            'sections_json' => Html::json($pack),
        ]);
        View::flash('success', 'Page saved as template.');
        View::redirect('/admin/pages/' . $id . '/');
    }
}
