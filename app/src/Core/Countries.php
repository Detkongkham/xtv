<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Country names in the 4 site languages by ISO 3166-1 alpha-2 code. app/lang/countries.php wins (its names are
 * the ones already on the site); every other country comes from ICU through ext-intl.
 */
final class Countries
{
    /** ICU region codes that are not countries */
    private const NOT_COUNTRIES = ['EU', 'EZ', 'UN', 'QO', 'XA', 'XB', 'ZZ'];

    private static ?array $codes = null;

    public static function name(string $code, string $lang): string
    {
        $own = Lang::file('countries')[$code][$lang] ?? null;
        if ($own !== null) {
            return $own;
        }
        $name = \Locale::getDisplayRegion('-' . $code, $lang === 'zh' ? 'zh_Hans' : $lang);
        return $name !== '' && $name !== $code ? $name : $code;
    }

    public static function exists(string $code): bool
    {
        return in_array($code, self::codes(), true);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        if (self::$codes === null) {
            $codes = [];
            foreach (\ResourceBundle::create('en', 'ICUDATA-region')['Countries'] ?? [] as $code => $_) {
                if (preg_match('/^[A-Z]{2}$/', (string) $code) && !in_array($code, self::NOT_COUNTRIES, true)) {
                    $codes[] = (string) $code;
                }
            }
            self::$codes = array_values(array_unique(array_merge($codes, array_keys(Lang::file('countries')))));
        }
        return self::$codes;
    }

    /** @return array<string, string> code => name, sorted by name in $lang */
    public static function options(string $lang): array
    {
        $out = [];
        foreach (self::codes() as $code) {
            $out[$code] = self::name($code, $lang);
        }
        $collator = new \Collator($lang);
        uasort($out, static fn ($a, $b) => $collator->compare($a, $b));
        return $out;
    }
}
