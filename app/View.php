<?php
declare(strict_types=1);

final class View
{
    public static function admin(string $template, array $data = []): void
    {
        $data['user'] = Auth::user();
        $data['flash'] = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        $data['postTypesNav'] = class_exists('Cpt') ? Cpt::activeTypes() : [];
        extract($data, EXTR_SKIP);
        $contentTemplate = ROOT . '/views/admin/' . $template . '.php';
        if (!is_file($contentTemplate)) {
            http_response_code(500);
            echo 'Missing view: ' . Html::e($template);
            return;
        }
        $bare = in_array($template, ['auth/login', 'auth/forgot', 'auth/reset', 'auth/two-factor', 'errors/403'], true);
        ob_start();
        require $contentTemplate;
        $content = ob_get_clean();
        if ($bare) {
            echo $content;
            return;
        }
        require ROOT . '/views/admin/layout.php';
    }

    public static function public(string $template, array $data = []): void
    {
        $file = ROOT . '/views/public/' . $template . '.php';
        if (!is_file($file) || filesize($file) === 0) {
            error_log('Missing or empty public view: ' . $template);
            http_response_code(500);
            echo 'Missing view: ' . Html::e($template);
            return;
        }
        extract($data, EXTR_SKIP);
        require $file;
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
    }

    public static function redirect(string $to, int $code = 302): never
    {
        if (str_starts_with($to, 'http://') || str_starts_with($to, 'https://')) {
            $host = parse_url(BASE_URL, PHP_URL_HOST);
            $toHost = parse_url($to, PHP_URL_HOST);
            if ($host && $toHost && strcasecmp($host, $toHost) !== 0) {
                $to = '/';
            }
        }
        if (Request::wantsJson()) {
            self::json(['ok' => true, 'redirect' => $to]);
        }
        header('Location: ' . $to, true, $code);
        exit;
    }

    public static function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo Html::json($data);
        exit;
    }
}
