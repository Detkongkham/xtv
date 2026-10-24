<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Publish\FontCheck;
use App\Publish\LinkChecker;
use App\Publish\PageList;
use App\Publish\SiteBuilder;
use App\Publish\SiteData;
use PHPUnit\Framework\TestCase;

/** Builds the site from database/seed/seed.json (no database) and checks what the Publisher checks. */
final class SiteBuildTest extends TestCase
{
    private static string $out;
    private static string $public;
    private static SiteBuilder $builder;
    private static int $pages;

    public static function setUpBeforeClass(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        self::$public = dirname(__DIR__, 2) . '/public';
        self::$out = sys_get_temp_dir() . '/xtk-build-' . bin2hex(random_bytes(4));
        $seed = json_decode((string) file_get_contents(App::path('database/seed/seed.json')), true);
        $seed['redirects'] = [
            ['from_page' => 'product-old', 'to_page' => 'product-older'],
            ['from_page' => 'product-older', 'to_page' => 'product-ct-scan'],   // chain: old -> older -> ct-scan
            ['from_page' => 'loop-a', 'to_page' => 'loop-b'],
            ['from_page' => 'loop-b', 'to_page' => 'loop-a'],                   // loop: dropped
            ['from_page' => 'about', 'to_page' => 'vision'],                    // a live page: never replaced
        ];
        $file = self::$out . '.json';
        file_put_contents($file, json_encode($seed));
        self::$builder = new SiteBuilder(SiteData::fromSeedFile($file), self::$public, '../assets', false, 'https://new.example.com');
        self::$pages = self::$builder->build(self::$out);
        unlink($file);
    }

    public static function tearDownAfterClass(): void
    {
        exec('rm -rf ' . escapeshellarg(self::$out));
    }

    public function testEveryPageInEveryLanguage(): void
    {
        self::assertSame(272, self::$pages);
        self::assertCount(68, self::$builder->slugs());
        self::assertContainsOnly('string', self::$builder->slugs());
        foreach (['lo', 'en', 'th', 'zh'] as $lang) {
            self::assertFileExists(self::$out . "/{$lang}/product-ct-scan.html");
            self::assertStringEndsWith("</html>\n", (string) file_get_contents(self::$out . "/{$lang}/index.html"));
        }
        self::assertStringContainsString('Disallow: /', (string) file_get_contents(self::$out . '/robots.txt'));
    }

    public function testNoBrokenLinksAndNoMissingChineseCharacters(): void
    {
        self::assertSame([], (new LinkChecker(self::$out, self::$public))->broken());
        self::assertSame([], FontCheck::missing(self::$out, self::$public . '/assets/fonts/noto-sans-sc-sub.txt'));
    }

    public function testLinkCheckerFindsABrokenLink(): void
    {
        $dir = self::$out . '-broken';
        mkdir($dir . '/en', 0755, true);
        file_put_contents($dir . '/en/a.html', '<a href="b.html">b</a><img src="../assets/img/nope.jpg"><a href="tel:+856">t</a><a href="#x">x</a><a href="../../up.html">u</a><source srcset="../assets/img/logo.svg 480w, ../assets/img/gone.webp 960w">');
        $broken = (new LinkChecker($dir, self::$public))->broken();
        exec('rm -rf ' . escapeshellarg($dir));
        self::assertSame(['en/a.html → b.html', 'en/a.html → ../assets/img/nope.jpg', 'en/a.html → ../../up.html', 'en/a.html → ../assets/img/gone.webp'], $broken);
    }

    public function testRedirectPagesFollowChainsAndSkipLoopsAndLivePages(): void
    {
        foreach (['product-old', 'product-older'] as $from) {
            self::assertStringContainsString('url=product-ct-scan.html', (string) file_get_contents(self::$out . "/th/{$from}.html"));
        }
        self::assertFileDoesNotExist(self::$out . '/lo/loop-a.html');
        self::assertStringNotContainsString('http-equiv="refresh"', (string) file_get_contents(self::$out . '/lo/about.html'));
    }

    public function testSitemapListsPagesButNot404(): void
    {
        $xml = (string) file_get_contents(self::$out . '/sitemap.xml');
        self::assertSame(67 * 4, substr_count($xml, '<url>'));
        self::assertStringContainsString('<loc>https://new.example.com/zh/product-ct-scan.html</loc>', $xml);
        self::assertStringNotContainsString('404.html', $xml);
        self::assertStringNotContainsString('product-old.html', $xml);
        self::assertStringContainsString('<xhtml:link rel="alternate" hreflang="x-default" href="https://new.example.com/en/product-ct-scan.html"/>', $xml);
    }

    public function testCanonicalAndHreflangWithFullUrls(): void
    {
        $page = (string) file_get_contents(self::$out . '/th/about.html');
        self::assertStringContainsString('<link rel="canonical" href="https://new.example.com/th/about.html">', $page);
        self::assertStringContainsString('<link rel="alternate" hreflang="zh-Hans" href="https://new.example.com/zh/about.html">', $page);
        self::assertStringContainsString('<link rel="alternate" hreflang="x-default" href="https://new.example.com/en/about.html">', $page);
        self::assertStringNotContainsString('rel="canonical"', (string) file_get_contents(self::$out . '/lo/404.html'));
    }

    /** @return list<array> the JSON-LD objects of a built page */
    private static function jsonLd(string $file): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#', (string) file_get_contents(self::$out . '/' . $file), $m);
        return array_map(static fn (string $json): array => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    public function testStructuredData(): void
    {
        [$org] = self::jsonLd('en/index.html');
        self::assertSame(['https://schema.org', 'Organization', 'https://new.example.com/en/index.html', '+85621563121'],
            [$org['@context'], $org['@type'], $org['url'], $org['telephone']]);

        [$product] = self::jsonLd('lo/product-ct-scan.html');
        self::assertSame('Product', $product['@type']);
        self::assertSame('CT Scan', $product['name']);
        self::assertStringStartsWith('https://new.example.com/assets/img/', $product['image']);
        self::assertSame([], self::jsonLd('lo/products.html'));

        $news = array_values(array_filter(self::$builder->slugs(), static fn ($s) => str_starts_with($s, 'news-')));
        [$article] = self::jsonLd('zh/' . $news[0] . '.html');
        self::assertSame('NewsArticle', $article['@type']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $article['datePublished']);
        self::assertSame('Organization', $article['publisher']['@type']);
    }

    public function testSupplierOrganizationAndProductBrand(): void
    {
        $seed = json_decode((string) file_get_contents(App::path('database/seed/seed.json')), true);
        $ct = array_values(array_filter($seed['products'], static fn ($p) => $p['slug'] === 'ct-scan'))[0];
        $sup = static fn (int $id, string $slug): array => ['id' => $id, 'slug' => $slug, 'name' => ucfirst($slug), 'country_code' => 'CN',
            'website_url' => 'https://' . $slug . '.example', 'logo_id' => null, 'logo_on_dark' => 0, 'partnership' => null, 'since_year' => null,
            'certificate_id' => null, 'summary_lo' => 'ຜູ້ຜະລິດ', 'summary_en' => 'Maker', 'summary_th' => null, 'summary_zh' => null,
            'about_lo' => null, 'about_en' => null, 'about_th' => null, 'about_zh' => null, 'is_featured' => 0, 'featured_sort' => 0, 'sort' => $id, 'is_visible' => 1];
        $seed['suppliers'] = [$sup(1, 'acme'), $sup(2, 'other')];
        $seed['product_suppliers'] = [['product_id' => $ct['id'], 'supplier_id' => 1, 'sort' => 0]];
        $dir = self::$out . '-sup';
        file_put_contents($dir . '.json', json_encode($seed));
        (new SiteBuilder(SiteData::fromSeedFile($dir . '.json'), self::$public, '../assets', false, 'https://new.example.com'))->build($dir);
        $ld = static function (string $file): array {
            preg_match('#<script type="application/ld\+json">(.*?)</script>#', (string) file_get_contents($file), $m);
            return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
        };
        $org = $ld($dir . '/en/supplier-acme.html');
        $product = $ld($dir . '/en/product-ct-scan.html');
        exec('rm -rf ' . escapeshellarg($dir) . ' ' . escapeshellarg($dir . '.json'));

        self::assertSame(['@type' => 'Organization', 'name' => 'Acme', 'url' => 'https://acme.example', 'description' => 'Maker'],
            array_intersect_key($org, array_flip(['@type', 'name', 'url', 'description'])));
        self::assertArrayNotHasKey('logo', $org);   // no logo yet: left out, not empty
        self::assertSame(['@type' => 'Brand', 'name' => 'Acme'], $product['brand']);
    }

    public function testSeoPagesOverrideTitleAndDescription(): void
    {
        $seed = json_decode((string) file_get_contents(App::path('database/seed/seed.json')), true);
        $seed['seo_pages'] = [
            ['page' => 'about', 'title_lo' => 'ກ່ຽວກັບ XTK', 'title_en' => 'About XTK </script>', 'title_th' => null, 'title_zh' => null,
                'desc_lo' => null, 'desc_en' => null, 'desc_th' => null, 'desc_zh' => null],
            ['page' => 'index', 'title_lo' => 'XTK ອຸປະກອນການແພດ', 'title_en' => 'XTK medical equipment Laos', 'title_th' => null, 'title_zh' => null,
                'desc_lo' => 'ລາວ', 'desc_en' => 'Medical equipment in Laos', 'desc_th' => null, 'desc_zh' => null],
        ];
        $dir = self::$out . '-seo';
        file_put_contents($dir . '.json', json_encode($seed));
        (new SiteBuilder(SiteData::fromSeedFile($dir . '.json'), self::$public, '../assets', false, null))->build($dir);
        $about = (string) file_get_contents($dir . '/th/about.html');
        $home = (string) file_get_contents($dir . '/en/index.html');
        exec('rm -rf ' . escapeshellarg($dir) . ' ' . escapeshellarg($dir . '.json'));

        self::assertStringContainsString('<title>About XTK &lt;/script&gt; | XTK</title>', $about);   // th empty: English
        self::assertStringContainsString('<title>XTK medical equipment Laos</title>', $home);        // home: no " | XTK"
        self::assertStringContainsString('<meta name="description" content="Medical equipment in Laos">', $home);
        // no base URL: relative alternates, no canonical
        self::assertStringContainsString('<link rel="alternate" hreflang="x-default" href="../en/index.html">', $home);
        self::assertStringNotContainsString('rel="canonical"', $home);
    }

    public function testPageNamesMatchTheBuiltPages(): void
    {
        $seed = App::path('database/seed/seed.json');
        self::assertSame(self::$builder->slugs(), PageList::names(SiteData::fromSeedFile($seed)));
    }

    public function testHeroSlidesFromTheTable(): void
    {
        $home = (string) file_get_contents(self::$out . '/en/index.html');
        self::assertSame(3, substr_count($home, '<div class="hero__slide'));
        self::assertSame(1, substr_count($home, '<h1 class="hero__title">'));
        self::assertStringContainsString('01 / 03', $home);
        // the old site's video: WebM first, then MP4, with the poster's WebP
        self::assertMatchesRegularExpression('#<video class="hero__media" poster="\.\./assets/img/photo/4773\.webp[^"]*"[^>]*><source src="\.\./assets/video/hero-lab\.webm[^"]*" type="video/webm"><source src="\.\./assets/video/hero-lab\.mp4#', $home);
        self::assertStringContainsString('href="healthcare.html">Medical equipment</a>', $home);
    }

    public function testFaqAnswersGetTheContactDetails(): void
    {
        $faq = (string) file_get_contents(self::$out . '/en/faq.html');
        $list = substr($faq, (int) strpos($faq, '<div class="faq rv">'));
        self::assertSame(5, substr_count(strstr($list, '</div>', true), '<details'));
        self::assertStringContainsString('Call the hotline on 020 5589 2929 or', $faq);
        self::assertStringContainsString('<p>Ban Dondeng, House No. 470', $faq);
        self::assertStringNotContainsString('{', strip_tags($faq));
    }

    public function testSettingsReachThePages(): void
    {
        $seed = json_decode((string) file_get_contents(App::path('database/seed/seed.json')), true);
        $seed['settings']['contact'] = ['tel' => '(856-21) 777888', 'fax' => ''] + $seed['settings']['contact'];
        $seed['settings']['analytics_id'] = 'G-TEST1234';
        $dir = self::$out . '-settings';
        file_put_contents($dir . '.json', json_encode($seed));
        (new SiteBuilder(SiteData::fromSeedFile($dir . '.json'), self::$public, '../assets', false, 'https://new.example.com'))->build($dir);
        $contact = (string) file_get_contents($dir . '/en/contact.html');
        $about = (string) file_get_contents($dir . '/en/about.html');
        exec('rm -rf ' . escapeshellarg($dir) . ' ' . escapeshellarg($dir . '.json'));

        self::assertStringContainsString('<a href="tel:+85621777888">(856-21) 777888</a>', $contact);
        self::assertStringNotContainsString('Fax', $contact . $about);   // an empty fax is left out
        self::assertStringContainsString("gtag('config','G-TEST1234')", $contact);
        self::assertStringNotContainsString('gtag', (string) file_get_contents(self::$out . '/en/contact.html'));
    }
}
