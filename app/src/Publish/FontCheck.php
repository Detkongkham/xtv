<?php
declare(strict_types=1);

namespace App\Publish;

/**
 * The Chinese web font is a subset with only the characters the site used when it was made. The server cannot
 * make a new subset (no Python), so the Publisher only warns: missing characters show in the visitor's system
 * font until a developer runs `scripts/optimize_media.py --zh-font` and deploys.
 */
final class FontCheck
{
    /** CJK punctuation, ideographs and full-width forms, as in optimize_media.py */
    private const CJK = '/[\x{3000}-\x{303F}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{FF00}-\x{FFEF}]/u';

    /** @return list<string> characters on the zh pages that the font subset does not have */
    public static function missing(string $buildDir, string $charsFile): array
    {
        if (!is_file($charsFile)) {
            return [];
        }
        $have = array_flip(mb_str_split(trim((string) file_get_contents($charsFile))));
        $missing = [];
        foreach (glob($buildDir . '/zh/*.html') ?: [] as $file) {
            preg_match_all(self::CJK, (string) file_get_contents($file), $m);
            foreach ($m[0] as $ch) {
                if (!isset($have[$ch])) {
                    $missing[$ch] = true;
                }
            }
        }
        return array_keys($missing);
    }
}
