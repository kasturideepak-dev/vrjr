<?php
declare(strict_types=1);

/**
 * Hostinger one-page installer.
 * Production: https://vrjuniorcollege.com/install.php
 * Staging: https://vrj.cognizance360.com/install.php
 */
$root = __DIR__;
if (!is_dir($root . '/app') && is_dir(dirname($root) . '/app')) {
    $root = dirname($root);
}

if (PHP_VERSION_ID < 80200) {
    http_response_code(500);
    echo 'Need PHP 8.2+. This server: ' . PHP_VERSION;
    exit;
}

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$httpHost = (string) ($_SERVER['HTTP_HOST'] ?? 'vrjuniorcollege.com');
$url = ($https ? 'https://' : 'http://') . $httpHost;
$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

if (is_file($root . '/storage/installed.lock') && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $url . '/admin/login/');
    exit;
}

$dbPrefix = str_contains($httpHost, 'cognizance360.com') ? 'u223704158' : 'u625301217';
$name = $dbPrefix . '_vrjr';
$user = $dbPrefix . '_vrjruser';
$pass = '';
$err = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = trim((string) ($_POST['db_name'] ?? ''));
    $user = trim((string) ($_POST['db_user'] ?? ''));
    $pass = (string) ($_POST['db_pass'] ?? '');
    if ($name === '' || $user === '' || $user === 'root') {
        $err = 'Enter the Hostinger database name and user (starts with ' . $dbPrefix . '_), not root.';
    } elseif ($pass === '') {
        $err = 'Enter the database password.';
    } else {
        try {
            $local = [
                'driver' => 'mysql',
                'host' => 'localhost',
                'port' => '3306',
                'database' => $name,
                'username' => $user,
                'password' => $pass,
                'charset' => 'utf8mb4',
            ];
            $pdo = new PDO(
                'mysql:host=localhost;port=3306;dbname=' . $name . ';charset=utf8mb4',
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $t) {
                $t = preg_replace('/[^A-Za-z0-9_]/', '', (string) $t);
                if ($t !== '') {
                    $pdo->exec('DROP TABLE IF EXISTS `' . $t . '`');
                }
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            $phpCfg = "<?php\nreturn " . var_export($local, true) . ";\n";
            if (!is_dir($root . '/config')) {
                mkdir($root . '/config', 0775, true);
            }
            file_put_contents($root . '/config/database.local.php', $phpCfg);
            file_put_contents($root . '/config/database.php', $phpCfg);

            foreach (['storage', 'storage/logs', 'storage/backups', 'storage/cache', 'storage/secrets', 'storage/tmp', 'public/uploads'] as $d) {
                $path = $root . '/' . $d;
                if (!is_dir($path)) {
                    mkdir($path, 0775, true);
                }
                @chmod($path, 0775);
            }
            @unlink($root . '/storage/installed.lock');

            putenv('APP_URL=' . $url);
            $_ENV['APP_URL'] = $url;
            $_SERVER['APP_URL'] = $url;
            if (!defined('ROOT')) {
                define('ROOT', $root);
            }
            $cfg = $local;
            require $root . '/database/install.php';

            echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ready</title>';
            echo '<style>body{margin:0;font-family:system-ui,sans-serif;background:#0b1220;color:#fff;display:grid;place-items:center;min-height:100vh}';
            echo '.c{width:min(420px,92vw);padding:28px;background:#151c2c;border-radius:16px}a{display:block;margin:10px 0;padding:14px;background:#e87028;color:#fff;text-align:center;border-radius:10px;text-decoration:none;font-weight:700}</style></head><body><div class="c">';
            echo '<h1>Site is ready</h1>';
            echo '<a href="' . $h($url . '/') . '">Open website</a>';
            echo '<a href="' . $h($url . '/admin/login/') . '">Open admin</a>';
            echo '<p style="opacity:.75;font-size:14px">Email: admin@vrjuniorcollege.com<br>Password: ChangeMe_VRJ2026</p></div></body></html>';
            exit;
        } catch (Throwable $e) {
            $err = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install · VR Junior College</title>
  <style>
    body { margin:0; font-family:system-ui,sans-serif; background:#0b1220; color:#e8edf5; }
    .w { min-height:100vh; display:grid; place-items:center; padding:20px; }
    .c { width:min(440px,100%); background:#151c2c; padding:28px; border-radius:16px; }
    h1 { margin:0 0 8px; font-size:22px; }
    p { color:#9aa3b5; font-size:14px; line-height:1.45; }
    button { width:100%; height:46px; border:0; border-radius:10px; background:#e87028; color:#fff; font-weight:700; cursor:pointer; margin-top:8px; }
    .e { background:#3a1515; color:#ffb4b4; padding:10px 12px; border-radius:10px; font-size:13px; margin:12px 0; }
    label { display:grid; gap:6px; margin:12px 0 0; font-size:13px; font-weight:600; }
    input { font:inherit; padding:11px 12px; border-radius:10px; border:1px solid #2a3348; background:#0b1220; color:#fff; }
  </style>
</head>
<body>
  <div class="w"><div class="c">
    <h1>Install VR CMS</h1>
    <p>Paste the database name, user and password from Hostinger → Databases. Host is always <strong>localhost</strong>.</p>
    <?php if ($err !== ''): ?><div class="e"><?= $h($err) ?></div><?php endif; ?>
    <form method="post">
      <label>Database name <input name="db_name" value="<?= $h($name) ?>" required autocomplete="off"></label>
      <label>Database user <input name="db_user" value="<?= $h($user) ?>" required autocomplete="off"></label>
      <label>Database password <input type="password" name="db_pass" required autocomplete="new-password"></label>
      <button type="submit">Install</button>
    </form>
  </div></div>
</body>
</html>
