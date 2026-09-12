<?php
declare(strict_types=1);

/**
 * Creates the database (if missing), imports schema.sql, and seeds content.
 * Usage: php database/install.php
 */
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}
require ROOT . '/app/helpers.php';

$localFile = ROOT . '/config/database.local.php';
if (!isset($cfg) || !is_array($cfg)) {
    $cfg = is_file($localFile)
        ? require $localFile
        : require ROOT . '/config/database.php';
}
$remote = (string) ($_SERVER['HTTP_HOST'] ?? '');
$looksDefault = (($cfg['username'] ?? '') === 'root' && ($cfg['password'] ?? '') === '');
if ($looksDefault && $remote !== '' && $remote !== '127.0.0.1' && !str_starts_with($remote, '127.0.0.1:') && $remote !== 'localhost') {
    throw new RuntimeException(
        'Do not use "root" with no password. That is only for your computer. In Hostinger go to Databases, create a MySQL database, then open /setup.php and paste that database name, user, and password.'
    );
}
foreach ([ROOT . '/storage/logs', ROOT . '/storage/backups', ROOT . '/storage/secrets', ROOT . '/public/uploads'] as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0775, true);
    }
}

$dbName = $cfg['database'];
if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
    throw new RuntimeException('Invalid database name in config/database.php');
}
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $dbName,
        $cfg['charset']
    );
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $opts);
} catch (PDOException $e) {
    $dsnHost = sprintf('mysql:host=%s;port=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['charset']);
    $pdo = new PDO($dsnHost, $cfg['username'], $cfg['password'], $opts);
    try {
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `' . $dbName . '`');
    } catch (PDOException $e2) {
        throw new RuntimeException(
            'Cannot use database `' . $dbName . '`. Create it in hPanel and put the exact name, user, and password in config/database.php. ' . $e2->getMessage()
        );
    }
}

$sql = file_get_contents(ROOT . '/database/schema.sql') ?: '';
$sql = preg_replace('/^--.*$/m', '', $sql) ?? $sql;
$st = '';
foreach (preg_split("/\n/", $sql) ?: [] as $line) {
    $trim = ltrim($line);
    if ($trim === '' || str_starts_with($trim, '--')) {
        continue;
    }
    $st .= $line . "\n";
    if (str_ends_with(rtrim($line), ';')) {
        $pdo->exec($st);
        $st = '';
    }
}

require ROOT . '/app/bootstrap.php';
require ROOT . '/database/seed.php';

file_put_contents(ROOT . '/storage/installed.lock', date('c'));
if (PHP_SAPI === 'cli') {
    echo "Install complete.\n";
    echo "Admin:  " . rtrim(BASE_URL, '/') . "/admin/login/\n";
    echo "Email:  admin@vrjuniorcollege.com\n";
    echo "Pass:   ChangeMe_VRJ2026\n";
}
