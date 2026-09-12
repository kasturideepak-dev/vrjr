<?php
declare(strict_types=1);

final class Admin
{
    public static function dashboard(): void
    {
        $monthStart = date('Y-m-01 00:00:00');
        $stats = [
            'pages' => (int) (Database::one('SELECT COUNT(*) c FROM pages WHERE deleted_at IS NULL')['c'] ?? 0),
            'published' => (int) (Database::one('SELECT COUNT(*) c FROM pages WHERE status="published" AND deleted_at IS NULL')['c'] ?? 0),
            'types' => (int) (Database::one('SELECT COUNT(*) c FROM post_types WHERE status="active"')['c'] ?? 0),
            'entries' => (int) (Database::one('SELECT COUNT(*) c FROM cpt_entries WHERE deleted_at IS NULL')['c'] ?? 0),
            'posts' => (int) (Database::one('SELECT COUNT(*) c FROM blog_posts WHERE deleted_at IS NULL')['c'] ?? 0),
            'leads_month' => (int) (Database::one('SELECT COUNT(*) c FROM form_submissions WHERE created_at >= ?', [$monthStart])['c'] ?? 0),
            'leads_new' => (int) (Database::one('SELECT COUNT(*) c FROM form_submissions WHERE status="new"')['c'] ?? 0),
            'media' => (int) (Database::one('SELECT COUNT(*) c FROM media')['c'] ?? 0),
            'nf' => (int) (Database::one('SELECT COUNT(*) c FROM not_found_log')['c'] ?? 0),
        ];
        $recentLeads = Database::all('SELECT s.*, f.name AS form_name FROM form_submissions s JOIN forms f ON f.id = s.form_id ORDER BY s.id DESC LIMIT 6');
        $activity = Database::all('SELECT a.*, u.name AS user_name FROM audit_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 8');
        $lastBackup = Database::one('SELECT * FROM backups ORDER BY id DESC LIMIT 1');
        $nf = Database::all('SELECT * FROM not_found_log ORDER BY last_hit_at DESC LIMIT 6');
        $courses = Cpt::type('courses');
        $home = Database::one('SELECT id FROM pages WHERE slug = "/" AND deleted_at IS NULL');
        View::admin('dashboard', [
            'title' => 'Overview',
            'stats' => $stats,
            'recentLeads' => $recentLeads,
            'activity' => $activity,
            'lastBackup' => $lastBackup,
            'notFound' => $nf,
            'coursesType' => $courses,
            'homeId' => $home['id'] ?? null,
        ]);
    }

    public static function search(): void
    {
        $q = Request::str('q');
        $like = '%' . $q . '%';
        $pages = $q === '' ? [] : Database::all('SELECT id, title, slug, status FROM pages WHERE deleted_at IS NULL AND (title LIKE ? OR slug LIKE ?) LIMIT 10', [$like, $like]);
        $posts = $q === '' ? [] : Database::all('SELECT id, title, slug, status FROM blog_posts WHERE deleted_at IS NULL AND title LIKE ? LIMIT 10', [$like]);
        $entries = $q === '' ? [] : Database::all(
            'SELECT e.id, e.title, e.slug, e.status, t.slug AS type_slug, t.name AS type_name
             FROM cpt_entries e JOIN post_types t ON t.id = e.post_type_id
             WHERE e.deleted_at IS NULL AND e.title LIKE ? LIMIT 10',
            [$like]
        );
        $media = $q === '' ? [] : Database::all('SELECT id, original_name, public_path FROM media WHERE original_name LIKE ? OR alt_text LIKE ? LIMIT 10', [$like, $like]);
        if (Request::wantsJson()) {
            View::json(compact('pages', 'posts', 'entries', 'media', 'q'));
        }
        View::admin('search', ['title' => 'Search', 'q' => $q, 'pages' => $pages, 'posts' => $posts, 'entries' => $entries, 'media' => $media]);
    }

    public static function users(): void
    {
        Auth::requirePerm('users.view');
        $rows = Database::all('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id');
        View::admin('users/index', ['title' => 'Users', 'rows' => $rows]);
    }

    public static function userForm(?string $id = null): void
    {
        $row = $id ? Database::one('SELECT * FROM users WHERE id = ?', [(int) $id]) : null;
        if ($id && !$row) {
            http_response_code(404);
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        Auth::requirePerm($row ? 'users.edit' : 'users.create');
        $roles = Database::all('SELECT * FROM roles ORDER BY id');
        View::admin('users/form', ['title' => $row ? 'Edit user' : 'New user', 'row' => $row, 'roles' => $roles]);
    }

    public static function userSave(): void
    {
        $id = Request::int('id');
        Auth::requirePerm($id ? 'users.edit' : 'users.create');
        $email = strtolower(Request::str('email'));
        $data = [
            'name' => Request::str('name'),
            'email' => $email,
            'role_id' => Request::int('role_id'),
            'status' => Request::str('status') === 'inactive' ? 'inactive' : 'active',
            'two_factor_enabled' => Request::bool('two_factor_enabled') ? 1 : 0,
        ];
        $pass = (string) ($_POST['password'] ?? '');
        if ($pass !== '') {
            if (strlen($pass) < 10) {
                View::flash('error', 'Password must be at least 10 characters.');
                View::redirect($id ? '/admin/users/' . $id . '/' : '/admin/users/new/');
            }
            $data['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
        }
        if ($id) {
            Database::update('users', $data, 'id = ?', [$id]);
            Audit::log('user.updated', 'user', $id);
        } else {
            if ($pass === '') {
                View::flash('error', 'Password is required for new users.');
                View::redirect('/admin/users/new/');
            }
            $id = Database::insert('users', $data);
            Audit::log('user.created', 'user', $id);
        }
        View::flash('success', 'User saved.');
        View::redirect('/admin/users/');
    }

    public static function settings(): void
    {
        Auth::requirePerm('settings.view');
        Snippets::boot();
        View::admin('settings/index', [
            'title' => 'Site settings',
            's' => Settings::all(),
            'sheetsReady' => Sheets::configured(),
        ]);
    }

    public static function settingsSave(): void
    {
        Auth::requirePerm('settings.edit');
        $keys = [
            'brand_name','tagline','logo','favicon','phone_primary','phone_secondary','email','email_support',
            'whatsapp','hours','head_office','brochure','facebook','instagram','linkedin','youtube',
            'default_seo_title','default_seo_description','og_image',
            'schema_json','robots_txt','maintenance_mode','maintenance_message',
            'recaptcha_site','recaptcha_secret','backup_auto','backup_interval','backup_retention',
            'accent_color','404_heading','404_text',
        ];
        foreach ($keys as $k) {
            if ($k === 'maintenance_mode' || $k === 'backup_auto') {
                Settings::set($k, Request::bool($k) ? '1' : '0');
                continue;
            }
            Settings::set($k, (string) ($_POST[$k] ?? ''));
        }
        $smtpHost = Request::str('smtp_host') ?: 'smtp.gmail.com';
        $smtpEnc = Request::str('smtp_encryption') ?: 'tls';
        $smtpPort = Request::str('smtp_port') ?: ($smtpEnc === 'ssl' ? '465' : '587');
        $smtpUser = strtolower(Request::str('smtp_user'));
        $smtpFrom = Request::str('smtp_from_email') ?: $smtpUser;
        Settings::set('smtp_host', $smtpHost);
        Settings::set('smtp_port', $smtpPort);
        Settings::set('smtp_encryption', $smtpEnc);
        Settings::set('smtp_user', $smtpUser);
        Settings::set('smtp_from_email', $smtpFrom);
        Settings::set('smtp_from_name', Request::str('smtp_from_name') ?: Request::str('brand_name'));
        $pass = preg_replace('/\s+/', '', Request::str('smtp_pass')) ?? '';
        if ($pass !== '') {
            Settings::set('smtp_pass', $pass);
        }
        if (!empty($_FILES['sheets_json']['tmp_name']) && is_uploaded_file($_FILES['sheets_json']['tmp_name'])) {
            $raw = file_get_contents($_FILES['sheets_json']['tmp_name']) ?: '';
            $j = json_decode($raw, true);
            if ($j && !empty($j['client_email']) && !empty($j['private_key'])) {
                $dest = Sheets::keyPath();
                if (!is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0775, true);
                }
                file_put_contents($dest, $raw);
                @chmod($dest, 0600);
            } else {
                View::flash('error', 'That file is not a valid Google service-account JSON key.');
                View::redirect('/admin/settings/');
            }
        }
        Audit::log('settings.updated', 'settings', 0);
        View::flash('success', 'Settings saved.');
        View::redirect('/admin/settings/');
    }

    public static function smtpTest(): void
    {
        Auth::requirePerm('settings.edit');
        $to = Request::str('test_email') ?: Settings::get('smtp_user');
        $ok = Mailer::send([$to], 'VR CMS test email', '<p>SMTP is working for VR Junior College.</p>');
        View::flash($ok ? 'success' : 'error', $ok ? ('Test email sent to ' . $to . '.') : ('Could not send. ' . (Mailer::lastError() ?: 'Check SMTP settings.')));
        View::redirect('/admin/settings/');
    }

    public static function audit(): void
    {
        $rows = Database::all(
            'SELECT a.*, u.name AS user_name FROM audit_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 200'
        );
        View::admin('audit/index', ['title' => 'Activity log', 'rows' => $rows]);
    }
}
