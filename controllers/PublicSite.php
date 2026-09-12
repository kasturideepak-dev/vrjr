<?php
declare(strict_types=1);

final class PublicSite
{
    public static function handle(string $path): void
    {
        self::canonicalize();
        Snippets::boot();
        Cron::publishScheduled();

        if (Settings::get('maintenance_mode', '0') === '1' && empty($_GET['preview'])) {
            http_response_code(503);
            header('Retry-After: 3600');
            $msg = Settings::get('maintenance_message', 'We will be back shortly.');
            $settings = Settings::all();
            $headerMenu = [];
            $footerMenu = [];
            $asset = '/assets/';
            $seo = ['seo_title' => 'We’ll be right back | VR Junior College', 'robots' => 'noindex'];
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Maintenance</title>';
            echo '<meta name="robots" content="noindex"><link rel="stylesheet" href="/assets/css/tokens.css">';
            echo '<link rel="stylesheet" href="/assets/css/base.css"></head><body>';
            echo '<main class="section"><div class="container"><h1>We’ll be right back</h1><p>' . Html::e($msg) . '</p></div></main></body></html>';
            return;
        }

        if ($path === '/thank-you/') {
            self::thankYou();
            return;
        }
        if ($path === '/sitemap.xml/') {
            self::sitemap();
            return;
        }
        if ($path === '/robots.txt/') {
            header('Content-Type: text/plain; charset=utf-8');
            echo Settings::get(
                'robots_txt',
                "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /login/\nDisallow: /preview/\nDisallow: /api/\nSitemap: " . url('/sitemap.xml') . "\n"
            );
            return;
        }

        $lookup = $path === '/' ? '/' : trim($path, '/');
        $redir = Redirects::find($lookup);
        if ($redir) {
            Redirects::hit((int) $redir['id']);
            $code = (int) $redir['status_code'] ?: 301;
            header('Location: ' . self::abs($redir['to_path']), true, $code);
            exit;
        }

        $previewTok = $_GET['preview'] ?? '';
        if (is_string($previewTok) && $previewTok !== '') {
            $tok = Database::one(
                'SELECT * FROM preview_tokens WHERE token = ? AND expires_at > NOW()',
                [$previewTok]
            );
            if ($tok) {
                if (!empty($tok['snippet_id'])) {
                    Snippets::setPreviewId((int) $tok['snippet_id']);
                }
                self::renderPreview($tok);
                return;
            }
        }

        if ($path === '/') {
            $page = self::livePageBySlug('/');
            if ($page) {
                self::renderPage($page, false);
                return;
            }
        }

        if (preg_match('#^/blog/page/([0-9]+)/$#', $path, $m)) {
            self::renderBlogIndex((int) $m[1]);
            return;
        }
        if (preg_match('#^/blog/category/([a-z0-9-]+)/page/([0-9]+)/$#', $path, $m)) {
            self::renderBlogCategory($m[1], (int) $m[2]);
            return;
        }
        if (preg_match('#^/blog/category/([a-z0-9-]+)/$#', $path, $m)) {
            self::renderBlogCategory($m[1], 1);
            return;
        }
        if (preg_match('#^/blog/([a-z0-9-]+)/$#', $path, $m)) {
            if ($m[1] !== 'page' && $m[1] !== 'category') {
                self::renderPost($m[1]);
                return;
            }
        }
        if ($path === '/blog/') {
            self::renderBlogIndex(1);
            return;
        }

        $slug = trim($path, '/');
        if ($slug === '404') {
            self::notFound();
            return;
        }
        $page = self::livePageBySlug($slug);
        if ($page) {
            self::renderPage($page, false);
            return;
        }

        $parts = explode('/', $slug);
        if (count($parts) === 1) {
            $type = Cpt::type($parts[0]);
            if ($type && (int) $type['public'] === 1 && (int) $type['has_archive'] === 1) {
                self::renderCptArchive($type);
                return;
            }
        }
        if (count($parts) === 2) {
            $type = Cpt::type($parts[0]);
            if ($type && (int) $type['public'] === 1) {
                $entry = Cpt::entry($parts[0], $parts[1]);
                if ($entry && $entry['status'] === 'published') {
                    self::renderCptSingle($type, $entry, false);
                    return;
                }
            }
        }

        self::notFound();
    }

    public static function canonicalize(): void
    {
        $raw = Request::rawPath();
        if ($raw === '/index.php' || str_ends_with($raw, '/index.php')) {
            header('Location: ' . url('/'), true, 301);
            exit;
        }
        $lower = strtolower($raw);
        $need = false;
        $target = $raw;
        if ($raw !== $lower) {
            $need = true;
            $target = $lower;
        }
        $isFile = preg_match('/\.(xml|txt|json|js|css|png|jpe?g|webp|gif|svg|ico|zip|pdf)$/i', $target);
        if (!$isFile && $target !== '/' && !str_ends_with($target, '/')) {
            $need = true;
            $target .= '/';
        }
        if ($need) {
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            $loc = $target . ($qs !== '' ? ('?' . $qs) : '');
            header('Location: ' . $loc, true, 301);
            exit;
        }
    }

    public static function notFound(): void
    {
        http_response_code(404);
        $p = Request::path();
        $exists = Database::one('SELECT id FROM not_found_log WHERE path = ?', [$p]);
        if ($exists) {
            Database::query('UPDATE not_found_log SET hits = hits + 1, last_hit_at = NOW(), referrer = ?, ip = ? WHERE id = ?', [
                substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
                Request::ip(),
                (int) $exists['id'],
            ]);
        } else {
            Database::insert('not_found_log', [
                'path' => $p,
                'referrer' => substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
                'ip' => Request::ip(),
            ]);
        }
        $page = self::livePageBySlug('404');
        if ($page) {
            self::renderPage($page, false);
            return;
        }
        View::public('404', self::ctx([]));
    }

    private static function livePageBySlug(string $slug): ?array
    {
        $page = Database::one('SELECT * FROM pages WHERE slug = ? AND deleted_at IS NULL', [$slug]);
        if (!$page || $page['status'] !== 'published') {
            return null;
        }
        return $page;
    }

    private static function renderPreview(array $tok): void
    {
        if ($tok['owner_type'] === 'page') {
            $page = Database::one('SELECT * FROM pages WHERE id = ? AND deleted_at IS NULL', [(int) $tok['owner_id']]);
            if ($page) {
                self::renderPage($page, true);
                return;
            }
        }
        if ($tok['owner_type'] === 'cpt') {
            $entry = Database::one('SELECT * FROM cpt_entries WHERE id = ? AND deleted_at IS NULL', [(int) $tok['owner_id']]);
            if ($entry) {
                $type = Cpt::typeById((int) $entry['post_type_id']);
                if ($type) {
                    $entry = Cpt::decode($entry);
                    $entry['_type'] = $type;
                    self::renderCptSingle($type, $entry, true);
                    return;
                }
            }
        }
        self::notFound();
    }

    public static function renderFromSnapshot(array $page, array $snap, bool $preview = true): void
    {
        $sections = $snap['sections'] ?? [];
        $seo = $snap['seo'] ?? [];
        $path = $page['slug'] === '/' ? '/' : path_url($page['slug']);
        $seo = Content::fillCanonical($seo, $path);
        $seo['robots'] = 'noindex,nofollow';
        header('X-Robots-Tag: noindex, nofollow');
        header('X-Frame-Options: SAMEORIGIN');
        View::public('page', self::ctx([
            'page' => $page,
            'sections' => $sections,
            'seo' => $seo,
            'preview' => $preview,
            'breadcrumbs' => self::crumbs($page['title'], $path),
        ]));
    }

    public static function renderPage(array $page, bool $preview): void
    {
        $sections = [];
        $seo = [];
        if (!$preview) {
            $snap = Content::liveSnapshot('page', (int) $page['id']);
            if ($snap) {
                $sections = $snap['sections'] ?? [];
                $seo = $snap['seo'] ?? [];
            }
        }
        if (!$sections) {
            $raw = Content::sections('page', (int) $page['id'], true);
            $sections = [];
            foreach ($raw as $s) {
                $sections[] = [
                    'type' => $s['type'],
                    'content' => json_decode($s['content_json'] ?: '{}', true) ?: [],
                    'is_visible' => 1,
                ];
            }
            $seo = Database::one('SELECT * FROM seo_metadata WHERE entity_type = "page" AND entity_id = ?', [(int) $page['id']]) ?: [];
        }
        $path = $page['slug'] === '/' ? '/' : path_url($page['slug']);
        $seo = Content::fillCanonical($seo, $path);
        $ctx = self::ctx([
            'page' => $page,
            'sections' => $sections,
            'seo' => $seo,
            'preview' => $preview,
            'breadcrumbs' => self::crumbs($page['title'], $path),
        ]);
        header('X-Frame-Options: SAMEORIGIN');
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        View::public('page', $ctx);
    }

    private static function renderCptArchive(array $type): void
    {
        $entries = Cpt::published($type['slug']);
        $seo = Database::one('SELECT * FROM seo_metadata WHERE entity_type = "post_type" AND entity_id = ?', [(int) $type['id']]) ?: [];
        $path = path_url($type['slug']);
        $seo = Content::fillCanonical($seo, $path);
        if (empty($seo['seo_title'])) {
            $seo['seo_title'] = ($type['archive_title'] ?: $type['name']) . ' | VR Junior College';
        }
        View::public('cpt-archive', self::ctx([
            'type' => $type,
            'entries' => $entries,
            'seo' => $seo,
            'breadcrumbs' => self::crumbs($type['archive_title'] ?: $type['name'], $path),
        ]));
    }

    private static function renderCptSingle(array $type, array $entry, bool $preview): void
    {
        $sections = [];
        $seo = [];
        if (!$preview) {
            $snap = Content::liveSnapshot('cpt', (int) $entry['id']);
            if ($snap) {
                $sections = $snap['sections'] ?? [];
                $seo = $snap['seo'] ?? [];
            }
        }
        if (!$sections) {
            $raw = Content::sections('cpt', (int) $entry['id'], true);
            foreach ($raw as $s) {
                $sections[] = [
                    'type' => $s['type'],
                    'content' => json_decode($s['content_json'] ?: '{}', true) ?: [],
                    'is_visible' => 1,
                ];
            }
            $seo = Database::one('SELECT * FROM seo_metadata WHERE entity_type = "cpt" AND entity_id = ?', [(int) $entry['id']]) ?: [];
        }
        if (($type['slug'] ?? '') === 'ai') {
            $sections = self::withAiLandingDefaults($sections, $entry);
        }
        $path = Cpt::permalink($entry, $type);
        $seo = Content::fillCanonical($seo, $path);
        if (empty($seo['seo_title'])) {
            $seo['seo_title'] = $entry['title'] . ' | VR Junior College';
        }
        $crumbs = [
            ['Home', '/'],
            [$type['name'], (int) $type['has_archive'] ? Cpt::archiveUrl($type) : null],
            [$entry['title'], $path],
        ];
        View::public('cpt-single', self::ctx([
            'type' => $type,
            'entry' => $entry,
            'sections' => $sections,
            'seo' => $seo,
            'preview' => $preview,
            'breadcrumbs' => $crumbs,
        ]));
    }

    private static function renderPost(string $slug): void
    {
        $post = Database::one(
            'SELECT p.*, u.name AS author_name FROM blog_posts p LEFT JOIN users u ON u.id = p.author_id
             WHERE p.slug = ? AND p.status = "published" AND p.deleted_at IS NULL',
            [$slug]
        );
        if (!$post) {
            self::notFound();
            return;
        }
        $seo = Database::one('SELECT * FROM seo_metadata WHERE entity_type = "blog" AND entity_id = ?', [(int) $post['id']]) ?: [];
        $path = path_url('blog/' . $post['slug']);
        $seo = Content::fillCanonical($seo, $path);
        $cats = Database::all(
            'SELECT c.name, c.slug FROM blog_categories c
             JOIN blog_post_categories pc ON pc.category_id = c.id WHERE pc.post_id = ?',
            [(int) $post['id']]
        );
        $related = Database::all(
            'SELECT title, slug, excerpt, featured_image, published_at FROM blog_posts
             WHERE status = "published" AND deleted_at IS NULL AND id <> ? ORDER BY published_at DESC LIMIT 5',
            [(int) $post['id']]
        );
        View::public('post', self::ctx([
            'post' => $post,
            'seo' => $seo,
            'related' => $related,
            'categories' => $cats,
            'breadcrumbs' => [
                ['Home', '/'],
                ['Blog', '/blog/'],
                [$post['title'], $path],
            ],
        ]));
    }

    private static function renderBlogIndex(int $pageNum): void
    {
        self::renderBlogList(null, $pageNum);
    }

    private static function renderBlogCategory(string $catSlug, int $pageNum): void
    {
        $cat = Database::one('SELECT * FROM blog_categories WHERE slug = ?', [$catSlug]);
        if (!$cat) {
            self::notFound();
            return;
        }
        self::renderBlogList($cat, $pageNum);
    }

    private static function renderBlogList(?array $cat, int $pageNum): void
    {
        $per = 100;
        $pageNum = max(1, $pageNum);
        $offset = ($pageNum - 1) * $per;
        $params = [];
        $where = 'p.status = "published" AND p.deleted_at IS NULL';
        $join = '';
        if ($cat) {
            $join = ' JOIN blog_post_categories pc ON pc.post_id = p.id ';
            $where .= ' AND pc.category_id = ?';
            $params[] = (int) $cat['id'];
        }
        $count = Database::one("SELECT COUNT(*) c FROM blog_posts p $join WHERE $where", $params);
        $total = (int) ($count['c'] ?? 0);
        $pages = max(1, (int) ceil($total / $per));
        $posts = Database::all(
            "SELECT p.title, p.slug, p.excerpt, p.featured_image, p.published_at FROM blog_posts p $join WHERE $where ORDER BY p.published_at DESC LIMIT $per OFFSET $offset",
            $params
        );
        $categories = Database::all('SELECT * FROM blog_categories ORDER BY name');
        $canonicalPath = $cat ? path_url('blog/category/' . $cat['slug']) : path_url('blog');
        $seo = [
            'seo_title' => $cat ? ($cat['name'] . ' | Blog | VR Junior College') : 'Blog | VR Junior College',
            'canonical_url' => url($canonicalPath),
            'robots' => 'index,follow',
        ];
        if (!$cat) {
            $blogPage = Database::one('SELECT id FROM pages WHERE slug = "blog" AND deleted_at IS NULL');
            if ($blogPage) {
                $pageSeo = Database::one('SELECT * FROM seo_metadata WHERE entity_type = "page" AND entity_id = ?', [(int) $blogPage['id']]);
                if ($pageSeo) {
                    if (!empty($pageSeo['seo_title'])) {
                        $seo['seo_title'] = $pageSeo['seo_title'];
                    }
                    if (!empty($pageSeo['meta_description'])) {
                        $seo['meta_description'] = $pageSeo['meta_description'];
                    }
                    if (!empty($pageSeo['og_image'])) {
                        $seo['og_image'] = $pageSeo['og_image'];
                    }
                }
            }
        }
        $crumbs = [['Home', '/'], ['Blog', '/blog/']];
        if ($cat) {
            $crumbs[] = [$cat['name'], $canonicalPath];
        }
        View::public('blog-index', self::ctx([
            'posts' => $posts,
            'seo' => $seo,
            'pageNum' => $pageNum,
            'totalPages' => $pages,
            'cat' => $cat,
            'categories' => $categories,
            'canonicalPath' => $canonicalPath,
            'breadcrumbs' => $crumbs,
        ]));
    }

    public static function thankYou(): void
    {
        Snippets::boot();
        $msg = (string) ($_SESSION['_form_thanks'] ?? '');
        unset($_SESSION['_form_thanks']);
        if ($msg === '') {
            $msg = 'Thank you. Our admissions team will be in touch shortly.';
        }
        View::public('thank-you', self::ctx([
            'message' => $msg,
            'seo' => [
                'seo_title' => 'Thank you | VR Junior College',
                'meta_description' => 'Your enquiry has been received. Our admissions team will contact you shortly.',
                'robots' => 'noindex,follow',
                'canonical_url' => url('/thank-you/'),
            ],
        ]));
    }

    /** AI landings always show banner, content, video testimonials and campuses. */
    private static function withAiLandingDefaults(array $sections, array $entry): array
    {
        $have = [];
        foreach ($sections as $s) {
            $t = (string) ($s['type'] ?? '');
            if ($t !== '') {
                $have[$t] = true;
            }
        }
        $out = [];
        foreach ($sections as $s) {
            if (($s['type'] ?? '') === 'blog') {
                continue;
            }
            $out[] = $s;
        }
        $videos = Templates::aiVideoItems();
        if (empty($have['video_testimonials'])) {
            $insert = ['type' => 'video_testimonials', 'content' => $videos, 'is_visible' => 1];
            $placed = false;
            $next = [];
            foreach ($out as $s) {
                $next[] = $s;
                if (!$placed && in_array($s['type'] ?? '', ['split', 'rich_text', 'page_hero', 'campaign_hero'], true)) {
                    $next[] = $insert;
                    $placed = true;
                }
            }
            if (!$placed) {
                $next[] = $insert;
            }
            $out = $next;
        }
        $hasCampus = false;
        foreach ($out as $s) {
            if (($s['type'] ?? '') === 'campuses') {
                $hasCampus = true;
                break;
            }
        }
        if (!$hasCampus) {
            $out[] = [
                'type' => 'campuses',
                'content' => [
                    'kicker' => 'Hyderabad',
                    'heading' => 'Our campuses',
                    'note' => 'Visit a campus or open the location in Google Maps.',
                ],
                'is_visible' => 1,
            ];
        } else {
            foreach ($out as &$s) {
                if (($s['type'] ?? '') !== 'campuses') {
                    continue;
                }
                $c = $s['content'] ?? [];
                if (($c['heading'] ?? '') === '') {
                    $c['heading'] = 'Our campuses';
                }
                if (($c['kicker'] ?? '') === '') {
                    $c['kicker'] = 'Hyderabad';
                }
                $s['content'] = $c;
            }
            unset($s);
        }
        if (!$out) {
            $out[] = [
                'type' => 'page_hero',
                'content' => [
                    'kicker' => 'AI',
                    'heading' => $entry['title'] ?? 'AI',
                    'lead' => $entry['excerpt'] ?? '',
                    'image' => $entry['featured_image'] ?? '/assets/img/careers/ai-ml.jpg',
                    'crumb' => 'AI',
                ],
                'is_visible' => 1,
            ];
        }
        return $out;
    }

    public static function ctx(array $extra): array
    {
        $settings = Settings::all();
        if (!isset($extra['snippetCtx'])) {
            if (!empty($extra['page']['id'])) {
                $extra['snippetCtx'] = ['owner_type' => 'page', 'owner_id' => (int) $extra['page']['id']];
            } elseif (!empty($extra['entry']['id'])) {
                $extra['snippetCtx'] = ['owner_type' => 'cpt', 'owner_id' => (int) $extra['entry']['id']];
            } else {
                $extra['snippetCtx'] = [];
            }
        }
        return array_merge([
            'settings' => $settings,
            'headerMenu' => Menu::items('header'),
            'footerMenu' => Menu::items('footer'),
            'base' => BASE_URL,
            'asset' => '/assets/',
            'schemaOrg' => $settings['schema_json'] ?? '',
        ], $extra);
    }

    private static function crumbs(string $title, string $path): array
    {
        return [['Home', '/'], [$title, $path]];
    }

    public static function abs(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        return path_url($path);
    }

    public static function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $cached = Cache::get('sitemap');
        if ($cached) {
            echo $cached;
            return;
        }
        $urls = [];
        foreach (Database::all(
            'SELECT p.slug, p.updated_at, s.robots FROM pages p
             LEFT JOIN seo_metadata s ON s.entity_type = "page" AND s.entity_id = p.id
             WHERE p.status = "published" AND p.deleted_at IS NULL'
        ) as $p) {
            if ($p['slug'] === '404') {
                continue;
            }
            if (!empty($p['robots']) && str_contains(strtolower((string) $p['robots']), 'noindex')) {
                continue;
            }
            $loc = $p['slug'] === '/' ? url('/') : url($p['slug']);
            $urls[] = [$loc, $p['updated_at']];
        }
        foreach (Database::all('SELECT slug, published_at FROM blog_posts WHERE status = "published" AND deleted_at IS NULL') as $p) {
            $urls[] = [url('blog/' . $p['slug']), $p['published_at']];
        }
        foreach (Database::all('SELECT slug FROM blog_categories') as $c) {
            $urls[] = [url('blog/category/' . $c['slug']), date('Y-m-d')];
        }
        foreach (Cpt::publicTypes() as $t) {
            if ((int) $t['has_archive']) {
                $urls[] = [url($t['slug']), $t['updated_at']];
            }
            foreach (Cpt::published($t['slug']) as $e) {
                $urls[] = [url($t['slug'] . '/' . $e['slug']), $e['updated_at']];
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as [$loc, $last]) {
            $xml .= '<url><loc>' . Html::e($loc) . '</loc>';
            if ($last) {
                $xml .= '<lastmod>' . Html::e(substr((string) $last, 0, 10)) . '</lastmod>';
            }
            $xml .= '</url>';
        }
        $xml .= '</urlset>';
        Cache::set('sitemap', $xml, 600);
        echo $xml;
    }
}
