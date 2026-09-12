<?php
declare(strict_types=1);

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function rawPath(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return rawurldecode($uri);
    }

    public static function path(): string
    {
        $uri = self::rawPath();
        if (str_ends_with($uri, '/index.php')) {
            $uri = substr($uri, 0, -9) ?: '/';
        }
        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/') . '/';
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function str(string $key, string $default = ''): string
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_numeric($v) ? (int) $v : $default;
    }

    public static function bool(string $key): bool
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? '';
        return $v === '1' || $v === 'on' || $v === 'true';
    }

    public static function wantsJson(): bool
    {
        if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
            return true;
        }
        $h = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (strcasecmp($h, 'XMLHttpRequest') === 0) {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    public static function header(string $name): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return (string) ($_SERVER[$key] ?? '');
    }
}
