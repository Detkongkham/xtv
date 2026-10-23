<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\Lang;
use App\Core\View;

/**
 * Renders the whole static site into a folder: every page in every language, redirect pages for renamed slugs,
 * root index.html, robots.txt and (with a base URL) sitemap.xml.
 */
final class SiteBuilder
{
    /** pages per language (without redirects), known after build() */
    private array $slugs = [];

    /**
     * @param string $publicDir  public/, where assets/ is read from (image sizes, WebP files, ?v= hashes)
     * @param string $assets     link to assets/ from a page in <out>/<lang>/
     * @param bool $preview      sample data: always noindex, with the preview bar
     * @param ?string $baseUrl   full origin for sitemap.xml, canonical, hreflang and JSON-LD (without it: no sitemap, relative links)
     */
    public function __construct(
        private SiteData $data,
        private string $publicDir,
        private string $assets = '../assets',
        private bool $preview = false,
        private ?string $baseUrl = null,
    ) {
    }

    /** @return list<string> page names (no .html) written in each language */
    public function slugs(): array
    {
        return $this->slugs;
    }

    /** @return int pages written */
    public function build(string $outDir): int
    {
        $noindex = $this->preview || (bool) $this->data->setting('noindex', true);
        $pages = PageList::of($this->data);
        $count = 0;
        $this->slugs = [];
        foreach (Lang::CODES as $code) {
            $v = new SiteView(new Lang($code), $this->data, $this->publicDir, $this->assets, $this->preview, $this->baseUrl);
            $dir = $outDir . '/' . $code;
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new \RuntimeException('Cannot create ' . $dir);
            }
            foreach ($pages as [$template, $vars]) {
                $body = $v->page($template, $vars);
                $meta = $v->takeMeta();
                $html = View::render('site/layout', ['v' => $v, 'meta' => $meta, 'body' => $body, 'noindex' => $noindex]);
                self::write($dir . '/' . $meta['slug'] . '.html', $v->versioned($html));
                $this->slugs[$meta['slug']] = true;
                $count++;
            }
        }
        $this->slugs = array_map('strval', array_keys($this->slugs)); // '404' became an int key
        foreach ($this->redirectMap() as $from => $to) {
            foreach (Lang::CODES as $code) {
                self::write($outDir . '/' . $code . '/' . $from . '.html', self::redirectPage(Lang::HTML[$code], $to));
            }
        }
        if ($this->baseUrl !== null) {
            self::write($outDir . '/sitemap.xml', $this->sitemap());
        }
        self::write($outDir . '/index.html', '<!doctype html>' . "\n" . '<html lang="lo">' . "\n" . '<head>' . "\n" . '<meta charset="utf-8">' . "\n"
            . '<title>XTK</title>' . "\n" . '<meta http-equiv="refresh" content="0; url=lo/index.html">' . "\n" . '<link rel="canonical" href="lo/index.html">' . "\n"
            . ($noindex ? '<meta name="robots" content="noindex, nofollow">' . "\n" : '') . '</head>' . "\n" . '<body>' . "\n"
            . '<p><a href="lo/index.html">ລາວ</a> · <a href="en/index.html">English</a> · <a href="th/index.html">ไทย</a> · <a href="zh/index.html">中文</a></p>' . "\n"
            . '</body>' . "\n" . '</html>' . "\n");
        self::write($outDir . '/robots.txt', $noindex ? "User-agent: *\nDisallow: /\n" : "User-agent: *\nAllow: /\n");
        return $count;
    }

    /** Old page => final page. Old names that are pages again are skipped; chains are followed, loops dropped. */
    private function redirectMap(): array
    {
        $pages = array_flip($this->slugs);
        $next = [];
        foreach ($this->data->redirects() as $r) {
            if (!isset($pages[$r['from_page']])) {
                $next[$r['from_page']] = $r['to_page'];
            }
        }
        $map = [];
        foreach ($next as $from => $to) {
            $seen = [$from => true];
            while (isset($next[$to]) && !isset($seen[$to])) {
                $seen[$to] = true;
                $to = $next[$to];
            }
            if (!isset($seen[$to])) {
                $map[$from] = $to;
            }
        }
        return $map;
    }

    private static function redirectPage(string $htmlLang, string $to): string
    {
        $href = e($to) . '.html';
        return "<!doctype html>\n<html lang=\"{$htmlLang}\">\n<head>\n<meta charset=\"utf-8\">\n<title>XTK</title>\n"
            . "<meta http-equiv=\"refresh\" content=\"0; url={$href}\">\n<link rel=\"canonical\" href=\"{$href}\">\n"
            . "<meta name=\"robots\" content=\"noindex\">\n</head>\n<body>\n<p><a href=\"{$href}\">{$href}</a></p>\n</body>\n</html>\n";
    }

    /** Every page except 404, each with its other languages (and x-default) as alternates, like the page heads. */
    private function sitemap(): string
    {
        $base = rtrim((string) $this->baseUrl, '/');
        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($this->slugs as $slug) {
            if ($slug === '404') {
                continue;
            }
            $alts = '';
            foreach (Lang::CODES as $l) {
                $alts .= '<xhtml:link rel="alternate" hreflang="' . Lang::HTML[$l] . '" href="' . e("{$base}/{$l}/{$slug}.html") . '"/>';
            }
            $alts .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . e($base . '/' . Lang::X_DEFAULT . "/{$slug}.html") . '"/>';
            foreach (Lang::CODES as $code) {
                $out .= '<url><loc>' . e("{$base}/{$code}/{$slug}.html") . '</loc>' . $alts . "</url>\n";
            }
        }
        return $out . "</urlset>\n";
    }

    private static function write(string $file, string $content): void
    {
        if (file_put_contents($file, $content) === false) {
            throw new \RuntimeException('Cannot write ' . $file);
        }
    }
}
