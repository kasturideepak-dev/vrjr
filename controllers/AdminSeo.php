<?php
declare(strict_types=1);

final class AdminSeo
{
    public static function index(): void
    {
        Auth::requirePerm('seo.view');
        Snippets::boot();
        View::admin('seo/index', [
            'title' => 'SEO',
            's' => Settings::all(),
            'pages' => Database::all(
                'SELECT p.id, p.title, p.slug, p.status, s.seo_title, s.meta_description, s.robots, s.canonical_url
                 FROM pages p LEFT JOIN seo_metadata s ON s.entity_type="page" AND s.entity_id = p.id
                 WHERE p.deleted_at IS NULL ORDER BY p.slug'
            ),
        ]);
    }

    public static function save(): void
    {
        Auth::requirePerm('seo.edit');
        foreach (['default_seo_title','default_seo_description','og_image','schema_json','robots_txt'] as $k) {
            Settings::set($k, (string) ($_POST[$k] ?? Settings::get($k)));
        }
        Audit::log('seo.updated', 'seo', 0);
        Cache::flush();
        View::flash('success', 'SEO settings saved.');
        View::redirect('/admin/seo/');
    }
}
