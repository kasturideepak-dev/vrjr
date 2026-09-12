<?php
declare(strict_types=1);

final class SectionRegistry
{
    public static function all(): array
    {
        return [
            'hero' => ['label' => 'Hero', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'heading_highlight', 'l' => 'Highlighted phrase in heading', 't' => 'text'],
                ['k' => 'rotate_label', 'l' => 'Rotating line label', 't' => 'text'],
                ['k' => 'rotate', 'l' => 'Rotating phrases (one per line)', 't' => 'textarea'],
                ['k' => 'lead', 'l' => 'Description', 't' => 'textarea'],
                ['k' => 'image', 'l' => 'Background image', 't' => 'image'],
                ['k' => 'slides', 'l' => 'Slideshow images (one path per line)', 't' => 'textarea'],
                ['k' => 'figure', 'l' => 'Portrait image', 't' => 'image'],
                ['k' => 'cta_label', 'l' => 'Primary CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'Primary CTA URL', 't' => 'text'],
                ['k' => 'cta2_label', 'l' => 'Secondary CTA', 't' => 'text'],
                ['k' => 'cta2_url', 'l' => 'Secondary CTA URL', 't' => 'text'],
                ['k' => 'video_url', 'l' => 'Video URL', 't' => 'text'],
                ['k' => 'stats', 'l' => 'Stats (label|value per line)', 't' => 'textarea'],
            ]],
            'marquee' => ['label' => 'Marquee', 'fields' => [
                ['k' => 'items', 'l' => 'Items (one per line)', 't' => 'textarea'],
            ]],
            'about' => ['label' => 'About (text + images)', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'quote', 'l' => 'Lead text', 't' => 'textarea'],
                ['k' => 'image_main', 'l' => 'Main image', 't' => 'image'],
                ['k' => 'image_card', 'l' => 'Card image', 't' => 'image'],
                ['k' => 'badge_value', 'l' => 'Badge value', 't' => 'text'],
                ['k' => 'badge_label', 'l' => 'Badge label', 't' => 'text'],
                ['k' => 'points', 'l' => 'Points (title|text per line)', 't' => 'textarea'],
                ['k' => 'cta_label', 'l' => 'CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'CTA URL', 't' => 'text'],
            ]],
            'programs' => ['label' => 'Programmes grid', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'split_left_kicker', 'l' => 'Split left kicker', 't' => 'text'],
                ['k' => 'split_left_title', 'l' => 'Split left title', 't' => 'text'],
                ['k' => 'split_left_text', 'l' => 'Split left text', 't' => 'textarea'],
                ['k' => 'split_right_kicker', 'l' => 'Split right kicker', 't' => 'text'],
                ['k' => 'split_right_title', 'l' => 'Split right title', 't' => 'text'],
                ['k' => 'split_right_text', 'l' => 'Split right text', 't' => 'textarea'],
            ]],
            'approach' => ['label' => 'Six-step approach', 'fields' => [
                ['k' => 'lead', 'l' => 'Lead', 't' => 'text'],
                ['k' => 'steps', 'l' => 'Steps (title|text per line)', 't' => 'textarea'],
            ]],
            'why' => ['label' => 'Why choose us', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'cards', 'l' => 'Cards (title|text per line)', 't' => 'textarea'],
                ['k' => 'video_image', 'l' => 'Video still', 't' => 'image'],
                ['k' => 'video_url', 'l' => 'Video URL', 't' => 'text'],
            ]],
            'cta_banner' => ['label' => 'CTA banner', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'text', 'l' => 'Text', 't' => 'textarea'],
                ['k' => 'image', 'l' => 'Background', 't' => 'image'],
                ['k' => 'cta_label', 'l' => 'Primary CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'Primary URL', 't' => 'text'],
                ['k' => 'cta2_label', 'l' => 'Secondary CTA', 't' => 'text'],
                ['k' => 'cta2_url', 'l' => 'Secondary URL', 't' => 'text'],
            ]],
            'stats' => ['label' => 'Statistics', 'fields' => [
                ['k' => 'items', 'l' => 'Stats (value|label per line)', 't' => 'textarea'],
            ]],
            'faculty' => ['label' => 'Faculty slider', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
            ]],
            'facilities' => ['label' => 'Facilities cards', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'cards', 'l' => 'Cards JSON or title|text|image|tag per line', 't' => 'textarea'],
            ]],
            'campuses' => ['label' => 'Campus grid', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'note', 'l' => 'Note', 't' => 'textarea'],
            ]],
            'testimonials' => ['label' => 'Testimonials', 'fields' => [
                ['k' => 'label', 'l' => 'Label', 't' => 'text'],
            ]],
            'blog' => ['label' => 'Blog grid', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
            ]],
            'enquire' => ['label' => 'Contact / enquire', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
            ]],
            'campus_life' => ['label' => 'Campus life gallery band', 'fields' => [
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'cta_label', 'l' => 'CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'CTA URL', 't' => 'text'],
                ['k' => 'bg', 'l' => 'Background', 't' => 'image'],
                ['k' => 'shot1', 'l' => 'Shot 1', 't' => 'image'],
                ['k' => 'shot2', 'l' => 'Shot 2 (film)', 't' => 'image'],
                ['k' => 'shot3', 'l' => 'Shot 3', 't' => 'image'],
                ['k' => 'video_url', 'l' => 'Film URL', 't' => 'text'],
            ]],
            'faq' => ['label' => 'FAQ', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'items', 'l' => 'Questions (question|answer per line)', 't' => 'textarea'],
            ]],
            'page_hero' => ['label' => 'Inner page hero', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lead', 'l' => 'Lead', 't' => 'textarea'],
                ['k' => 'image', 'l' => 'Background image', 't' => 'image'],
                ['k' => 'figure', 'l' => 'Portrait image', 't' => 'image'],
                ['k' => 'crumb', 'l' => 'Current crumb', 't' => 'text'],
                ['k' => 'cta_label', 'l' => 'Primary CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'Primary CTA URL', 't' => 'text'],
                ['k' => 'cta2_label', 'l' => 'Secondary CTA', 't' => 'text'],
                ['k' => 'cta2_url', 'l' => 'Secondary CTA URL', 't' => 'text'],
            ]],
            'campaign_hero' => ['label' => 'Campaign hero', 'fields' => [
                ['k' => 'variant', 'l' => 'Layout (mpc / bipc / neet)', 't' => 'text'],
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lead', 'l' => 'Lead', 't' => 'textarea'],
                ['k' => 'chips', 'l' => 'Chips (one per line)', 't' => 'textarea'],
                ['k' => 'image', 'l' => 'Background', 't' => 'image'],
                ['k' => 'figure', 'l' => 'Card photo', 't' => 'image'],
                ['k' => 'crumb', 'l' => 'Crumb', 't' => 'text'],
                ['k' => 'offer_kicker', 'l' => 'Offer kicker', 't' => 'text'],
                ['k' => 'offer_title', 'l' => 'Offer title', 't' => 'text'],
                ['k' => 'offer_text', 'l' => 'Offer text', 't' => 'textarea'],
                ['k' => 'offer_points', 'l' => 'Offer points (one per line)', 't' => 'textarea'],
                ['k' => 'cta_label', 'l' => 'Primary CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'Primary URL', 't' => 'text'],
                ['k' => 'cta2_label', 'l' => 'Secondary CTA', 't' => 'text'],
                ['k' => 'cta2_url', 'l' => 'Secondary URL', 't' => 'text'],
            ]],
            'proof' => ['label' => 'Proof strip', 'fields' => [
                ['k' => 'items', 'l' => 'Items (title|text per line)', 't' => 'textarea'],
            ]],
            'highlight' => ['label' => 'Two-up highlight', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'items', 'l' => 'Items (kicker|title|text per line)', 't' => 'textarea'],
                ['k' => 'bg', 'l' => 'Background (paper/white)', 't' => 'text'],
            ]],
            'careers' => ['label' => 'Career / path cards', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'cards', 'l' => 'Cards (title|text per line)', 't' => 'textarea'],
                ['k' => 'bg', 'l' => 'Background (paper/white)', 't' => 'text'],
            ]],
            'vision_mission' => ['label' => 'Vision & mission', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'vision_kicker', 'l' => 'Left kicker', 't' => 'text'],
                ['k' => 'vision_title', 'l' => 'Left title', 't' => 'text'],
                ['k' => 'vision_text', 'l' => 'Left text', 't' => 'textarea'],
                ['k' => 'vision_items', 'l' => 'Left points (one per line)', 't' => 'textarea'],
                ['k' => 'mission_kicker', 'l' => 'Right kicker', 't' => 'text'],
                ['k' => 'mission_title', 'l' => 'Right title', 't' => 'text'],
                ['k' => 'mission_text', 'l' => 'Right text', 't' => 'textarea'],
                ['k' => 'mission_items', 'l' => 'Right points (one per line)', 't' => 'textarea'],
            ]],
            'process' => ['label' => 'Process steps', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'steps', 'l' => 'Steps (title|text per line)', 't' => 'textarea'],
                ['k' => 'note', 'l' => 'Note under steps', 't' => 'text'],
                ['k' => 'bg', 'l' => 'Background (paper/white)', 't' => 'text'],
            ]],
            'rich_text' => ['label' => 'Rich text', 'fields' => [
                ['k' => 'html', 'l' => 'HTML', 't' => 'html'],
            ]],
            'split' => ['label' => 'Text + image', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'html', 'l' => 'Body HTML', 't' => 'html'],
                ['k' => 'points', 'l' => 'Points (title|text per line)', 't' => 'textarea'],
                ['k' => 'image', 'l' => 'Image', 't' => 'image'],
                ['k' => 'image_left', 'l' => 'Image on left (1/0)', 't' => 'text'],
                ['k' => 'cta_label', 'l' => 'CTA', 't' => 'text'],
                ['k' => 'cta_url', 'l' => 'CTA URL', 't' => 'text'],
            ]],
            'features' => ['label' => 'Feature cards', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'cards', 'l' => 'Cards (kicker|title|text per line)', 't' => 'textarea'],
                ['k' => 'bg', 'l' => 'Background (paper/white)', 't' => 'text'],
            ]],
            'gallery' => ['label' => 'Gallery grid', 'fields' => [
                ['k' => 'images', 'l' => 'Image paths (one per line)', 't' => 'textarea'],
            ]],
            'video' => ['label' => 'Video embed', 'fields' => [
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'image', 'l' => 'Poster image', 't' => 'image'],
                ['k' => 'video_url', 'l' => 'Video URL', 't' => 'text'],
            ]],
            'video_testimonials' => ['label' => 'Video testimonials', 'fields' => [
                ['k' => 'kicker', 'l' => 'Eyebrow', 't' => 'text'],
                ['k' => 'heading', 'l' => 'Heading', 't' => 'text'],
                ['k' => 'lede', 'l' => 'Lede', 't' => 'textarea'],
                ['k' => 'items', 'l' => 'Videos (name|role|YouTube URL per line)', 't' => 'textarea'],
            ]],
            'html' => ['label' => 'Custom HTML', 'fields' => [
                ['k' => 'html', 'l' => 'HTML', 't' => 'html'],
            ]],
        ];
    }

    public static function label(string $type): string
    {
        return self::all()[$type]['label'] ?? $type;
    }

    public static function fields(string $type): array
    {
        return self::all()[$type]['fields'] ?? [];
    }

    public static function defaults(string $type): array
    {
        $out = [];
        foreach (self::fields($type) as $f) {
            $out[$f['k']] = '';
        }
        return $out;
    }

    public static function icon(string $type): string
    {
        return match ($type) {
            'hero', 'page_hero', 'campaign_hero' => 'H',
            'stats' => '#',
            'testimonials' => '“',
            'faq' => '?',
            'enquire' => '@',
            'faculty' => 'F',
            'gallery', 'campus_life' => '▣',
            'cta_banner' => '→',
            'video', 'video_testimonials' => '▶',
            'blog' => 'B',
            'about', 'split', 'rich_text' => 'T',
            'programs', 'features', 'careers' => '⊞',
            'campuses' => '⌖',
            default => strtoupper(substr($type, 0, 1)),
        };
    }

    public static function groupLabel(string $g): string
    {
        return match ($g) {
            'text' => 'Text content',
            'media' => 'Media',
            'cta' => 'Call to action',
            default => 'More fields',
        };
    }

    public static function fieldGroup(array $f): string
    {
        if (($f['t'] ?? '') === 'image' || in_array($f['k'] ?? '', ['image', 'figure', 'image_main', 'image_card', 'video_image', 'bg', 'shot1', 'shot2', 'shot3', 'images', 'og_image', 'slides'], true)) {
            return 'media';
        }
        $k = $f['k'] ?? '';
        if (str_starts_with($k, 'cta') || in_array($k, ['video_url'], true)) {
            return 'cta';
        }
        if (in_array($k, ['kicker', 'heading', 'lead', 'lede', 'quote', 'text', 'html', 'items', 'points', 'cards', 'steps', 'crumb', 'chips'], true)
            || str_contains($k, 'title') || str_contains($k, 'text') || str_contains($k, 'kicker')) {
            return 'text';
        }
        return 'more';
    }

    public static function groupedFields(string $type): array
    {
        $out = ['text' => [], 'media' => [], 'cta' => [], 'more' => []];
        foreach (self::fields($type) as $f) {
            $out[self::fieldGroup($f)][] = $f;
        }
        return array_filter($out);
    }
}
