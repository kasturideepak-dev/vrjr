<?php
/**
 * Application configuration.
 *
 * Production BASE_URL must be https://vrjuniorcollege.com (no trailing slash).
 * Override with environment variables on the server, or edit the defaults below.
 */
$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$detected = $host !== ''
    ? (($https ? 'https://' : 'http://') . $host)
    : 'https://vrjuniorcollege.com';

return [
    'name' => 'VR Junior College',
    'url' => rtrim((string) (getenv('APP_URL') ?: $detected), '/'),
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => (getenv('APP_DEBUG') ?: '0') === '1',
    'key' => getenv('APP_KEY') ?: 'CHANGE_ME_TO_A_RANDOM_64_CHAR_SECRET',
    'timezone' => 'Asia/Kolkata',
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 3600),
    'cron_key' => getenv('CRON_KEY') ?: 'change-this-cron-key',
    'uploads_max_bytes' => 8 * 1024 * 1024,
    'reserved_slugs' => [
        'admin', 'login', 'logout', 'preview', 'uploads', 'assets', 'assets-admin',
        'api', 'cron', 'install', 'sitemap.xml', 'robots.txt', 'blog', 'thank-you', 'enquire',
    ],
];
