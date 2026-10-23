<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\Lang;

/**
 * One page rendered from the current data for /admin/preview, without writing anything (docs/plan/03-backend.md).
 * Links between pages are turned into preview links, so a click stays in the preview; assets and uploads keep
 * their ../ links, which reach public/ from /admin/.
 */
final class PagePreview
{
    public const URL = '/admin/preview';

    public function __construct(private SiteData $data, private string $publicDir)
    {
    }

    /** null = no such page (a hidden item, a product without a picture, a misspelt name). */
    public function render(string $page, string $lang, string $bar): ?string
    {
        $found = null;
        foreach (PageList::of($this->data) as $entry) {
            if (PageList::name($entry) === $page) {
                $found = $entry;
                break;
            }
        }
        // a draft news item with a body: shown as it will look once published
        if ($found === null && str_starts_with($page, 'news-') && ($n = $this->data->draftNews(substr($page, 5))) !== null && $this->data->hasPage($n)) {
            $found = ['news-article', ['n' => $n]];
        }
        if ($found === null) {
            return null;
        }
        $v = new SiteView(new Lang($lang), $this->data, $this->publicDir, '../assets', true);
        [, $html] = SiteBuilder::renderPage($v, $found[0], $found[1], true, $bar);
        return self::previewLinks($html, $lang);
    }

    /** href="contact.html#form" and href="../en/about.html" (language switch, hreflang) → preview URLs. */
    public static function previewLinks(string $html, string $lang): string
    {
        return (string) preg_replace_callback('#href="(?:\.\./(lo|en|th|zh)/)?([a-z0-9][a-z0-9-]*)\.html(\#[^"]*)?"#', static function (array $m) use ($lang): string {
            return 'href="' . self::url($m[2], $m[1] !== '' ? $m[1] : $lang) . ($m[3] ?? '') . '"';
        }, $html);
    }

    /** Preview URL, escaped for an href attribute. */
    public static function url(string $page, string $lang = 'lo'): string
    {
        return self::URL . '?page=' . rawurlencode($page) . '&amp;lang=' . $lang;
    }
}
