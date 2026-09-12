<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
$static = ['css' => 1, 'js' => 1, 'png' => 1, 'jpg' => 1, 'jpeg' => 1, 'webp' => 1, 'gif' => 1, 'svg' => 1, 'ico' => 1, 'woff' => 1, 'woff2' => 1, 'map' => 1];
$file = __DIR__ . $uri;
if ($uri !== '/' && isset($static[$ext])) {
    if (is_file($file)) {
        return false;
    }
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not found';
    exit;
}
if ($uri !== '/' && is_file($file)) {
    return false;
}
require __DIR__ . '/index.php';
