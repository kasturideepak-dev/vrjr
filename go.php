<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
$root = __DIR__;
if (!is_dir($root . '/app') && is_dir(dirname($root) . '/app')) {
    $root = dirname($root);
}

$ok = static function (string $html): never {
    echo '<!DOCTYPE html><html><body style="font-family:system-ui;max-width:520px;margin:40px auto;padding:20px">';
    echo $html, '</body></html>';
    exit;
};

try {
    if (!defined('ROOT')) {
        define('ROOT', $root);
    }
    $cfg = is_file($root . '/config/database.local.php')
        ? require $root . '/config/database.local.php'
        : require $root . '/config/database.php';

    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'] ?? '3306', $cfg['database']),
        (string) $cfg['username'],
        (string) $cfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $hasPages = (bool) $pdo->query("SHOW TABLES LIKE 'pages'")->fetch();
    if (!$hasPages) {
        @unlink($root . '/storage/installed.lock');
        require $root . '/database/install.php';
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'vrj.cognizance360.com');
    $ok(
        '<h1>Done</h1>'
        . '<p>Database: <code>' . htmlspecialchars((string) $cfg['database'], ENT_QUOTES, 'UTF-8') . '</code></p>'
        . '<p>User: <code>' . htmlspecialchars((string) $cfg['username'], ENT_QUOTES, 'UTF-8') . '</code></p>'
        . '<p><a href="' . htmlspecialchars($base . '/', ENT_QUOTES, 'UTF-8') . '">Open website</a></p>'
        . '<p><a href="' . htmlspecialchars($base . '/admin/login/', ENT_QUOTES, 'UTF-8') . '">Open admin</a></p>'
        . '<p>admin@vrjuniorcollege.com<br>ChangeMe_VRJ2026</p>'
    );
} catch (Throwable $e) {
    $ok('<h1>Could not finish</h1><pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>');
}
