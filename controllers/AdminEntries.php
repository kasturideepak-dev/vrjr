<?php
declare(strict_types=1);

final class AdminEntries
{
    public static function index(string $typeSlug): void
    {
        Auth::requirePerm('entries.view');
        $type = Cpt::type($typeSlug);
        if (!$type) {
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        $trash = Request::str('trash') === '1';
        $sql = 'SELECT * FROM cpt_entries WHERE post_type_id = ? AND ' . ($trash ? 'deleted_at IS NOT NULL' : 'deleted_at IS NULL') . ' ORDER BY sort_order, id DESC';
        View::admin('entries/index', [
            'title' => $type['name'],
            'type' => $type,
            'rows' => Database::all($sql, [(int) $type['id']]),
            'trash' => $trash,
        ]);
    }

    public static function form(string $typeSlug, ?string $id = null): void
    {
        $type = Cpt::type($typeSlug);
        if (!$type) {
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        $row = $id ? Database::one('SELECT * FROM cpt_entries WHERE id = ? AND post_type_id = ?', [(int) $id, (int) $type['id']]) : null;
        Auth::requirePerm($row ? 'entries.edit' : 'entries.create');
        if ($id && !$row) {
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        $fields = Cpt::fields((int) $type['id']);
        $values = $row ? (json_decode($row['fields_json'] ?: '{}', true) ?: []) : [];
        $sections = $row ? Content::sections('cpt', (int) $row['id']) : [];
        $seo = $row ? (Database::one('SELECT * FROM seo_metadata WHERE entity_type="cpt" AND entity_id=?', [(int) $row['id']]) ?: []) : [];
        View::admin('entries/form', [
            'title' => $row ? ('Edit ' . $type['singular_name']) : ('New ' . $type['singular_name']),
            'type' => $type,
            'row' => $row,
            'fieldDefs' => $fields,
            'values' => $values,
            'sections' => $sections,
            'seo' => $seo,
            'registry' => SectionRegistry::all(),
            'sectionTemplates' => Database::all('SELECT * FROM section_templates ORDER BY name'),
            'pageTemplates' => Database::all('SELECT * FROM page_templates ORDER BY name'),
            'defaultTemplateId' => Templates::defaultIdForType($type),
            'ownerType' => 'cpt',
            'ownerId' => $row ? (int) $row['id'] : 0,
            'publicPath' => $row ? Cpt::permalink($row, $type) : path_url($type['slug'] . '/new'),
        ]);
    }

    public static function save(string $typeSlug): void
    {
        $type = Cpt::type($typeSlug);
        if (!$type) {
            View::redirect('/admin/post-types/');
        }
        $id = Request::int('id');
        Auth::requirePerm($id ? 'entries.edit' : 'entries.create');
        $fieldDefs = Cpt::fields((int) $type['id']);
        $values = Cpt::saveFieldsFromRequest($fieldDefs);
        $errors = Cpt::validateRequired($fieldDefs, $values);
        $title = Request::str('title');
        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        $slugIn = Request::str('slug') ?: $title;
        if ($errors) {
            if (Request::wantsJson()) {
                View::json(['ok' => false, 'error' => implode(' ', $errors)], 422);
            }
            View::flash('error', implode(' ', $errors));
            View::redirect($id ? '/admin/content/' . $typeSlug . '/' . $id . '/' : '/admin/content/' . $typeSlug . '/new/');
        }
        $old = $id ? Database::one('SELECT * FROM cpt_entries WHERE id = ?', [$id]) : null;
        $slug = Slug::uniqueEntry((int) $type['id'], $slugIn, $id ?: null);
        $status = Request::str('status') ?: 'draft';
        if (!in_array($status, ['draft', 'published', 'scheduled', 'unpublished'], true)) {
            $status = 'draft';
        }
        $data = [
            'post_type_id' => (int) $type['id'],
            'title' => $title,
            'slug' => $slug,
            'status' => $status,
            'excerpt' => Request::str('excerpt'),
            'featured_image' => Request::str('featured_image'),
            'body_html' => Html::allowedHtml((string) ($_POST['body_html'] ?? '')),
            'fields_json' => Html::json($values),
            'sort_order' => Request::int('sort_order'),
            'author_id' => Auth::id(),
            'scheduled_at' => $status === 'scheduled' ? (Request::str('scheduled_at') ?: null) : null,
        ];
        if ($id) {
            Database::update('cpt_entries', $data, 'id = ?', [$id]);
            if ($old && $old['slug'] !== $slug && $old['status'] === 'published' && (int) $type['public']) {
                Redirects::onSlugChange($type['slug'] . '/' . $old['slug'], $type['slug'] . '/' . $slug, 'Entry slug change');
            }
            Audit::log('entry.updated', 'cpt', $id);
        } else {
            $id = Database::insert('cpt_entries', $data);
            Audit::log('entry.created', 'cpt', $id);
            $tid = Request::int('template_id');
            if ($tid && in_array($type['template_mode'], ['builder', 'both'], true)) {
                Templates::apply('cpt', $id, $tid);
            }
        }
        if ($old && in_array($type['template_mode'], ['builder', 'both'], true)) {
            Content::saveSectionsFromPost('cpt', $id);
        }
        $seo = Content::seoFromRequest();
        $seo = Content::fillCanonical($seo, $type['slug'] . '/' . $slug);
        Database::upsertSeo('cpt', $id, $seo);
        Content::snapshot('cpt', $id, false, 'Draft saved');
        if ($status === 'published') {
            Content::publish('cpt', $id);
        }
        Cache::flush();
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'id' => $id, 'slug' => $slug, 'url' => Cpt::permalink(['slug' => $slug, 'post_type_id' => (int) $type['id']], $type)]);
        }
        View::flash('success', 'Saved.');
        View::redirect('/admin/content/' . $typeSlug . '/' . $id . '/');
    }

    public static function addSection(string $typeSlug, string $id): void
    {
        Auth::requirePerm('entries.edit');
        Content::addSection('cpt', (int) $id, Request::str('type'), Request::int('section_template_id') ?: null);
        View::redirect('/admin/content/' . $typeSlug . '/' . $id . '/#builder');
    }

    public static function saveSectionTemplate(string $typeSlug, string $id): void
    {
        Auth::requirePerm('templates.create');
        $sec = Database::one(
            'SELECT * FROM content_sections WHERE id = ? AND owner_type="cpt" AND owner_id = ?',
            [Request::int('section_id'), (int) $id]
        );
        if ($sec) {
            Templates::saveSectionAsTemplate($sec, Request::str('name'));
            View::flash('success', 'Saved as a section template. Find it under Templates.');
        }
        View::redirect('/admin/content/' . $typeSlug . '/' . $id . '/#builder');
    }

    public static function deleteSection(string $typeSlug, string $id): void
    {
        Auth::requirePerm('entries.edit');
        Database::delete('content_sections', 'id = ? AND owner_type="cpt" AND owner_id = ?', [Request::int('section_id'), (int) $id]);
        View::redirect('/admin/content/' . $typeSlug . '/' . $id . '/#builder');
    }

    public static function publish(string $typeSlug, string $id): void
    {
        Auth::requirePerm('entries.publish');
        Content::publish('cpt', (int) $id);
        Audit::log('entry.published', 'cpt', (int) $id);
        View::flash('success', 'Published.');
        View::redirect('/admin/content/' . $typeSlug . '/' . $id . '/');
    }

    public static function preview(string $typeSlug, string $id): void
    {
        Auth::requirePerm('entries.view');
        $type = Cpt::type($typeSlug);
        $row = Database::one('SELECT slug FROM cpt_entries WHERE id = ?', [(int) $id]);
        View::redirect(Content::previewUrl('cpt', (int) $id, $type['slug'] . '/' . $row['slug']));
    }

    public static function trash(string $typeSlug): void
    {
        Auth::requirePerm('entries.delete');
        $id = Request::int('id');
        $links = Menu::linksTo('cpt_entry', 'cpt', $id);
        if ($links && !Request::bool('confirm_links')) {
            View::flash('error', 'This entry is linked from a menu. Confirm to trash it.');
            View::redirect('/admin/content/' . $typeSlug . '/' . $id . '/');
        }
        Database::update('cpt_entries', ['deleted_at' => date('Y-m-d H:i:s'), 'status' => 'unpublished'], 'id = ?', [$id]);
        Cache::flush();
        Audit::log('entry.trashed', 'cpt', $id);
        View::flash('success', 'Moved to trash.');
        View::redirect('/admin/content/' . $typeSlug . '/');
    }

    public static function restore(string $typeSlug): void
    {
        Auth::requirePerm('entries.edit');
        $id = Request::int('id');
        Database::update('cpt_entries', ['deleted_at' => null, 'status' => 'draft'], 'id = ?', [$id]);
        View::flash('success', 'Restored as a draft.');
        View::redirect('/admin/content/' . $typeSlug . '/');
    }

    public static function destroy(string $typeSlug): void
    {
        Auth::requirePerm('entries.delete');
        $id = Request::int('id');
        Database::delete('cpt_entries', 'id = ?', [$id]);
        Cache::flush();
        Audit::log('entry.deleted', 'cpt', $id);
        View::redirect('/admin/content/' . $typeSlug . '/?trash=1');
    }
}
