<?php
declare(strict_types=1);

try {
    if (!is_file(__DIR__ . '/storage/installed.lock')) {
        header('Location: /install.php');
        exit;
    }
    require __DIR__ . '/public/index.php';
} catch (Throwable $e) {
    if (str_contains($e->getMessage(), "doesn't exist")) {
        header('Location: /install.php');
        exit;
    }
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
}
