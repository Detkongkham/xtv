<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Theme colors (/admin/theme). Every color is a CSS custom property declared in public/assets/css/style.css (SITE) or
 * public/admin/assets/admin.css (ADMIN) with the default below. A theme stores only the colors that were changed, as
 * #rrggbb or #rrggbbaa; the rest keep following their base color. css() turns a theme into one :root rule that is
 * loaded after the stylesheet.
 *
 * Default: '#hex', 'token' (follows that color) or ['token', percent] (that color, partly transparent).
 */
final class ThemeService
{
    private const BASE = [
        'ສີແບຣນ' => [
            'brand-900' => ['ແບຣນ ເຂັ້ມສຸດ (footer, hero)', '#1b1f55'],
            'brand-600' => ['ແບຣນ ຫຼັກ (ຫົວຂໍ້, ລິ້ງ, ປຸ່ມ)', '#2f368e'],
            'brand-500' => ['ແບຣນ ຕອນ hover', '#4a52a8'],
            'brand-100' => ['ແບຣນ ອ່ອນ', '#e6e8f4'],
            'brand-50' => ['ແບຣນ ອ່ອນສຸດ (ພື້ນ)', '#f3f4fa'],
        ],
        'ສີເນັ້ນ' => [
            'accent-red' => ['ແດງ (ເສັ້ນເນັ້ນ)', '#e82227'],
            'accent-red-text' => ['ແດງ ຕົວໜັງສື', '#c8161d'],
            'accent-red-50' => ['ແດງ ພື້ນອ່ອນ', '#fdeced'],
            'accent-green' => ['ຂຽວ', '#019147'],
            'accent-green-text' => ['ຂຽວ ຕົວໜັງສື', '#026838'],
            'accent-green-50' => ['ຂຽວ ພື້ນອ່ອນ', '#e3f3ea'],
        ],
        'ຕົວໜັງສື ແລະ ພື້ນ' => [
            'ink' => ['ຫົວຂໍ້', '#1a1a1a'],
            'body' => ['ເນື້ອຫາ', '#4c5768'],
            'muted' => ['ຕົວໜັງສືຈາງ', '#6a6d7c'],
            'line' => ['ເສັ້ນຂອບ', '#dadce0'],
            'surface' => ['ພື້ນສຳຮອງ (ເທົາອ່ອນ)', '#f5f6f8'],
            'white' => ['ຂາວ (ພື້ນ ແລະ ຕົວໜັງສືເທິງພື້ນເຂັ້ມ)', '#ffffff'],
        ],
    ];

    public const SITE_ONLY = [
        'ໜ້າເວັບທົ່ວໄປ' => [
            'page-bg' => ['ພື້ນໜ້າເວັບ', 'white'],
            'link' => ['ລິ້ງ', 'brand-600'],
            'heading-sec' => ['ຫົວຂໍ້ section', 'brand-600'],
            'sec-rule' => ['ເສັ້ນໃຕ້ຫົວຂໍ້ section', 'accent-red'],
            'sec-surface' => ['ພື້ນ section ສີເທົາ', 'surface'],
            'sec-tint' => ['ພື້ນ section ສີແບຣນອ່ອນ', 'brand-50'],
        ],
        'Header ແລະ ເມນູ' => [
            'hdr-bg' => ['ພື້ນ header', 'white'],
            'hdr-text' => ['ຕົວໜັງສືເມນູ', 'ink'],
            'nav-line' => ['ເສັ້ນໃຕ້ເມນູ ແລະ ແທັບ', 'accent-red'],
            'mega-bg' => ['ພື້ນ mega-menu', 'white'],
            'drawer-bg' => ['ພື້ນເມນູມືຖື', 'white'],
        ],
        'Hero ແລະ ຫົວໜ້າຍ່ອຍ' => [
            'hero-bg' => ['ພື້ນ hero', 'brand-900'],
            'hero-overlay' => ['ຊັ້ນສີທັບຮູບ hero', ['brand-900', 55]],
            'hero-text' => ['ຕົວໜັງສື hero', 'white'],
            'hero-line' => ['ເສັ້ນນຳໜ້າ eyebrow', 'accent-red'],
            'phead-bg' => ['ພື້ນຫົວໜ້າຍ່ອຍ', 'surface'],
            'phead-title' => ['ຫົວຂໍ້ໜ້າຍ່ອຍ', 'brand-600'],
        ],
        'ປຸ່ມ' => [
            'btn-bg' => ['ປຸ່ມຫຼັກ ພື້ນ', 'brand-600'],
            'btn-text' => ['ປຸ່ມຫຼັກ ຕົວໜັງສື', 'white'],
            'btn-hover' => ['ປຸ່ມຫຼັກ ຕອນ hover', 'brand-500'],
            'btn-ghost' => ['ປຸ່ມຂອບ (ghost)', 'brand-600'],
            'float-bg' => ['ປຸ່ມລອຍ', 'brand-600'],
            'float-wa' => ['ປຸ່ມລອຍ WhatsApp', 'accent-green'],
        ],
        'ກາດ ແລະ ສະຖິຕິ' => [
            'card-bg' => ['ພື້ນກາດ', 'white'],
            'card-line' => ['ຂອບກາດ', 'line'],
            'card-shadow' => ['ເງົາກາດຕອນ hover', ['brand-600', 10]],
            'icon-bg' => ['ພື້ນໄອຄອນ', 'brand-50'],
            'icon-color' => ['ໄອຄອນ', 'brand-600'],
            'stat-num' => ['ຕົວເລກສະຖິຕິ', 'brand-600'],
            'stat-sup' => ['ເຄື່ອງໝາຍ + ຂອງສະຖິຕິ', 'accent-green'],
        ],
        'ແຖບ CTA ແລະ ສາຂາ' => [
            'cta-bg' => ['ພື້ນແຖບ CTA', 'brand-600'],
            'cta-text' => ['ຕົວໜັງສືແຖບ CTA', 'white'],
            'loc-bg' => ['ພື້ນລາຍການສາຂາ', 'brand-900'],
        ],
        'ຟອມຕິດຕໍ່' => [
            'field-line' => ['ຂອບຊ່ອງພິມ', 'line'],
            'field-focus' => ['ຂອບຊ່ອງພິມຕອນເລືອກ', 'brand-600'],
            'ok-bg' => ['ສົ່ງສຳເລັດ ພື້ນ', 'accent-green-50'],
            'ok-text' => ['ສົ່ງສຳເລັດ ຕົວໜັງສື', 'accent-green-text'],
            'err-bg' => ['ຜິດພາດ ພື້ນ', 'accent-red-50'],
            'err-text' => ['ຜິດພາດ ຕົວໜັງສື', 'accent-red-text'],
        ],
        'Footer' => [
            'ftr-bg' => ['ພື້ນ footer', 'brand-900'],
            'ftr-text' => ['ຕົວໜັງສື footer', ['white', 65]],
            'ftr-heading' => ['ຫົວຂໍ້ ແລະ hover ໃນ footer', 'white'],
            'ftr-line' => ['ເສັ້ນແບ່ງ footer', ['white', 15]],
        ],
    ];

    public const ADMIN_ONLY = [
        'ໂຄງໜ້າ admin' => [
            'page-bg' => ['ພື້ນໜ້າ', 'surface'],
            'side-bg' => ['ພື້ນເມນູຂ້າງ', 'white'],
            'side-text' => ['ຕົວໜັງສືເມນູຂ້າງ', 'body'],
            'side-active-bg' => ['ເມນູທີ່ເລືອກ ພື້ນ', 'brand-50'],
            'side-active-text' => ['ເມນູທີ່ເລືອກ ຕົວໜັງສື', 'brand-600'],
            'top-bg' => ['ພື້ນແຖບເທິງ', 'white'],
            'btn-bg' => ['ປຸ່ມຫຼັກ ພື້ນ', 'brand-600'],
            'btn-text' => ['ປຸ່ມຫຼັກ ຕົວໜັງສື', 'white'],
            'btn-hover' => ['ປຸ່ມຫຼັກ ຕອນ hover', 'brand-500'],
        ],
        'ສີສະຖານະ' => [
            'info' => ['ຂໍ້ມູນ (info)', '#0369a1'],
            'success' => ['ສຳເລັດ (success)', '#017a3c'],
            'warning' => ['ເຕືອນ (warning)', '#b45309'],
            'warning-50' => ['ເຕືອນ ພື້ນອ່ອນ', '#fef6e7'],
            'warning-line' => ['ເຕືອນ ຂອບ', '#f0c96a'],
            'warning-dot' => ['ເຕືອນ ຈຸດ', '#d97706'],
            'danger' => ['ອັນຕະລາຍ (danger)', 'accent-red-text'],
            'danger-line' => ['ອັນຕະລາຍ ຂອບ', '#f3c4c6'],
            'danger-hover' => ['ປຸ່ມລຶບ ຕອນ hover', '#a51117'],
        ],
        'ກຣາຟ' => [
            'chart-1' => ['ກຣາຟ ສີ 1', 'brand-600'],
            'chart-2' => ['ກຣາຟ ສີ 2', '#0891b2'],
            'chart-3' => ['ກຣາຟ ສີ 3', '#d97706'],
            'chart-4' => ['ກຣາຟ ສີ 4', 'accent-green'],
            'chart-5' => ['ກຣາຟ ສີ 5', 'accent-red'],
        ],
    ];

    /** Text on background pairs checked for readability: [text, background, what]. */
    private const PAIRS = [
        'site' => [
            ['body', 'page-bg', 'ເນື້ອຫາ ເທິງພື້ນໜ້າເວັບ'],
            ['ink', 'page-bg', 'ຫົວຂໍ້ ເທິງພື້ນໜ້າເວັບ'],
            ['heading-sec', 'page-bg', 'ຫົວຂໍ້ section ເທິງພື້ນໜ້າເວັບ'],
            ['link', 'page-bg', 'ລິ້ງ ເທິງພື້ນໜ້າເວັບ'],
            ['muted', 'page-bg', 'ຕົວໜັງສືຈາງ ເທິງພື້ນໜ້າເວັບ'],
            ['body', 'sec-surface', 'ເນື້ອຫາ ເທິງ section ສີເທົາ'],
            ['hdr-text', 'hdr-bg', 'ເມນູ ເທິງ header'],
            ['phead-title', 'phead-bg', 'ຫົວຂໍ້ໜ້າຍ່ອຍ'],
            ['hero-text', 'hero-bg', 'ຕົວໜັງສື hero'],
            ['btn-text', 'btn-bg', 'ປຸ່ມຫຼັກ'],
            ['btn-text', 'btn-hover', 'ປຸ່ມຫຼັກ ຕອນ hover'],
            ['btn-ghost', 'page-bg', 'ປຸ່ມຂອບ'],
            ['body', 'card-bg', 'ເນື້ອຫາໃນກາດ'],
            ['cta-text', 'cta-bg', 'ແຖບ CTA'],
            ['ok-text', 'ok-bg', 'ຂໍ້ຄວາມສົ່ງສຳເລັດ'],
            ['err-text', 'err-bg', 'ຂໍ້ຄວາມຜິດພາດ'],
            ['ftr-text', 'ftr-bg', 'ຕົວໜັງສື footer'],
            ['ftr-heading', 'ftr-bg', 'ຫົວຂໍ້ footer'],
        ],
        'admin' => [
            ['body', 'page-bg', 'ເນື້ອຫາ ເທິງພື້ນໜ້າ'],
            ['ink', 'white', 'ຫົວຂໍ້ ເທິງກາດ'],
            ['side-text', 'side-bg', 'ເມນູຂ້າງ'],
            ['side-active-text', 'side-active-bg', 'ເມນູທີ່ເລືອກ'],
            ['btn-text', 'btn-bg', 'ປຸ່ມຫຼັກ'],
            ['muted', 'white', 'ຕົວໜັງສືຈາງ ເທິງກາດ'],
            ['accent-green-text', 'accent-green-50', 'ແຈ້ງເຕືອນສຳເລັດ'],
            ['accent-red-text', 'accent-red-50', 'ແຈ້ງເຕືອນຜິດພາດ'],
        ],
    ];

    /** Ready-made brand colors (both site and admin); the rest of the colors follow them. */
    public const PRESETS = [
        'XTK ເລີ່ມຕົ້ນ (ນ້ຳເງິນໂລໂກ້)' => '#2f368e',
        'ຂຽວການແພດ' => '#00796b',
        'ນ້ຳເງິນທະເລ' => '#0057a8',
        'ມ່ວງ' => '#5b3f9e',
        'ເທົາເຂັ້ມ' => '#37474f',
    ];

    public const SCOPES = ['site' => 'ໜ້າເວັບ', 'admin' => 'ລະບົບ Admin'];

    private const HEX = '/^#[0-9a-f]{6}([0-9a-f]{2})?$/';

    /** @return array<string, array<string, array{0: string, 1: string|array}>> group => token => [label, default] */
    public static function groups(string $scope): array
    {
        return self::BASE + ($scope === 'admin' ? self::ADMIN_ONLY : self::SITE_ONLY);
    }

    /** @return array<string, array{0: string, 1: string|array}> token => [label, default] */
    public static function tokens(string $scope): array
    {
        return array_merge(...array_values(self::groups($scope)));
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    public static function pairs(string $scope): array
    {
        return self::PAIRS[$scope] ?? [];
    }

    /**
     * Keep known colors with a valid value, lower case. Empty = follows its default.
     * @param array<string, mixed> $input token => '#rrggbb' | '#rrggbbaa' | ''
     * @return array<string, string>
     */
    public static function clean(string $scope, array $input): array
    {
        $tokens = self::tokens($scope);
        $out = [];
        foreach ($input as $token => $value) {
            if (!is_string($value)) {
                continue;
            }
            $value = strtolower(trim($value));
            if (isset($tokens[$token]) && preg_match(self::HEX, $value)) {
                $out[(string) $token] = $value;
            }
        }
        return $out;
    }

    /** @return array{site: array<string, string>, admin: array<string, string>} */
    public static function cleanTheme(mixed $theme): array
    {
        $theme = is_array($theme) ? $theme : [];
        return [
            'site' => self::clean('site', is_array($theme['site'] ?? null) ? $theme['site'] : []),
            'admin' => self::clean('admin', is_array($theme['admin'] ?? null) ? $theme['admin'] : []),
        ];
    }

    /** One :root rule with the changed colors, '' when nothing was changed. Values are checked again here. */
    public static function css(string $scope, array $colors): string
    {
        $rules = '';
        foreach (self::clean($scope, $colors) as $token => $value) {
            $rules .= '--' . $token . ':' . $value . ';';
        }
        return $rules === '' ? '' : ':root{' . $rules . '}';
    }

    /** The default as it is written in the stylesheet. */
    public static function defaultCss(string|array $default): string
    {
        if (is_array($default)) {
            return 'color-mix(in srgb, var(--' . $default[0] . ') ' . $default[1] . '%, transparent)';
        }
        return $default[0] === '#' ? $default : 'var(--' . $default . ')';
    }

    /**
     * Every color as [r, g, b, a] (a 0..1) after the theme is applied.
     * @return array<string, array{0: int, 1: int, 2: int, 3: float}>
     */
    public static function resolve(string $scope, array $colors): array
    {
        $tokens = self::tokens($scope);
        $colors = self::clean($scope, $colors);
        $out = [];
        $get = static function (string $t) use (&$get, &$out, $tokens, $colors): array {
            if (isset($out[$t])) {
                return $out[$t];
            }
            $d = $colors[$t] ?? $tokens[$t][1];
            if (is_array($d)) {
                $c = $get($d[0]);
                $c[3] = round($c[3] * $d[1] / 100, 3);
            } else {
                $c = $d[0] === '#' ? self::rgba($d) : $get($d);
            }
            return $out[$t] = $c;
        };
        foreach (array_keys($tokens) as $t) {
            $get($t);
        }
        return $out;
    }

    /** @return array{0: int, 1: int, 2: int, 3: float} */
    public static function rgba(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $a = strlen($hex) === 8 ? round(hexdec(substr($hex, 6, 2)) / 255, 3) : 1.0;
        return [(int) hexdec(substr($hex, 0, 2)), (int) hexdec(substr($hex, 2, 2)), (int) hexdec(substr($hex, 4, 2)), $a];
    }

    public static function hex(array $rgb): string
    {
        return sprintf('#%02x%02x%02x', ...array_map(static fn ($v) => max(0, min(255, (int) round($v))), array_slice($rgb, 0, 3)));
    }

    /** WCAG contrast ratio; a see-through text color is first laid over its background, the background over white. */
    public static function contrast(array $fg, array $bg): float
    {
        $bg = self::over($bg, [255, 255, 255]);
        $fg = self::over($fg, $bg);
        $l = static function (array $c): float {
            $ch = static function (int|float $v): float {
                $v /= 255;
                return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
            };
            return 0.2126 * $ch($c[0]) + 0.7152 * $ch($c[1]) + 0.0722 * $ch($c[2]);
        };
        [$a, $b] = [$l($fg), $l($bg)];
        return round((max($a, $b) + 0.05) / (min($a, $b) + 0.05), 2);
    }

    /**
     * Pairs below 4.5:1 (WCAG AA for normal text).
     * @return list<array{what: string, ratio: float}>
     */
    public static function warnings(string $scope, array $colors): array
    {
        $c = self::resolve($scope, $colors);
        $out = [];
        foreach (self::pairs($scope) as [$fg, $bg, $what]) {
            $ratio = self::contrast($c[$fg], $c[$bg]);
            if ($ratio < 4.5) {
                $out[] = ['what' => $what, 'ratio' => $ratio];
            }
        }
        return $out;
    }

    /**
     * The brand scale from one main color, in the same steps as the logo blue: 900 darker, 500 lighter, 100 and 50 tints.
     * @return array<string, string>
     */
    public static function brandScale(string $hex): array
    {
        $c = self::rgba($hex);
        $mix = static fn (array $with, float $t): string => self::hex([
            $c[0] + ($with[0] - $c[0]) * $t, $c[1] + ($with[1] - $c[1]) * $t, $c[2] + ($with[2] - $c[2]) * $t,
        ]);
        return [
            'brand-900' => $mix([0, 0, 0], 0.42),
            'brand-600' => self::hex($c),
            'brand-500' => $mix([255, 255, 255], 0.15),
            'brand-100' => $mix([255, 255, 255], 0.88),
            'brand-50' => $mix([255, 255, 255], 0.94),
        ];
    }

    /** Everything the theme page script needs to resolve, preview and check colors in the browser. */
    public static function clientData(): array
    {
        $scopes = [];
        foreach (array_keys(self::SCOPES) as $scope) {
            $tokens = [];
            foreach (self::tokens($scope) as $t => [$label, $default]) {
                $tokens[$t] = ['label' => $label, 'default' => $default, 'css' => self::defaultCss($default)];
            }
            $scopes[$scope] = ['tokens' => $tokens, 'pairs' => self::pairs($scope)];
        }
        $presets = [];
        foreach (self::PRESETS as $name => $hex) {
            $presets[] = ['name' => $name, 'brand' => $hex === '#2f368e' ? [] : self::brandScale($hex)];
        }
        return ['scopes' => $scopes, 'presets' => $presets];
    }

    private static function over(array $c, array $under): array
    {
        $a = $c[3] ?? 1.0;
        return [$c[0] * $a + $under[0] * (1 - $a), $c[1] * $a + $under[1] * (1 - $a), $c[2] * $a + $under[2] * (1 - $a), 1.0];
    }
}
