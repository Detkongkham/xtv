<?php
declare(strict_types=1);

namespace App\Publish;

/** The pages to build for one language: [template under templates/site/pages, variables]. Same list in every language. */
final class PageList
{
    /** fixed page names; a division slug (its page name) may not be one of these or start with a prefix below */
    public const RESERVED = ['index', 'about', 'vision', 'products', 'solutions', 'services', 'news', 'contact', 'downloads', 'faq',
        'privacy', 'terms', 'suppliers', 'sitemap', '404'];
    public const RESERVED_PREFIXES = ['product-', 'supplier-', 'news-'];

    public static function isReserved(string $page): bool
    {
        foreach (self::RESERVED_PREFIXES as $prefix) {
            if (str_starts_with($page, $prefix)) {
                return true;
            }
        }
        return in_array($page, self::RESERVED, true);
    }

    /** @return list<array{0: string, 1: array}> */
    public static function of(SiteData $data): array
    {
        $pages = [['home', []], ['about', []], ['vision', []], ['products', []]];
        foreach ($data->divisions() as $d) {
            $pages[] = ['division', ['d' => $d]];
        }
        foreach ($data->allProducts() as $place) {
            if ($place['product']['image_id'] !== null) {
                $pages[] = ['product', ['p' => $place['product']]];
            }
        }
        array_push($pages, ['solutions', []], ['services', []], ['news', []]);
        foreach ($data->news() as $n) {
            if ($data->hasPage($n)) {
                $pages[] = ['news-article', ['n' => $n]];
            }
        }
        array_push($pages, ['contact', []], ['downloads', []], ['faq', []],
            ['legal', ['slug' => 'privacy', 'keys' => ['pv1', 'pv2', 'pv3']]], ['legal', ['slug' => 'terms', 'keys' => ['tm1', 'tm2', 'tm3']]]);
        if ($data->suppliers()) {
            $pages[] = ['suppliers', []];
            foreach ($data->suppliers() as $s) {
                $pages[] = ['supplier', ['s' => $s]];
            }
        }
        array_push($pages, ['sitemap', []], ['404', []]);
        return $pages;
    }
}
