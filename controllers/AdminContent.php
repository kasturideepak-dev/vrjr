<?php
declare(strict_types=1);

final class AdminContent
{
    public static function faqs(): void
    {
        Auth::requirePerm('faqs.view');
        View::admin('content/faqs', ['title' => 'FAQs', 'rows' => Database::all('SELECT * FROM faqs ORDER BY sort_order, id')]);
    }

    public static function faqSave(): void
    {
        Auth::requirePerm('faqs.edit');
        $id = Request::int('id');
        $data = [
            'question' => Request::str('question'),
            'answer' => Request::str('answer'),
            'sort_order' => Request::int('sort_order'),
            'is_visible' => Request::bool('is_visible') ? 1 : 0,
            'entity_type' => 'global',
        ];
        if ($id) {
            Database::update('faqs', $data, 'id = ?', [$id]);
        } else {
            Database::insert('faqs', $data);
        }
        Cache::flush();
        View::flash('success', 'FAQ saved.');
        View::redirect('/admin/faqs/');
    }

    public static function faqDelete(): void
    {
        Auth::requirePerm('faqs.edit');
        Database::delete('faqs', 'id = ?', [Request::int('id')]);
        View::redirect('/admin/faqs/');
    }

    public static function testimonials(): void
    {
        Auth::requirePerm('testimonials.view');
        Cpt::ensureSchema();
        View::admin('content/testimonials', ['title' => 'Testimonials', 'rows' => Database::all('SELECT * FROM testimonials ORDER BY sort_order, id')]);
    }

    public static function testimonialSave(): void
    {
        Auth::requirePerm('testimonials.edit');
        Cpt::ensureSchema();
        $id = Request::int('id');
        $data = [
            'name' => Request::str('name'),
            'role' => Request::str('role'),
            'quote' => Request::str('quote'),
            'initials' => Request::str('initials'),
            'video_url' => Request::str('video_url'),
            'sort_order' => Request::int('sort_order'),
            'is_visible' => Request::bool('is_visible') ? 1 : 0,
        ];
        if ($id) {
            Database::update('testimonials', $data, 'id = ?', [$id]);
        } else {
            Database::insert('testimonials', $data);
        }
        Cache::flush();
        View::flash('success', 'Testimonial saved.');
        View::redirect('/admin/testimonials/');
    }

    public static function testimonialDelete(): void
    {
        Auth::requirePerm('testimonials.edit');
        Database::delete('testimonials', 'id = ?', [Request::int('id')]);
        View::redirect('/admin/testimonials/');
    }

    public static function slugCheck(): void
    {
        $kind = Request::str('kind');
        $slug = Slug::make(Request::str('slug'));
        $id = Request::int('id');
        $typeId = Request::int('type_id');
        $ok = true;
        $msg = '';
        if (Slug::isReserved($slug)) {
            $ok = false;
            $msg = 'Reserved system route.';
        } elseif ($kind === 'page' && Slug::firstSegmentTaken($slug, ['page_id' => $id])) {
            $ok = false;
            $msg = 'Collides with another page or post type.';
        } elseif ($kind === 'post_type' && Slug::firstSegmentTaken($slug, ['post_type_id' => $id])) {
            $ok = false;
            $msg = 'Collides with a page or reserved route.';
        } elseif ($kind === 'entry') {
            $sql = 'SELECT id FROM cpt_entries WHERE post_type_id = ? AND slug = ? AND deleted_at IS NULL';
            $params = [$typeId, $slug];
            if ($id) {
                $sql .= ' AND id <> ?';
                $params[] = $id;
            }
            if (Database::one($sql, $params)) {
                $ok = false;
                $msg = 'Another entry in this type already uses that slug.';
            }
        }
        View::json(['ok' => $ok, 'slug' => $slug, 'message' => $msg]);
    }
}
