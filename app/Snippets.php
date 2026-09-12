<?php
declare(strict_types=1);

/**
 * Super-Admin code snippets: raw HTML/CSS/JS injected into the public site.
 * Access is locked at the role level (Auth::isSuper). Content is NOT passed
 * through Html::allowedHtml — that would break legitimate scripts.
 */
final class Snippets
{
    private static bool $booted = false;
    private static ?int $previewId = null;

    public static function setPreviewId(?int $id): void
    {
        self::$previewId = $id && $id > 0 ? $id : null;
    }

    public static function previewId(): ?int
    {
        return self::$previewId;
    }

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $admin = str_starts_with($path, '/admin');
        try {
            if ($admin) {
                self::ensureSchema();
                self::migrateFromSettings();
            }
        } catch (Throwable $e) {
            error_log('Snippets boot: ' . $e->getMessage());
        }
    }

    public static function ensureSchema(): void
    {
        $tables = Database::all("SHOW TABLES LIKE 'code_snippets'");
        if (!$tables) {
            Database::pdo()->exec(
                "CREATE TABLE IF NOT EXISTS code_snippets (
                  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  name VARCHAR(160) NOT NULL,
                  type ENUM('html','css','javascript') NOT NULL DEFAULT 'html',
                  code MEDIUMTEXT NOT NULL,
                  placement ENUM('header','body_start','footer') NOT NULL DEFAULT 'header',
                  scope ENUM('global','specific') NOT NULL DEFAULT 'global',
                  is_active TINYINT(1) NOT NULL DEFAULT 0,
                  activated_once TINYINT(1) NOT NULL DEFAULT 0,
                  priority INT NOT NULL DEFAULT 10,
                  notes TEXT NULL,
                  origin_key VARCHAR(80) NULL,
                  last_error TEXT NULL,
                  last_error_at DATETIME NULL,
                  created_by INT UNSIGNED NULL,
                  updated_by INT UNSIGNED NULL,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (id),
                  UNIQUE KEY uq_snippet_origin (origin_key),
                  KEY idx_snippets_active (is_active, placement, priority)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
        $targets = Database::all("SHOW TABLES LIKE 'snippet_targets'");
        if (!$targets) {
            Database::pdo()->exec(
                "CREATE TABLE IF NOT EXISTS snippet_targets (
                  snippet_id INT UNSIGNED NOT NULL,
                  target_type ENUM('page','cpt') NOT NULL,
                  target_id INT UNSIGNED NOT NULL,
                  PRIMARY KEY (snippet_id, target_type, target_id),
                  KEY idx_st_target (target_type, target_id),
                  CONSTRAINT fk_st_snippet FOREIGN KEY (snippet_id) REFERENCES code_snippets(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
        $col = Database::all("SHOW COLUMNS FROM preview_tokens LIKE 'snippet_id'");
        if (!$col) {
            Database::pdo()->exec('ALTER TABLE preview_tokens ADD COLUMN snippet_id INT UNSIGNED NULL AFTER owner_id');
            try {
                Database::pdo()->exec('ALTER TABLE preview_tokens ADD KEY idx_preview_snippet (snippet_id)');
            } catch (Throwable) {
            }
        }
    }

    public static function migrateFromSettings(): void
    {
        if (!self::tableReady()) {
            return;
        }

        $gtm = trim(Settings::get('gtm_id'));
        $ga = trim(Settings::get('analytics_id'));
        $pixel = trim(Settings::get('meta_pixel_id'));
        $header = (string) (Settings::all()['header_scripts'] ?? '');
        $footer = (string) (Settings::all()['footer_scripts'] ?? '');
        $hasTrackingKeys = array_key_exists('gtm_id', Settings::all())
            || array_key_exists('analytics_id', Settings::all())
            || array_key_exists('meta_pixel_id', Settings::all())
            || array_key_exists('header_scripts', Settings::all())
            || array_key_exists('footer_scripts', Settings::all());

        if ($gtm !== '') {
            self::seedOrigin(
                'tracking.gtm_head',
                'Google Tag Manager',
                'html',
                self::gtmHeadCode($gtm),
                'header',
                5,
                true,
                'Migrated from Settings → GTM ID (' . $gtm . '). Container snippet in <head>.'
            );
            self::seedOrigin(
                'tracking.gtm_body',
                'Google Tag Manager (noscript)',
                'html',
                self::gtmBodyCode($gtm),
                'body_start',
                5,
                true,
                'Migrated from Settings → GTM ID. Noscript iframe immediately after <body>.'
            );
        } else {
            self::seedOrigin(
                'tracking.gtm_head',
                'Google Tag Manager',
                'html',
                self::gtmHeadCode('GTM-XXXXXXX'),
                'header',
                5,
                false,
                'Pre-seeded GTM container. Replace GTM-XXXXXXX with your container ID, then activate.'
            );
            self::seedOrigin(
                'tracking.gtm_body',
                'Google Tag Manager (noscript)',
                'html',
                self::gtmBodyCode('GTM-XXXXXXX'),
                'body_start',
                5,
                false,
                'Pre-seeded GTM noscript. Keep the ID in sync with the head snippet.'
            );
        }

        $gaActive = $ga !== '';
        $gaId = $gaActive ? $ga : 'G-XXXXXXXXXX';
        self::seedOrigin(
            'tracking.ga',
            'Google Analytics',
            'html',
            self::gaCode($gaId),
            'header',
            20,
            $gaActive,
            $gaActive
                ? 'Migrated from Settings → GA ID (' . $ga . ').'
                : 'Pre-seeded GA4 / gtag snippet. Replace G-XXXXXXXXXX with your Measurement ID, then activate. Skip this if GTM already loads Analytics.'
        );

        $pxActive = $pixel !== '';
        $pxId = $pxActive ? $pixel : '000000000000000';
        self::seedOrigin(
            'tracking.meta_pixel',
            'Meta Pixel',
            'html',
            self::pixelCode($pxId),
            'header',
            30,
            $pxActive,
            $pxActive
                ? 'Migrated from Settings → Meta Pixel ID (' . $pixel . ').'
                : 'Pre-seeded Meta Pixel. Replace 000000000000000 with your Pixel ID, then activate.'
        );

        if (trim($header) !== '') {
            self::seedOrigin(
                'tracking.header_scripts',
                'Header scripts (migrated)',
                'html',
                $header,
                'header',
                40,
                true,
                'Migrated from Settings → Header scripts.'
            );
        }
        if (trim($footer) !== '') {
            self::seedOrigin(
                'tracking.footer_scripts',
                'Footer scripts (migrated)',
                'html',
                $footer,
                'footer',
                40,
                true,
                'Migrated from Settings → Footer scripts.'
            );
        }

        if ($hasTrackingKeys) {
            foreach (['gtm_id', 'analytics_id', 'meta_pixel_id', 'header_scripts', 'footer_scripts'] as $k) {
                Database::query('DELETE FROM settings WHERE setting_key = ?', [$k]);
            }
            Settings::reload();
        }
    }

    private static function seedOrigin(
        string $key,
        string $name,
        string $type,
        string $code,
        string $placement,
        int $priority,
        bool $active,
        string $notes
    ): void {
        $exists = Database::one('SELECT id FROM code_snippets WHERE origin_key = ?', [$key]);
        if ($exists) {
            return;
        }
        Database::insert('code_snippets', [
            'name' => $name,
            'type' => $type,
            'code' => $code,
            'placement' => $placement,
            'scope' => 'global',
            'is_active' => $active ? 1 : 0,
            'activated_once' => $active ? 1 : 0,
            'priority' => $priority,
            'notes' => $notes,
            'origin_key' => $key,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    public static function gtmHeadCode(string $id): string
    {
        $id = self::trackingId($id);
        return "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':Date.now(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$id}');</script>";
    }

    public static function gtmBodyCode(string $id): string
    {
        $id = self::trackingId($id);
        return '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $id . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
    }

    public static function gaCode(string $id): string
    {
        $id = self::trackingId($id);
        return '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $id . '"></script>' . "\n"
            . "<script>\nwindow.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', '{$id}');\n</script>";
    }

    public static function pixelCode(string $id): string
    {
        $id = self::trackingId($id);
        return "<script>\n!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?\nn.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;\nn.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;\nt.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',\n'https://connect.facebook.net/en_US/fbevents.js');\nfbq('init', '{$id}');\nfbq('track', 'PageView');\n</script>\n<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id={$id}&ev=PageView&noscript=1\"/></noscript>";
    }

    private static function trackingId(string $id): string
    {
        $clean = preg_replace('/[^A-Za-z0-9_-]/', '', $id) ?? '';
        return $clean !== '' ? $clean : 'INVALID';
    }

    public static function emit(string $placement, array $ctx = []): void
    {
        self::boot();
        if (!self::tableReady()) {
            return;
        }
        foreach (self::matching($placement, $ctx) as $row) {
            self::emitOne($row);
        }
    }

    /**
     * Render one snippet. Any PHP-level failure is swallowed so a single
     * broken snippet cannot take down the page shell.
     */
    public static function emitOne(array $row): void
    {
        $prev = set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        ob_start();
        try {
            echo self::compile($row);
            $buf = ob_get_clean();
            echo $buf;
            if (!empty($row['last_error'])) {
                self::clearError((int) $row['id']);
            }
        } catch (Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            self::recordFailure($row, $e);
        } finally {
            if ($prev) {
                set_error_handler($prev);
            } else {
                restore_error_handler();
            }
        }
    }

    public static function compile(array $row): string
    {
        $code = (string) ($row['code'] ?? '');
        $name = (string) ($row['name'] ?? 'untitled');
        if (strlen($code) > 524288) {
            throw new RuntimeException('Snippet "' . $name . '" exceeds 512 KB and was not rendered.');
        }
        if (str_contains($code, "\0")) {
            throw new RuntimeException('Snippet "' . $name . '" contains a null byte and was not rendered.');
        }
        $trim = ltrim($code);
        if (preg_match('/^<\?(php|=)/i', $trim)) {
            throw new RuntimeException('Snippet "' . $name . '" looks like PHP source. Snippets cannot execute PHP; it was skipped to protect the page.');
        }
        $type = (string) ($row['type'] ?? 'html');
        if ($type === 'css') {
            if (preg_match('/<style[\s>]/i', $trim)) {
                return $code;
            }
            return "<style>\n" . $code . "\n</style>";
        }
        if ($type === 'javascript') {
            if (preg_match('/<script[\s>]/i', $trim)) {
                return $code;
            }
            return "<script>\n" . $code . "\n</script>";
        }
        return $code;
    }

    public static function recordFailure(array $row, Throwable $e): void
    {
        $id = (int) ($row['id'] ?? 0);
        $name = (string) ($row['name'] ?? 'untitled');
        $msg = 'Snippet "' . $name . '" failed: ' . $e->getMessage();
        error_log($msg);
        if ($id < 1 || !self::tableReady()) {
            return;
        }
        try {
            Database::update('code_snippets', [
                'last_error' => substr($msg, 0, 2000),
                'last_error_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);
        } catch (Throwable $inner) {
            error_log('Could not store snippet error: ' . $inner->getMessage());
        }
    }

    private static function clearError(int $id): void
    {
        try {
            Database::query('UPDATE code_snippets SET last_error = NULL, last_error_at = NULL WHERE id = ?', [$id]);
        } catch (Throwable) {
        }
    }

    public static function matching(string $placement, array $ctx): array
    {
        if (!self::tableReady()) {
            return [];
        }
        $params = [$placement];
        $sql = 'SELECT * FROM code_snippets WHERE placement = ? AND (is_active = 1';
        if (self::$previewId) {
            $sql .= ' OR id = ?';
            $params[] = self::$previewId;
        }
        $sql .= ') ORDER BY priority ASC, id ASC';
        $rows = Database::all($sql, $params);
        if (!$rows) {
            return [];
        }

        $ownerType = (string) ($ctx['owner_type'] ?? '');
        $ownerId = (int) ($ctx['owner_id'] ?? 0);
        $ids = array_map(static fn ($r) => (int) $r['id'], $rows);
        $specific = array_filter($rows, static fn ($r) => ($r['scope'] ?? '') === 'specific');
        $targetMap = [];
        if ($specific) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            foreach (Database::all('SELECT snippet_id, target_type, target_id FROM snippet_targets WHERE snippet_id IN (' . $in . ')', $ids) as $t) {
                $targetMap[(int) $t['snippet_id']][] = $t['target_type'] . ':' . (int) $t['target_id'];
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if (self::$previewId && $id === self::$previewId) {
                $out[] = $row;
                continue;
            }
            if (($row['scope'] ?? 'global') === 'global') {
                $out[] = $row;
                continue;
            }
            $key = $ownerType . ':' . $ownerId;
            if ($ownerType !== '' && $ownerId > 0 && in_array($key, $targetMap[$id] ?? [], true)) {
                $out[] = $row;
            }
        }
        return $out;
    }

    public static function one(int $id): ?array
    {
        self::boot();
        $row = Database::one('SELECT * FROM code_snippets WHERE id = ?', [$id]);
        if (!$row) {
            return null;
        }
        $row['targets'] = Database::all(
            'SELECT target_type, target_id FROM snippet_targets WHERE snippet_id = ?',
            [$id]
        );
        return $row;
    }

    public static function saveTargets(int $snippetId, array $pairs): void
    {
        Database::delete('snippet_targets', 'snippet_id = ?', [$snippetId]);
        $seen = [];
        foreach ($pairs as $p) {
            $type = $p['type'] ?? '';
            $tid = (int) ($p['id'] ?? 0);
            if (!in_array($type, ['page', 'cpt'], true) || $tid < 1) {
                continue;
            }
            $k = $type . ':' . $tid;
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            Database::insert('snippet_targets', [
                'snippet_id' => $snippetId,
                'target_type' => $type,
                'target_id' => $tid,
            ]);
        }
    }

    public static function parseTargetInput(array $raw): array
    {
        $out = [];
        foreach ($raw as $v) {
            if (!is_string($v) || !preg_match('/^(page|cpt):(\d+)$/', $v, $m)) {
                continue;
            }
            $out[] = ['type' => $m[1], 'id' => (int) $m[2]];
        }
        return $out;
    }

    public static function hostFor(array $snippet): array
    {
        if (($snippet['scope'] ?? '') === 'specific') {
            $targets = $snippet['targets'] ?? Database::all(
                'SELECT target_type, target_id FROM snippet_targets WHERE snippet_id = ?',
                [(int) $snippet['id']]
            );
            foreach ($targets as $t) {
                if ($t['target_type'] === 'page') {
                    $page = Database::one('SELECT id, slug FROM pages WHERE id = ? AND deleted_at IS NULL', [(int) $t['target_id']]);
                    if ($page) {
                        $path = $page['slug'] === '/' ? '/' : (string) $page['slug'];
                        return ['page', (int) $page['id'], $path];
                    }
                }
                if ($t['target_type'] === 'cpt') {
                    $entry = Database::one('SELECT * FROM cpt_entries WHERE id = ? AND deleted_at IS NULL', [(int) $t['target_id']]);
                    if ($entry) {
                        $type = Cpt::typeById((int) $entry['post_type_id']);
                        if ($type && (int) $type['public'] === 1) {
                            $entry['_type'] = $type;
                            $path = trim(Cpt::permalink($entry, $type), '/');
                            return ['cpt', (int) $entry['id'], $path];
                        }
                    }
                }
            }
        }
        $home = Database::one('SELECT id, slug FROM pages WHERE slug = "/" AND deleted_at IS NULL');
        if ($home) {
            return ['page', (int) $home['id'], '/'];
        }
        $any = Database::one('SELECT id, slug FROM pages WHERE deleted_at IS NULL ORDER BY id LIMIT 1');
        if ($any) {
            return ['page', (int) $any['id'], $any['slug'] === '/' ? '/' : (string) $any['slug']];
        }
        return ['page', 0, '/'];
    }

    public static function previewUrl(array $snippet): string
    {
        [$ownerType, $ownerId, $path] = self::hostFor($snippet);
        if ($ownerId < 1) {
            return '/?preview-snippet=' . (int) $snippet['id'];
        }
        return Content::previewUrl($ownerType, $ownerId, $path, (int) $snippet['id']);
    }

    public static function scopeLabel(array $row, array $targets = []): string
    {
        if (($row['scope'] ?? 'global') === 'global') {
            return 'Global';
        }
        $n = count($targets);
        if ($n === 0) {
            $n = (int) (Database::one(
                'SELECT COUNT(*) c FROM snippet_targets WHERE snippet_id = ?',
                [(int) $row['id']]
            )['c'] ?? 0);
        }
        return $n === 1 ? '1 page' : $n . ' pages';
    }

    public static function targetCatalog(): array
    {
        $pages = Database::all(
            'SELECT id, title, slug FROM pages WHERE deleted_at IS NULL ORDER BY title'
        );
        $entries = Database::all(
            'SELECT e.id, e.title, e.slug, t.name AS type_name, t.slug AS type_slug
             FROM cpt_entries e
             JOIN post_types t ON t.id = e.post_type_id
             WHERE e.deleted_at IS NULL
             ORDER BY t.sort_order, t.name, e.title'
        );
        $grouped = [];
        foreach ($entries as $e) {
            $grouped[$e['type_name']][] = $e;
        }
        return ['pages' => $pages, 'cpt' => $grouped];
    }

    private static function tableReady(): bool
    {
        try {
            return Database::all("SHOW TABLES LIKE 'code_snippets'") !== [];
        } catch (Throwable) {
            return false;
        }
    }
}
