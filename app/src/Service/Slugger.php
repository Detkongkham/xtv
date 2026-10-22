<?php
declare(strict_types=1);

namespace App\Service;

/** URL names: lowercase a-z, 0-9 and single hyphens ("Fetal & Maternal Monitor" -> "fetal-maternal-monitor"). */
final class Slugger
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public static function slugify(string $text, int $max = 120): string
    {
        $text = (string) preg_replace('/\p{S}+/u', ' ', $text); // ® ™ & … would otherwise become "r", "tm"
        $ascii = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()')?->transliterate($text) ?? strtolower($text);
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $ascii), '-');
        return rtrim(substr($slug, 0, $max), '-');
    }

    public static function isValid(string $slug, int $max = 120): bool
    {
        return strlen($slug) <= $max && preg_match(self::PATTERN, $slug) === 1;
    }
}
