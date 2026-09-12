<?php
declare(strict_types=1);

final class ImageOptimizer
{
    public static function optimize(string $path, string $mime, int $maxW = 1920): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            return;
        }
        $info = @getimagesize($path);
        if (!$info) {
            return;
        }
        [$w, $h] = $info;
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
        if (!$src) {
            return;
        }
        $nw = $w;
        $nh = $h;
        if ($w > $maxW) {
            $nw = $maxW;
            $nh = (int) round($h * ($maxW / $w));
        }
        $dst = imagecreatetruecolor($nw, $nh);
        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        if ($mime === 'image/jpeg') {
            imagejpeg($dst, $path, 82);
        } elseif ($mime === 'image/png') {
            imagepng($dst, $path, 6);
        } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
            imagewebp($dst, $path, 82);
        }
        if (function_exists('imagewebp') && $mime !== 'image/gif') {
            $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
            if ($webp && $webp !== $path) {
                imagewebp($dst, $webp, 80);
            }
        }
        imagedestroy($src);
        imagedestroy($dst);
    }
}
