<?php
declare(strict_types=1);

final class Html
{
    public static function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function json($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    public static function lines(string $text): array
    {
        $out = [];
        foreach (preg_split("/\r\n|\n|\r/", $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $out[] = array_map('trim', explode('|', $line));
        }
        return $out;
    }

    public static function allowedHtml(string $html): string
    {
        $allowed = '<p><br><h2><h3><h4><ul><ol><li><strong><em><b><i><a><blockquote><img><table><thead><tbody><tr><th><td><hr><span><div><iframe>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/i', '', $clean) ?? $clean;
        return $clean;
    }

    public static function youtubeId(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $url)) {
            return $url;
        }
        return '';
    }

    public static function selected($a, $b): string
    {
        return (string) $a === (string) $b ? ' selected' : '';
    }

    public static function checked(bool $on): string
    {
        return $on ? ' checked' : '';
    }
}
