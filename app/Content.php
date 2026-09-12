<?php
declare(strict_types=1);

final class Content
{
    public static function sections(string $ownerType, int $ownerId, bool $visibleOnly = false): array
    {
        $sql = 'SELECT * FROM content_sections WHERE owner_type = ? AND owner_id = ?';
        if ($visibleOnly) {
            $sql .= ' AND is_visible = 1';
        }
        $sql .= ' ORDER BY sort_order, id';
        return Database::all($sql, [$ownerType, $ownerId]);
    }

    public static function snapshot(string $ownerType, int $ownerId, bool $live, string $note = ''): void
    {
        $sections = self::sections($ownerType, $ownerId);
        $pack = [];
        foreach ($sections as $s) {
            $pack[] = [
                'type' => $s['type'],
                'content' => json_decode($s['content_json'] ?: '{}', true) ?: [],
                'is_visible' => (int) $s['is_visible'],
            ];
        }
        $seoType = $ownerType === 'cpt' ? 'cpt' : $ownerType;
        $seo = Database::one('SELECT * FROM seo_metadata WHERE entity_type = ? AND entity_id = ?', [$seoType, $ownerId]) ?: [];
        if ($live) {
            Database::query('UPDATE content_revisions SET is_live = 0 WHERE owner_type = ? AND owner_id = ?', [$ownerType, $ownerId]);
        }
        Database::insert('content_revisions', [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'is_live' => $live ? 1 : 0,
            'snapshot_json' => Html::json(['sections' => $pack, 'seo' => $seo]),
            'note' => $note,
            'created_by' => Auth::id(),
        ]);
    }

    public static function liveSnapshot(string $ownerType, int $ownerId): ?array
    {
        $rev = Database::one(
            'SELECT snapshot_json FROM content_revisions WHERE owner_type = ? AND owner_id = ? AND is_live = 1 ORDER BY id DESC LIMIT 1',
            [$ownerType, $ownerId]
        );
        return $rev ? (json_decode($rev['snapshot_json'], true) ?: null) : null;
    }

    public static function saveSectionsFromPost(string $ownerType, int $ownerId): void
    {
        $ids = $_POST['section_id'] ?? [];
        $vis = $_POST['visible'] ?? [];
        foreach ($ids as $i => $sid) {
            $sid = (int) $sid;
            $sec = Database::one(
                'SELECT * FROM content_sections WHERE id = ? AND owner_type = ? AND owner_id = ?',
                [$sid, $ownerType, $ownerId]
            );
            if (!$sec) {
                continue;
            }
            $fields = SectionRegistry::fields($sec['type']);
            $content = json_decode($sec['content_json'] ?: '{}', true) ?: [];
            foreach ($fields as $f) {
                $key = 's' . $sid . '_' . $f['k'];
                if ($f['t'] === 'html') {
                    $content[$f['k']] = Html::allowedHtml((string) ($_POST[$key] ?? ''));
                } else {
                    $content[$f['k']] = trim((string) ($_POST[$key] ?? ''));
                }
            }
            $rawVis = $vis[$sid] ?? $sec['is_visible'];
            $isVis = $rawVis === '1' || $rawVis === 1 || $rawVis === 'on' || $rawVis === true || $rawVis === 'true';
            Database::update('content_sections', [
                'content_json' => Html::json($content),
                'sort_order' => (int) $i,
                'is_visible' => $isVis ? 1 : 0,
            ], 'id = ?', [$sid]);
        }
    }

    public static function addSection(string $ownerType, int $ownerId, string $type, ?int $tplId = null): int
    {
        $max = Database::one(
            'SELECT MAX(sort_order) m FROM content_sections WHERE owner_type = ? AND owner_id = ?',
            [$ownerType, $ownerId]
        );
        $content = SectionRegistry::defaults($type);
        if ($tplId) {
            $tpl = Database::one('SELECT * FROM section_templates WHERE id = ?', [$tplId]);
            if ($tpl) {
                $type = $tpl['type'];
                $content = json_decode($tpl['content_json'], true) ?: $content;
            }
        }
        return Database::insert('content_sections', [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'type' => $type,
            'content_json' => Html::json($content),
            'sort_order' => (int) ($max['m'] ?? 0) + 1,
            'is_visible' => 1,
            'section_template_id' => $tplId ?: null,
        ]);
    }

    public static function publish(string $ownerType, int $id): void
    {
        self::snapshot($ownerType, $id, true, 'Published');
        $now = date('Y-m-d H:i:s');
        if ($ownerType === 'page') {
            Database::update('pages', [
                'status' => 'published',
                'published_at' => $now,
                'updated_by' => Auth::id(),
            ], 'id = ?', [$id]);
        } elseif ($ownerType === 'cpt') {
            Database::update('cpt_entries', [
                'status' => 'published',
                'published_at' => $now,
            ], 'id = ?', [$id]);
        }
        Cache::flush();
    }

    public static function seoFromRequest(): array
    {
        return [
            'seo_title' => Request::str('seo_title'),
            'meta_description' => Request::str('meta_description'),
            'canonical_url' => Request::str('canonical_url'),
            'robots' => Request::str('robots') ?: 'index,follow',
            'og_title' => Request::str('og_title'),
            'og_description' => Request::str('og_description'),
            'og_image' => Request::str('og_image'),
            'twitter_title' => Request::str('twitter_title'),
            'twitter_description' => Request::str('twitter_description'),
            'twitter_image' => Request::str('twitter_image'),
        ];
    }

    public static function fillCanonical(array $seo, string $path): array
    {
        if (($seo['canonical_url'] ?? '') === '') {
            $seo['canonical_url'] = url($path);
        }
        return $seo;
    }

    public static function previewUrl(string $ownerType, int $ownerId, string $publicPath, ?int $snippetId = null): string
    {
        $token = bin2hex(random_bytes(32));
        $row = [
            'token' => $token,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_by' => Auth::id(),
        ];
        if ($snippetId && $snippetId > 0) {
            $row['snippet_id'] = $snippetId;
        }
        Database::insert('preview_tokens', $row);
        $path = $publicPath === '/' ? '/' : path_url($publicPath);
        return $path . '?preview=' . $token;
    }
}
