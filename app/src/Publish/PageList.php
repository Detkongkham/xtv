<?php
declare(strict_types=1);

namespace App\Publish;

/** The pages to build for one language: [template under templates/site/pages, variables]. Same list in every language. */
final class PageList
{
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
