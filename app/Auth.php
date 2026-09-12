<?php
declare(strict_types=1);

final class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['uid']) || !empty($_SESSION['_2fa_pending'])) {
            return null;
        }
        static $cached = false;
        static $user = null;
        if ($cached) {
            return $user;
        }
        $cached = true;
        $user = Database::one(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.status = "active"',
            [(int) $_SESSION['uid']]
        );
        return $user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isSuper(): bool
    {
        $u = self::user();
        return $u && $u['role_slug'] === 'super-admin';
    }

    public static function permissions(): array
    {
        $u = self::user();
        if (!$u) {
            return [];
        }
        static $perms = null;
        if ($perms !== null) {
            return $perms;
        }
        $rows = Database::all(
            'SELECT p.slug FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?',
            [(int) $u['role_id']]
        );
        $perms = array_column($rows, 'slug');
        return $perms;
    }

    public static function can(string $perm): bool
    {
        $u = self::user();
        if (!$u) {
            return false;
        }
        if ($u['role_slug'] === 'super-admin') {
            return true;
        }
        return in_array($perm, self::permissions(), true);
    }

    public static function requirePerm(string $perm): void
    {
        if (!self::can($perm)) {
            self::forbid();
        }
    }

    public static function requireSuper(): void
    {
        if (!self::isSuper()) {
            self::forbid();
        }
    }

    private static function forbid(): never
    {
        if (Request::wantsJson()) {
            View::json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        http_response_code(403);
        View::admin('errors/403', ['title' => 'Forbidden']);
        exit;
    }

    public static function attempt(string $email, string $password, bool $remember = false): string
    {
        $email = strtolower(trim($email));
        $ip = Request::ip();
        $fails = Database::one(
            'SELECT COUNT(*) AS c FROM login_attempts
             WHERE (email = ? OR ip = ?) AND success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
            [$email, $ip]
        );
        if ((int) ($fails['c'] ?? 0) >= 8) {
            Database::insert('login_attempts', ['email' => $email, 'ip' => $ip, 'success' => 0]);
            return 'locked';
        }
        $user = Database::one('SELECT * FROM users WHERE email = ?', [$email]);
        $ok = $user && $user['status'] === 'active' && password_verify($password, $user['password_hash']);
        Database::insert('login_attempts', ['email' => $email, 'ip' => $ip, 'success' => $ok ? 1 : 0]);
        if (!$ok) {
            return 'invalid';
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            Database::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [(int) $user['id']]);
        }
        if ((int) $user['two_factor_enabled'] === 1) {
            self::issueTwoFactor((int) $user['id'], $user['email'], $user['name']);
            session_regenerate_id(true);
            $_SESSION['_2fa_pending'] = (int) $user['id'];
            $_SESSION['_2fa_remember'] = $remember ? 1 : 0;
            $_SESSION['_last_activity'] = time();
            return '2fa';
        }
        self::establish((int) $user['id'], $remember);
        return 'ok';
    }

    public static function establish(int $userId, bool $remember = false): void
    {
        session_regenerate_id(true);
        unset($_SESSION['_2fa_pending'], $_SESSION['_2fa_remember']);
        $_SESSION['uid'] = $userId;
        $_SESSION['_last_activity'] = time();
        $_SESSION['_remember'] = $remember ? 1 : 0;
        Database::update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => Request::ip(),
        ], 'id = ?', [$userId]);
        Audit::log('login', 'user', $userId);
        if ($remember) {
            self::issueRememberCookie($userId);
        }
    }

    public static function logout(): void
    {
        if (!empty($_COOKIE['vrj_remember'])) {
            $sel = explode(':', (string) $_COOKIE['vrj_remember'], 2)[0] ?? '';
            if ($sel !== '') {
                Database::delete('remember_tokens', 'selector = ?', [$sel]);
            }
            setcookie('vrj_remember', '', time() - 3600, '/', '', Request::isHttps(), true);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function issueRememberCookie(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $token = bin2hex(random_bytes(32));
        Database::insert('remember_tokens', [
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30),
        ]);
        $secure = Request::isHttps();
        setcookie('vrj_remember', $selector . ':' . $token, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function resumeRememberCookie(string $cookie): void
    {
        $parts = explode(':', $cookie, 2);
        if (count($parts) !== 2) {
            return;
        }
        [$selector, $token] = $parts;
        $row = Database::one(
            'SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW()',
            [$selector]
        );
        if (!$row || !hash_equals($row['token_hash'], hash('sha256', $token))) {
            return;
        }
        $user = Database::one('SELECT * FROM users WHERE id = ? AND status = "active"', [(int) $row['user_id']]);
        if (!$user) {
            return;
        }
        Database::delete('remember_tokens', 'id = ?', [(int) $row['id']]);
        self::establish((int) $user['id'], true);
    }

    public static function issueTwoFactor(int $userId, string $email, string $name): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Database::query('DELETE FROM two_factor_codes WHERE user_id = ? OR expires_at < NOW()', [$userId]);
        Database::insert('two_factor_codes', [
            'user_id' => $userId,
            'code_hash' => hash('sha256', $code),
            'expires_at' => date('Y-m-d H:i:s', time() + 600),
        ]);
        Mailer::send(
            [$email],
            'Your VR CMS sign-in code',
            '<p>Hi ' . Html::e($name) . ',</p><p>Your one-time sign-in code is:</p><p style="font-size:28px;letter-spacing:6px;font-weight:700">' . Html::e($code) . '</p><p>It expires in 10 minutes. If you did not try to sign in, ignore this email.</p>'
        );
    }

    public static function verifyTwoFactor(string $code): bool
    {
        $uid = (int) ($_SESSION['_2fa_pending'] ?? 0);
        if ($uid < 1) {
            return false;
        }
        $row = Database::one(
            'SELECT * FROM two_factor_codes WHERE user_id = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1',
            [$uid]
        );
        if (!$row) {
            return false;
        }
        if ((int) $row['attempts'] >= 5) {
            return false;
        }
        Database::update('two_factor_codes', ['attempts' => (int) $row['attempts'] + 1], 'id = ?', [(int) $row['id']]);
        if (!hash_equals($row['code_hash'], hash('sha256', trim($code)))) {
            return false;
        }
        Database::delete('two_factor_codes', 'id = ?', [(int) $row['id']]);
        $remember = !empty($_SESSION['_2fa_remember']);
        self::establish($uid, $remember);
        return true;
    }

    public static function createReset(string $email): bool
    {
        $email = strtolower(trim($email));
        $user = Database::one('SELECT * FROM users WHERE email = ? AND status = "active"', [$email]);
        if (!$user) {
            return true;
        }
        $token = bin2hex(random_bytes(32));
        Database::insert('password_resets', [
            'user_id' => (int) $user['id'],
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);
        $link = url('/admin/reset/' . $token . '/');
        Mailer::send(
            [$email],
            'Reset your VR CMS password',
            '<p>Hi ' . Html::e($user['name']) . ',</p><p>Reset your password using this link (valid for 1 hour):</p><p><a href="' . Html::e($link) . '">' . Html::e($link) . '</a></p><p>If you did not request this, you can ignore the email.</p>'
        );
        return true;
    }

    public static function consumeReset(string $token, string $password): bool
    {
        $row = Database::one(
            'SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()',
            [hash('sha256', $token)]
        );
        if (!$row) {
            return false;
        }
        Database::update('users', [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ], 'id = ?', [(int) $row['user_id']]);
        Database::update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $row['id']]);
        Database::delete('remember_tokens', 'user_id = ?', [(int) $row['user_id']]);
        Audit::log('password.reset', 'user', (int) $row['user_id']);
        return true;
    }
}
