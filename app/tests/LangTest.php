<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Core\Lang;
use PHPUnit\Framework\TestCase;

final class LangTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
    }

    public function testEveryLanguageHasTheSameKeys(): void
    {
        $lo = array_keys(Lang::file('lo'));
        foreach (['en', 'th', 'zh'] as $code) {
            self::assertSame($lo, array_keys(Lang::file($code)), $code);
        }
        foreach (Lang::CODES as $code) {
            self::assertNotContains('', Lang::file($code), $code);
        }
    }

    public function testFormatFillsPlaceholdersInOrderAndByName(): void
    {
        $en = new Lang('en');
        self::assertSame('3 suppliers · 2 countries', $en->f('sup_meta', 3, 2));
        self::assertSame('XTK distributes 5 products from Acme in the Lao PDR.', $en->f('sup_sells', n: 5, name: 'Acme'));
    }

    public function testEmptyThaiOrChineseFallsBackToEnglish(): void
    {
        $row = ['name_lo' => 'ລາວ', 'name_en' => 'English', 'name_th' => '', 'name_zh' => null];
        self::assertSame('ລາວ', (new Lang('lo'))->tr($row, 'name'));
        self::assertSame('English', (new Lang('th'))->tr($row, 'name'));
        self::assertSame('English', (new Lang('zh'))->tr($row, 'name'));
    }

    public function testMissingKeyFails(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        (new Lang('lo'))->t('no_such_key');
    }
}
