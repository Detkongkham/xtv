<?php
declare(strict_types=1);

namespace App\Publish;

/**
 * Finds links and image sources in a built site that point at a missing file. A link that leaves the build
 * (../assets/..., /api/contact.php) is looked up in public/ instead. Outside links, mailto: and tel: are skipped.
 */
final class LinkChecker
{
    /** top-level names a build contains; everything else is read from public/ */
    public const BUILD_ITEMS = ['lo', 'en', 'th', 'zh', 'index.html', 'robots.txt', 'sitemap.xml'];

    private array $exists = [];

    public function __construct(private string $buildDir, private string $publicDir)
    {
    }

    /** @return list<string> "lo/page.html → target" for every broken link, at most $limit */
    public function broken(int $limit = 50): array
    {
        $out = [];
        $files = array_merge(glob($this->buildDir . '/index.html') ?: [], glob($this->buildDir . '/{lo,en,th,zh}/*.html', GLOB_BRACE) ?: []);
        foreach ($files as $file) {
            $rel = substr($file, strlen($this->buildDir) + 1);
            preg_match_all('/\s(href|src|srcset)="([^"]*)"/', (string) file_get_contents($file), $m, PREG_SET_ORDER);
            $urls = [];
            foreach ($m as [, $attr, $value]) {
                // srcset: "a.webp 480w, b.webp 960w"
                $urls = array_merge($urls, $attr === 'srcset' ? array_map(static fn ($c) => preg_split('/\s+/', trim($c))[0], explode(',', $value)) : [$value]);
            }
            foreach (array_unique($urls) as $url) {
                $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($url === '' || $url[0] === '#' || preg_match('#^([a-z][a-z0-9+.-]*:|//)#i', $url)) {
                    continue;
                }
                if (!$this->resolves(dirname($rel), $url)) {
                    $out[] = $rel . ' → ' . $url;
                    if (count($out) >= $limit) {
                        return $out;
                    }
                }
            }
        }
        return $out;
    }

    private function resolves(string $dir, string $url): bool
    {
        $path = (string) preg_replace('/[?#].*$/s', '', $url);
        $parts = [];
        foreach (explode('/', ($path[0] === '/' ? '' : $dir . '/') . $path) as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }
            if ($p === '..') {
                if ($parts === []) {
                    return false; // above the docroot
                }
                array_pop($parts);
                continue;
            }
            $parts[] = rawurldecode($p);
        }
        if ($parts === []) {
            return true; // the site root
        }
        $root = in_array($parts[0], self::BUILD_ITEMS, true) ? $this->buildDir : $this->publicDir;
        $file = $root . '/' . implode('/', $parts);
        return $this->exists[$file] ??= is_file($file) || is_file($file . '/index.html');
    }
}
