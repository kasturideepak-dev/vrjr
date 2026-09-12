<?php
declare(strict_types=1);

/**
 * Root-relative path with the site-wide trailing-slash convention.
 * Files with extensions (sitemap.xml, robots.txt, assets) are left as-is.
 */
function path_url(string $path): string
{
    if ($path === '' || $path === '/') {
        return '/';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    $path = '/' . ltrim($path, '/');
    $last = basename(parse_url($path, PHP_URL_PATH) ?: $path);
    if (str_contains($last, '.') && !str_ends_with($last, '.html')) {
        return $path;
    }
    return rtrim($path, '/') . '/';
}

/** Absolute production URL using BASE_URL. */
function url(string $path = '/'): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . path_url($path);
}

function asset_url(string $path): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $path;
}

/** Admin CSS/JS. Lives in public/assets-admin; also copied to /assets-admin on Hostinger. */
function admin_asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = ROOT . '/assets-admin/' . $path;
    if (!is_file($file)) {
        $file = ROOT . '/public/assets-admin/' . $path;
    }
    $v = is_file($file) ? filemtime($file) : time();
    return '/assets-admin/' . $path . '?v=' . $v;
}

/**
 * Serve /assets-admin and /uploads from disk when LiteSpeed sends them through index.php.
 * Hostinger’s document root is the project folder, so those files sit under public/.
 */
function serve_static_if_needed(): void
{
    if (!defined('ROOT') || PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        return;
    }
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method !== 'GET' && $method !== 'HEAD') {
        return;
    }
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uri = rawurldecode($uri);

    $map = [
        '/assets-admin/' => [ROOT . '/assets-admin/', ROOT . '/public/assets-admin/'],
        '/public/assets-admin/' => [ROOT . '/public/assets-admin/', ROOT . '/assets-admin/'],
        '/uploads/' => [ROOT . '/public/uploads/'],
        '/public/uploads/' => [ROOT . '/public/uploads/'],
    ];

    $rel = null;
    $bases = [];
    foreach ($map as $prefix => $dirs) {
        if (str_starts_with($uri, $prefix)) {
            $rel = substr($uri, strlen($prefix));
            $bases = $dirs;
            break;
        }
    }
    if ($rel === null || $rel === '' || str_contains($rel, '..') || str_contains($rel, "\0")) {
        return;
    }

    $file = null;
    foreach ($bases as $base) {
        $candidate = rtrim($base, '/\\') . '/' . $rel;
        if (!is_file($candidate)) {
            continue;
        }
        $real = realpath($candidate);
        $baseReal = realpath(rtrim($base, '/\\'));
        if ($real === false || $baseReal === false) {
            continue;
        }
        $basePrefix = $baseReal . DIRECTORY_SEPARATOR;
        if (!str_starts_with($real, $basePrefix)) {
            continue;
        }
        $file = $real;
        break;
    }
    if ($file === null) {
        return;
    }

    $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'mjs' => 'application/javascript; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'pdf' => 'application/pdf',
        'ico' => 'image/x-icon',
        'map' => 'application/json',
    ];
    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: public, max-age=604800');
    header('Content-Length: ' . (string) filesize($file));
    if ($method === 'HEAD') {
        exit;
    }
    readfile($file);
    exit;
}

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require ROOT . '/config/app.php';
    }
    if ($key === null) {
        return $cfg;
    }
    return $cfg[$key] ?? $default;
}
