<?php
/**
 * MySQL connection. Hostinger: setup.php writes database.local.php.
 */
$local = __DIR__ . '/database.local.php';
if (is_file($local)) {
    return require $local;
}

$httpHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
$isLocal = $httpHost === '' || $httpHost === '127.0.0.1' || $httpHost === 'localhost'
    || str_starts_with($httpHost, '127.0.0.1:') || str_starts_with($httpHost, 'localhost:');

return [
    'driver' => 'mysql',
    'host' => getenv('DB_HOST') ?: ($isLocal ? '127.0.0.1' : 'localhost'),
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: ($isLocal ? 'vrj_cms' : ''),
    'username' => getenv('DB_USER') ?: ($isLocal ? 'root' : ''),
    'password' => getenv('DB_PASS') !== false && getenv('DB_PASS') !== '' ? getenv('DB_PASS') : '',
    'charset' => 'utf8mb4',
];
