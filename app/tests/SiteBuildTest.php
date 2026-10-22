<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Publish\FontCheck;
use App\Publish\LinkChecker;
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
        self::assertSame(276, self::$pages);
        self::assertCount(69, self::$builder->slugs());
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
        self::assertSame(68 * 4, substr_count($xml, '<url>'));
        self::assertStringContainsString('<loc>https://new.example.com/zh/product-ct-scan.html</loc>', $xml);
        self::assertStringNotContainsString('404.html', $xml);
        self::assertStringNotContainsString('product-old.html', $xml);
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
