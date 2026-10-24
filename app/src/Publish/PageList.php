<?php
declare(strict_types=1);

namespace App\Publish;

/** The pages to build for one language: [template under templates/site/pages, variables]. Same list in every language. */
final class PageList
{
    /** fixed page names; a division slug (its page name) may not be one of these or start with a prefix below */
    public const RESERVED = ['index', 'about', 'vision', 'products', 'services', 'news', 'contact', 'downloads', 'faq',
        'privacy', 'terms', 'suppliers', 'sitemap', '404'];
    public const RESERVED_PREFIXES = ['product-', 'supplier-', 'news-'];
    /**
     * Fixed pages whose title and description can be changed in /admin/seo (seo_pages), with the text keys the page
     * templates use when nothing is entered. The home page title is "XTK — <co_name>"; the others get " | XTK".
     */
    public const SEO = [
        'index' => ['co_name', 'co_tag'], 'about' => ['about_profile', 'about_lead'], 'vision' => ['vision', 'vision_p'],
        'products' => ['products', 'prod_lead'], 'services' => ['services', 'srv_lead'],
        'news' => ['news', 'news_lead'], 'suppliers' => ['sup_h', 'sup_lead'], 'contact' => ['contact', 'contact_lead'],
        'downloads' => ['downloads', 'dl_lead'], 'faq' => ['faq', 'faq_lead'],
    ];

    public static function isReserved(string $page): bool
    {
        foreach (self::RESERVED_PREFIXES as $prefix) {
            if (str_starts_with($page, $prefix)) {
                return true;
            }
        }
        return in_array($page, self::RESERVED, true);
    }

    /**
     * Page names (no .html) that of() builds, for links chosen in the admin (hero buttons).
     * Same names as the templates give to $v->meta().
     * @return list<string>
     */
    public static function names(SiteData $data): array
    {
        return array_map(self::name(...), self::of($data));
    }

    /** @param array{0: string, 1: array} $page an entry of of() */
    public static function name(array $page): string
    {
        return match ($page[0]) {
            'home' => 'index',
            'division' => $page[1]['d']['slug'],
            'product' => 'product-' . $page[1]['p']['slug'],
            'news-article' => 'news-' . $page[1]['n']['slug'],
            'supplier' => 'supplier-' . $page[1]['s']['slug'],
            'legal' => $page[1]['slug'],
            default => $page[0],
        };
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
        array_push($pages, ['services', []], ['news', []]);
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
