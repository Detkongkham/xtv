<?php
declare(strict_types=1);

namespace App\Core;

/** UI text of the public site (app/lang/<code>.php) and the language fallback for database content. */
final class Lang
{
    public const CODES = ['lo', 'en', 'th', 'zh'];
    public const NAMES = ['lo' => 'ລາວ', 'en' => 'English', 'th' => 'ไทย', 'zh' => '中文'];
    public const SHORT = ['lo' => 'LA', 'en' => 'EN', 'th' => 'TH', 'zh' => '中文'];
    public const HTML = ['lo' => 'lo', 'en' => 'en', 'th' => 'th', 'zh' => 'zh-Hans'];

    private static array $files = [];

    private array $strings;

    public function __construct(public readonly string $code)
    {
        if (!in_array($code, self::CODES, true)) {
            throw new \InvalidArgumentException('Unknown language: ' . $code);
        }
        $this->strings = self::file($code);
    }

    public static function file(string $name): array
    {
        return self::$files[$name] ??= require App::path('lang/' . $name . '.php');
    }

    public function t(string $key): string
    {
        return $this->strings[$key] ?? throw new \OutOfBoundsException('Missing text: ' . $key);
    }

    /** t() with "{}" filled in order and "{name}" by key. */
    public function f(string $key, string|int ...$args): string
    {
        $text = $this->t($key);
        $i = 0;
        return (string) preg_replace_callback('/\{(\w*)\}/', static function (array $m) use ($args, &$i): string {
            return (string) ($m[1] === '' ? $args[$i++] : $args[$m[1]]);
        }, $text);
    }

    /** A translated column of a database row: <field>_<code>, or <field>_en when that is empty (_th / _zh are optional). */
    public function tr(array $row, string $field): string
    {
        $value = $row[$field . '_' . $this->code] ?? null;
        if ($value === null || $value === '') {
            $value = $row[$field . '_en'] ?? '';
        }
        return (string) $value;
    }
}
