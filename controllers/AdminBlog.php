<?php
declare(strict_types=1);

final class AdminBlog
{
    public static function index(): void
    {
        Auth::requirePerm('blog.view');
        $trash = Request::str('trash') === '1';
        $sql = 'SELECT p.*, u.name AS author_name FROM blog_posts p LEFT JOIN users u ON u.id = p.author_id WHERE '
            . ($trash ? 'p.deleted_at IS NOT NULL' : 'p.deleted_at IS NULL')
            . ' ORDER BY p.published_at DESC, p.id DESC';
        View::admin('blog/index', [
            'title' => $trash ? 'Blog — Trash' : 'Blog',
            'rows' => Database::all($sql),
            'trash' => $trash,
            'categories' => Database::all('SELECT * FROM blog_categories ORDER BY name'),
        ]);
    }

    public static function form(?string $id = null): void
    {
        $row = $id ? Database::one('SELECT * FROM blog_posts WHERE id = ?', [(int) $id]) : null;
        Auth::requirePerm($row ? 'blog.edit' : 'blog.create');
        $cats = Database::all('SELECT * FROM blog_categories ORDER BY name');
        $tags = Database::all('SELECT * FROM blog_tags ORDER BY name');
        $selectedCats = $row ? array_column(Database::all('SELECT category_id FROM blog_post_categories WHERE post_id = ?', [(int) $row['id']]), 'category_id') : [];
        $selectedTags = $row ? array_column(Database::all('SELECT tag_id FROM blog_post_tags WHERE post_id = ?', [(int) $row['id']]), 'tag_id') : [];
        $seo = $row ? (Database::one('SELECT * FROM seo_metadata WHERE entity_type="blog" AND entity_id=?', [(int) $row['id']]) ?: []) : [];
        $faqs = $row ? Database::all('SELECT question, answer FROM faqs WHERE entity_type="blog" AND entity_id=? ORDER BY sort_order, id', [(int) $row['id']]) : [];
        View::admin('blog/form', [
            'title' => $row ? 'Edit post' : 'New post',
            'row' => $row,
            'categories' => $cats,
            'tags' => $tags,
            'selectedCats' => $selectedCats,
            'selectedTags' => $selectedTags,
            'seo' => $seo,
            'faqs' => $faqs,
        ]);
    }

    public static function save(): void
    {
        $id = Request::int('id');
        Auth::requirePerm($id ? 'blog.edit' : 'blog.create');
        $old = $id ? Database::one('SELECT * FROM blog_posts WHERE id = ?', [$id]) : null;
        $slug = Slug::uniqueInTable('blog_posts', Request::str('slug') ?: Request::str('title'), $id ?: null);
        if (Slug::isReserved($slug)) {
            View::flash('error', 'Reserved slug.');
            View::redirect('/admin/blog/');
        }
        $status = Request::str('status') ?: 'draft';
        $data = [
            'title' => Request::str('title'),
            'slug' => $slug,
            'excerpt' => Request::str('excerpt'),
            'body_html' => Html::allowedHtml((string) ($_POST['body_html'] ?? '')),
            'featured_image' => Request::str('featured_image'),
            'author_id' => Request::int('author_id') ?: Auth::id(),
            'status' => $status,
            'scheduled_at' => $status === 'scheduled' ? Request::str('scheduled_at') : null,
            'published_at' => $status === 'published' ? (Request::str('published_at') ?: date('Y-m-d H:i:s')) : ($old['published_at'] ?? null),
        ];
        if ($id) {
            if ($old && $old['slug'] !== $slug && $old['status'] === 'published') {
                Redirects::onSlugChange('blog/' . $old['slug'], 'blog/' . $slug, 'Blog slug change');
            }
            Database::update('blog_posts', $data, 'id = ?', [$id]);
        } else {
            $id = Database::insert('blog_posts', $data);
        }
        Database::delete('blog_post_categories', 'post_id = ?', [$id]);
        foreach ($_POST['categories'] ?? [] as $cid) {
            Database::insert('blog_post_categories', ['post_id' => $id, 'category_id' => (int) $cid]);
        }
        Database::delete('blog_post_tags', 'post_id = ?', [$id]);
        $tagNames = Request::str('tags');
        if ($tagNames !== '') {
            foreach (array_map('trim', explode(',', $tagNames)) as $tn) {
                if ($tn === '') {
                    continue;
                }
                $ts = Slug::make($tn);
                $tag = Database::one('SELECT id FROM blog_tags WHERE slug = ?', [$ts]);
                $tid = $tag ? (int) $tag['id'] : Database::insert('blog_tags', ['name' => $tn, 'slug' => $ts]);
                Database::insert('blog_post_tags', ['post_id' => $id, 'tag_id' => $tid]);
            }
        }
        Database::delete('faqs', 'entity_type = ? AND entity_id = ?', ['blog', $id]);
        $fq = $_POST['faq_question'] ?? [];
        $fa = $_POST['faq_answer'] ?? [];
        $order = 0;
        foreach ((array) $fq as $k => $q) {
            $q = trim((string) $q);
            $a = trim((string) ($fa[$k] ?? ''));
            if ($q === '' || $a === '') {
                continue;
            }
            Database::insert('faqs', [
                'question' => mb_substr($q, 0, 255),
                'answer' => $a,
                'entity_type' => 'blog',
                'entity_id' => $id,
                'sort_order' => ++$order,
                'is_visible' => 1,
            ]);
        }
        $seo = Content::seoFromRequest();
        $seo = Content::fillCanonical($seo, 'blog/' . $slug);
        Database::upsertSeo('blog', $id, $seo);
        Cache::flush();
        Audit::log('blog.saved', 'blog', $id);
        if (Request::wantsJson()) {
            View::json(['ok' => true, 'id' => $id]);
        }
        View::flash('success', 'Post saved.');
        View::redirect('/admin/blog/' . $id . '/');
    }

    public static function trash(): void
    {
        Auth::requirePerm('blog.delete');
        $id = Request::int('id');
        Database::update('blog_posts', ['deleted_at' => date('Y-m-d H:i:s'), 'status' => 'unpublished'], 'id = ?', [$id]);
        Cache::flush();
        View::redirect('/admin/blog/');
    }

    public static function restore(): void
    {
        Auth::requirePerm('blog.edit');
        Database::update('blog_posts', ['deleted_at' => null, 'status' => 'draft'], 'id = ?', [Request::int('id')]);
        View::redirect('/admin/blog/');
    }

    public static function destroy(): void
    {
        Auth::requirePerm('blog.delete');
        Database::delete('blog_posts', 'id = ?', [Request::int('id')]);
        Cache::flush();
        View::redirect('/admin/blog/?trash=1');
    }

    public static function saveCategory(): void
    {
        Auth::requirePerm('blog.edit');
        $name = Request::str('name');
        if ($name === '') {
            View::redirect('/admin/blog/');
        }
        $slug = Slug::uniqueInTable('blog_categories', Request::str('slug') ?: $name);
        Database::insert('blog_categories', ['name' => $name, 'slug' => $slug]);
        View::flash('success', 'Category added.');
        View::redirect('/admin/blog/');
    }
}
