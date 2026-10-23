<?php
declare(strict_types=1);

namespace App\Tests;

use App\Service\ThemeService as T;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ThemeServiceTest extends TestCase
{
    public static function sheets(): array
    {
        $root = dirname(__DIR__, 2) . '/public';
        return ['site' => ['site', $root . '/assets/css/style.css'], 'admin' => ['admin', $root . '/admin/assets/admin.css']];
    }

    /** The list on /admin/theme and the stylesheet must agree, or a color would show the wrong default (or do nothing). */
    #[DataProvider('sheets')]
    public function testEveryColorIsDeclaredWithItsDefault(string $scope, string $file): void
    {
        preg_match_all('/:root\s*\{([^}]*)\}/', (string) file_get_contents($file), $m);
        $declared = [];
        foreach ($m[1] as $block) {
            preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/', $block, $d, PREG_SET_ORDER);
            foreach ($d as [, $name, $value]) {
                $declared[$name] = trim($value);
            }
        }
        foreach (T::tokens($scope) as $token => [, $default]) {
            $this->assertArrayHasKey($token, $declared, "--{$token} missing in {$file}");
            $this->assertSame(T::defaultCss($default), $declared[$token], "--{$token} default");
        }
    }

    public function testCleanKeepsKnownValidColorsOnly(): void
    {
        $this->assertSame(
            ['brand-600' => '#00796b', 'hero-overlay' => '#1b1f558c'],
            T::clean('site', ['brand-600' => ' #00796B ', 'hero-overlay' => '#1b1f558c', 'nope' => '#000000', 'ink' => 'red',
                'body' => '#12345', 'line' => '#000;}body{x', 'muted' => ['#000000'], 'link' => '']),
        );
        $this->assertSame([], T::clean('admin', ['ftr-bg' => '#000000'])); // a site-only color
    }

    public function testCssIsOneRootRuleOrNothing(): void
    {
        $this->assertSame('', T::css('site', []));
        $this->assertSame(':root{--brand-600:#00796b;--btn-text:#ffffffcc;}', T::css('site', ['brand-600' => '#00796B', 'btn-text' => '#ffffffcc', 'x' => '#000000']));
    }

    public function testFollowersTakeTheChangedBaseColor(): void
    {
        $c = T::resolve('site', ['brand-600' => '#00796b']);
        $this->assertSame([0, 121, 107, 1.0], $c['btn-bg']);
        $this->assertSame([0, 121, 107, 1.0], $c['link']);
        $this->assertSame(0.1, $c['card-shadow'][3]);
        $c = T::resolve('site', ['brand-600' => '#00796b', 'btn-bg' => '#ff000080']);
        $this->assertSame([255, 0, 0, 0.502], $c['btn-bg']);
    }

    public function testContrast(): void
    {
        $this->assertSame(21.0, T::contrast([0, 0, 0, 1.0], [255, 255, 255, 1.0]));
        $this->assertSame(1.0, T::contrast([47, 54, 142, 1.0], [47, 54, 142, 1.0]));
        $this->assertGreaterThan(10, T::contrast(T::rgba('#2f368e'), T::rgba('#ffffff')));
    }

    public function testDefaultThemeReadsWell(): void
    {
        $this->assertSame([], T::warnings('site', []));
        $this->assertSame([], T::warnings('admin', []));
    }

    public function testHardToReadPairsAreReported(): void
    {
        $w = T::warnings('site', ['btn-bg' => '#ffff00']);
        $this->assertContains('ປຸ່ມຫຼັກ', array_column($w, 'what'));
    }

    public function testBrandScaleStartsFromTheMainColor(): void
    {
        $s = T::brandScale('#2f368e');
        $this->assertSame('#2f368e', $s['brand-600']);
        $white = T::rgba('#ffffff');
        $steps = array_map(static fn (string $hex): float => T::contrast(T::rgba($hex), $white), $s);
        // darkest to lightest, in the order of the logo blue scale
        $this->assertSame(['brand-900', 'brand-600', 'brand-500', 'brand-100', 'brand-50'], array_keys($s));
        $this->assertTrue($steps['brand-900'] > $steps['brand-600'] && $steps['brand-600'] > $steps['brand-500'] && $steps['brand-500'] > $steps['brand-100'] && $steps['brand-100'] > $steps['brand-50']);
        $this->assertLessThan(1.3, $steps['brand-100']);
    }

    public function testCleanThemeAcceptsAnything(): void
    {
        $this->assertSame(['site' => [], 'admin' => []], T::cleanTheme(null));
        $this->assertSame(['site' => [], 'admin' => ['info' => '#000000']], T::cleanTheme(['site' => 'x', 'admin' => ['info' => '#000000']]));
    }
}
