<?php
declare(strict_types=1);

final class Cron
{
    public static function run(): array
    {
        $started = date('Y-m-d H:i:s');
        $messages = [];
        $ok = true;
        try {
            $n = self::publishScheduled();
            $messages[] = "Published $n scheduled item(s).";
            if (Settings::get('backup_auto', '0') === '1') {
                $freq = Settings::get('backup_interval', 'daily');
                $last = Database::one('SELECT created_at FROM backups WHERE kind = "scheduled" ORDER BY id DESC LIMIT 1');
                $due = true;
                if ($last) {
                    $hours = $freq === 'weekly' ? 24 * 7 : 24;
                    $due = strtotime($last['created_at']) < time() - ($hours * 3600);
                }
                if ($due) {
                    Backup::create('scheduled', 'Automatic backup');
                    $messages[] = 'Scheduled backup created.';
                }
            }
            Database::query('DELETE FROM preview_tokens WHERE expires_at < NOW()');
            Database::query('DELETE FROM password_resets WHERE expires_at < NOW()');
            Database::query('DELETE FROM two_factor_codes WHERE expires_at < NOW()');
            Database::query('DELETE FROM cache_keys WHERE expires_at IS NOT NULL AND expires_at < NOW()');
        } catch (Throwable $e) {
            $ok = false;
            $messages[] = $e->getMessage();
            error_log('Cron: ' . $e->getMessage());
        }
        Database::insert('cron_runs', [
            'task' => 'main',
            'started_at' => $started,
            'finished_at' => date('Y-m-d H:i:s'),
            'status' => $ok ? 'ok' : 'error',
            'message' => implode(' ', $messages),
        ]);
        return ['ok' => $ok, 'messages' => $messages];
    }

    public static function publishScheduled(): int
    {
        $stamp = ROOT . '/storage/tmp/cron-last.txt';
        $dir = dirname($stamp);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_file($stamp) && (time() - (int) @filemtime($stamp)) < 60) {
            return 0;
        }
        @touch($stamp);
        $n = 0;
        $now = date('Y-m-d H:i:s');
        foreach (Database::all('SELECT id FROM pages WHERE status = "scheduled" AND deleted_at IS NULL AND scheduled_at IS NOT NULL AND scheduled_at <= ?', [$now]) as $p) {
            Content::publish('page', (int) $p['id']);
            $n++;
        }
        foreach (Database::all('SELECT id FROM cpt_entries WHERE status = "scheduled" AND deleted_at IS NULL AND scheduled_at IS NOT NULL AND scheduled_at <= ?', [$now]) as $p) {
            Content::publish('cpt', (int) $p['id']);
            $n++;
        }
        foreach (Database::all('SELECT id FROM blog_posts WHERE status = "scheduled" AND deleted_at IS NULL AND scheduled_at IS NOT NULL AND scheduled_at <= ?', [$now]) as $p) {
            Database::update('blog_posts', [
                'status' => 'published',
                'published_at' => $now,
            ], 'id = ?', [(int) $p['id']]);
            $n++;
        }
        if ($n) {
            Cache::flush();
        }
        return $n;
    }
}
