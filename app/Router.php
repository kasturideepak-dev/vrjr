<?php
declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler, array $opts = []): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler', 'opts');
    }

    public function get(string $pattern, callable $handler, array $opts = []): void
    {
        $this->add('GET', $pattern, $handler, $opts);
    }

    public function post(string $pattern, callable $handler, array $opts = []): void
    {
        $this->add('POST', $pattern, $handler, $opts);
    }

    public function dispatch(): void
    {
        $method = Request::method();
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $path = Request::path();

        foreach ($this->routes as $r) {
            if ($r['method'] !== $method && $r['method'] !== 'ANY') {
                continue;
            }
            $regex = '#^' . $r['pattern'] . '$#';
            if (!preg_match($regex, $path, $m)) {
                continue;
            }
            $opts = $r['opts'];
            if (!empty($opts['auth']) && !Auth::check()) {
                if (Request::wantsJson()) {
                    View::json(['ok' => false, 'error' => 'auth'], 401);
                }
                View::redirect('/admin/login/');
            }
            if (!empty($opts['super'])) {
                Auth::requireSuper();
            }
            if (!empty($opts['perm'])) {
                Auth::requirePerm($opts['perm']);
            }
            if ($method === 'POST' && empty($opts['no_csrf'])) {
                if (!Csrf::checkRequest()) {
                    if (Request::wantsJson()) {
                        View::json(['ok' => false, 'error' => 'Invalid CSRF token'], 419);
                    }
                    http_response_code(419);
                    echo 'Invalid CSRF token';
                    return;
                }
            }
            $args = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            ($r['handler'])(...array_values($args));
            return;
        }

        http_response_code(404);
        if (str_starts_with($path, '/admin/')) {
            if (!Auth::check()) {
                View::redirect('/admin/login/');
            }
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        PublicSite::notFound();
    }
}
