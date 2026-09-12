<?php
declare(strict_types=1);

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

require_once ROOT . '/app/helpers.php';
serve_static_if_needed();

$app = require ROOT . '/config/app.php';
define('BASE_URL', rtrim((string) $app['url'], '/'));
define('APP_KEY', (string) $app['key']);

date_default_timezone_set($app['timezone']);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
$errorLog = ROOT . '/storage/logs/php.log';
if (!is_dir(dirname($errorLog))) {
    mkdir(dirname($errorLog), 0775, true);
}
ini_set('error_log', $errorLog);

spl_autoload_register(static function (string $class): void {
    $map = [
        ROOT . '/app/' . $class . '.php',
        ROOT . '/controllers/' . $class . '.php',
    ];
    foreach ($map as $f) {
        if (is_file($f)) {
            require $f;
            return;
        }
    }
});

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('vrj_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

set_exception_handler(static function (Throwable $e) use ($app): void {
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    $path = '/';
    try {
        $path = Request::path();
    } catch (Throwable) {
    }
    if (str_starts_with($path, '/admin/')) {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Server error</title></head><body>';
        echo '<h1>Server error</h1><p>Please try again. The error has been logged.</p>';
        if (!empty($app['debug'])) {
            echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        }
        echo '</body></html>';
        return;
    }
    $show = !empty($app['debug'])
        || (isset($_SERVER['HTTP_HOST']) && !str_contains((string) $_SERVER['HTTP_HOST'], 'vrjuniorcollege.com'));
    $file = ROOT . '/views/public/500.php';
    if (is_file($file) && !$show) {
        $settings = [];
        $headerMenu = [];
        $footerMenu = [];
        $asset = '/assets/';
        $seo = ['seo_title' => 'Server error | VR Junior College', 'robots' => 'noindex'];
        require $file;
        return;
    }
    echo '<h1>Something went wrong</h1>';
    if ($show) {
        echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    }
});

if (!empty($_SESSION['uid']) && empty($_SESSION['_2fa_pending'])) {
    $life = (int) ($app['session_lifetime'] ?? 3600);
    $last = (int) ($_SESSION['_last_activity'] ?? 0);
    if ($last > 0 && (time() - $last) > $life && empty($_SESSION['_remember'])) {
        Auth::logout();
    } else {
        $_SESSION['_last_activity'] = time();
    }
}

if (empty($_SESSION['uid']) && !empty($_COOKIE['vrj_remember'])) {
    Auth::resumeRememberCookie((string) $_COOKIE['vrj_remember']);
}
