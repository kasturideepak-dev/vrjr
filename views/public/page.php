<?php
$first = $sections[0] ?? [];
$isCampaign = ($first['type'] ?? '') === 'campaign_hero';
$firstContent = $first['content'] ?? [];
$courseVariant = preg_replace('/[^a-z]/', '', strtolower((string) ($firstContent['variant'] ?? '')));
if ($courseVariant === '' && $isCampaign && class_exists('Request')) {
    $path = Request::path();
    if (str_contains($path, 'bipc')) {
        $courseVariant = 'bipc';
    } elseif (str_contains($path, 'neet-long') || str_contains($path, 'long-term')) {
        $courseVariant = 'neet';
    } elseif (str_contains($path, 'mpc') || str_contains($path, 'iit-jee')) {
        $courseVariant = 'mpc';
    }
}
$bodyClass = trim(($bodyClass ?? '') . ($isCampaign ? ' is-campaign' : '') . ($courseVariant !== '' ? ' course-' . $courseVariant : ''));
require ROOT . '/views/public/_start.php';
foreach ($sections ?? [] as $sec) {
    $c = $sec['content'] ?? (json_decode($sec['content_json'] ?? '{}', true) ?: []);
    $type = $sec['type'] ?? '';
    $file = ROOT . '/views/public/sections/' . $type . '.php';
    if (is_file($file)) {
        require $file;
    }
}
require ROOT . '/views/public/_end.php';
