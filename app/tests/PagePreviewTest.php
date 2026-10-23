<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Publish\PagePreview;
use App\Publish\SiteData;
use PHPUnit\Framework\TestCase;

/** /admin/preview pages, rendered from database/seed/seed.json (no database). */
final class PagePreviewTest extends TestCase
{
    private static PagePreview $preview;

    public static function setUpBeforeClass(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        $seed = json_decode((string) file_get_contents(App::path('database/seed/seed.json')), true);
        $draft = $seed['news'][0];
        $seed['news'][] = ['id' => 999, 'slug' => 'draft-one', 'status' => 'draft', 'title_en' => 'Draft headline', 'title_lo' => 'ຮ່າງ'] + $draft;
        $file = sys_get_temp_dir() . '/xtk-preview-' . bin2hex(random_bytes(4)) . '.json';
        file_put_contents($file, json_encode($seed));
        self::$preview = new PagePreview(SiteData::fromSeedFile($file), dirname(__DIR__, 2) . '/public');
        unlink($file);
    }

    public function testPageWithPreviewLinksAndBar(): void
    {
        $html = (string) self::$preview->render('product-ct-scan', 'th', '<p class="preview-bar">BAR</p>');
        self::assertStringContainsString('<html lang="th">', $html);
        self::assertStringContainsString('<p class="preview-bar">BAR</p>', $html);
        self::assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        self::assertStringContainsString('href="/admin/preview?page=contact&amp;lang=th"', $html);
        self::assertStringContainsString('hreflang="zh-Hans" href="/admin/preview?page=product-ct-scan&amp;lang=zh"', $html);
        self::assertDoesNotMatchRegularExpression('/href="[^"]*\.html/', $html);
        self::assertStringContainsString('href="../assets/css/style.css?v=', $html);   // assets: ../ from /admin/
        self::assertStringNotContainsString('gtag', $html);
    }

    public function testDraftNewsHasAPreviewButNoPublishedPage(): void
    {
        self::assertStringContainsString('Draft headline', (string) self::$preview->render('news-draft-one', 'en', ''));
    }

    public function testUnknownPage(): void
    {
        self::assertNull(self::$preview->render('product-nope', 'lo', ''));
        self::assertNull(self::$preview->render('news-nope', 'lo', ''));
    }

    public function testLinksWithAnchorAndOtherLanguage(): void
    {
        self::assertSame('<a href="/admin/preview?page=faq&amp;lang=lo#q2">', PagePreview::previewLinks('<a href="faq.html#q2">', 'lo'));
        self::assertSame('<a href="/admin/preview?page=index&amp;lang=en">', PagePreview::previewLinks('<a href="../en/index.html">', 'lo'));
        self::assertSame('<a href="../uploads/a.pdf">', PagePreview::previewLinks('<a href="../uploads/a.pdf">', 'lo'));
    }
}
