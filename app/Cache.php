<?php
declare(strict_types=1);

final class Cache
{
    public static function get(string $key): ?string
    {
        $row = Database::one(
            'SELECT cache_value FROM cache_keys WHERE cache_key = ? AND (expires_at IS NULL OR expires_at > NOW())',
            [$key]
        );
        return $row['cache_value'] ?? null;
    }

    public static function set(string $key, string $value, int $ttl = 300): void
    {
        $exp = date('Y-m-d H:i:s', time() + $ttl);
        Database::query(
            'INSERT INTO cache_keys (cache_key, cache_value, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE cache_value = VALUES(cache_value), expires_at = VALUES(expires_at)',
            [$key, $value, $exp]
        );
    }

    public static function forget(string $key): void
    {
        Database::delete('cache_keys', 'cache_key = ?', [$key]);
    }

    public static function flush(): void
    {
        Database::query('DELETE FROM cache_keys');
    }
}
