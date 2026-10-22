<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\Lang;
use App\Core\View;

/** Renders the whole static site (every page in every language, root index.html, robots.txt) into a folder. */
final class SiteBuilder
{
    /**
     * @param string $publicDir  public/, where assets/ is read from (image sizes, WebP files, ?v= hashes)
     * @param string $assets     link to assets/ from a page in <out>/<lang>/
     * @param bool $preview      sample data: always noindex, with the preview bar
     */
    public function __construct(
        private SiteData $data,
        private string $publicDir,
        private string $assets = '../assets',
        private bool $preview = false,
    ) {
    }

    /** @return int pages written */
    public function build(string $outDir): int
    {
        $noindex = $this->preview || (bool) $this->data->setting('noindex', true);
        $pages = PageList::of($this->data);
        $count = 0;
        foreach (Lang::CODES as $code) {
            $v = new SiteView(new Lang($code), $this->data, $this->publicDir, $this->assets, $this->preview);
            $dir = $outDir . '/' . $code;
            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new \RuntimeException('Cannot create ' . $dir);
            }
            foreach ($pages as [$template, $vars]) {
                $body = $v->page($template, $vars);
                $meta = $v->takeMeta();
                $html = View::render('site/layout', ['v' => $v, 'meta' => $meta, 'body' => $body, 'noindex' => $noindex]);
                self::write($dir . '/' . $meta['slug'] . '.html', $v->versioned($html));
                $count++;
            }
        }
        self::write($outDir . '/index.html', '<!doctype html>' . "\n" . '<html lang="lo">' . "\n" . '<head>' . "\n" . '<meta charset="utf-8">' . "\n"
            . '<title>XTK</title>' . "\n" . '<meta http-equiv="refresh" content="0; url=lo/index.html">' . "\n" . '<link rel="canonical" href="lo/index.html">' . "\n"
            . ($noindex ? '<meta name="robots" content="noindex, nofollow">' . "\n" : '') . '</head>' . "\n" . '<body>' . "\n"
            . '<p><a href="lo/index.html">ລາວ</a> · <a href="en/index.html">English</a> · <a href="th/index.html">ไทย</a> · <a href="zh/index.html">中文</a></p>' . "\n"
            . '</body>' . "\n" . '</html>' . "\n");
        self::write($outDir . '/robots.txt', $noindex ? "User-agent: *\nDisallow: /\n" : "User-agent: *\nAllow: /\n");
        return $count;
    }

    private static function write(string $file, string $content): void
    {
        if (file_put_contents($file, $content) === false) {
            throw new \RuntimeException('Cannot write ' . $file);
        }
    }
}
