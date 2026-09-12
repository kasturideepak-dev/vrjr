<?php
declare(strict_types=1);

final class Settings
{
    private static ?array $all = null;

    public static function all(): array
    {
        if (self::$all !== null) {
            return self::$all;
        }
        $rows = Database::all('SELECT setting_key, setting_value FROM settings');
        self::$all = [];
        foreach ($rows as $r) {
            self::$all[$r['setting_key']] = $r['setting_value'];
        }
        return self::$all;
    }

    public static function get(string $key, string $default = ''): string
    {
        return (string) (self::all()[$key] ?? $default);
    }

    public static function set(string $key, string $value): void
    {
        Database::query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$all = null;
        Cache::flush();
    }

    public static function many(array $pairs): void
    {
        foreach ($pairs as $k => $v) {
            self::set((string) $k, (string) $v);
        }
    }

    public static function reload(): void
    {
        self::$all = null;
    }
}
