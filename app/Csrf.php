<?php
declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . Html::e(self::token()) . '">';
    }

    public static function check(?string $token): bool
    {
        $have = $_SESSION['_csrf'] ?? '';
        if (!is_string($token) || $have === '') {
            return false;
        }
        return hash_equals($have, $token);
    }

    public static function checkRequest(): bool
    {
        $token = $_POST['_csrf'] ?? Request::header('X-CSRF-Token') ?: Request::header('X-Csrf-Token');
        return self::check(is_string($token) ? $token : null);
    }
}
