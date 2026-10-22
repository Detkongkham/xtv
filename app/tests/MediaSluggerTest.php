<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Core\Countries;
use App\Service\MediaService;
use App\Service\Slugger;
use PHPUnit\Framework\TestCase;

final class MediaSluggerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
    }

    public function testSlugs(): void
    {
        self::assertSame('fetal-maternal-monitor', Slugger::slugify('Fetal & Maternal Monitor'));
        self::assertSame('analgesics-antipyretic-and-nsaids', Slugger::slugify('  Analgesics, Antipyretic and NSAIDs '));
        self::assertSame('siemens-healthineers', Slugger::slugify('Siemens Healthineers®'));
        self::assertSame('a', Slugger::slugify('A--'));
        self::assertTrue(Slugger::isValid('ct-scan'));
        self::assertFalse(Slugger::isValid('CT scan'));
        self::assertFalse(Slugger::isValid('-ct'));
        self::assertFalse(Slugger::isValid('ct--scan'));
    }

    public function testFitWidthScalesDownAndKeepsTransparency(): void
    {
        $img = imagecreatetruecolor(600, 200);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $out = MediaService::fitWidth($img, 480);
        self::assertSame([480, 160], [imagesx($out), imagesy($out)]);
        self::assertSame(127, imagecolorsforindex($out, imagecolorat($out, 5, 5))['alpha']);

        $small = MediaService::fitWidth(imagecreatetruecolor(300, 100), 480);
        self::assertSame(300, imagesx($small));
    }

    public function testCountryNames(): void
    {
        self::assertSame('ຈີນ', Countries::name('CN', 'lo'));
        self::assertSame('Vietnam', Countries::name('VN', 'en'));
        self::assertSame('越南', Countries::name('VN', 'zh'));
        self::assertTrue(Countries::exists('LA'));
        self::assertFalse(Countries::exists('ZZ'));
        self::assertGreaterThan(240, count(Countries::codes()));
    }
}
