<?php
declare(strict_types=1);

namespace App\Core;

/** Holds config and the shared Db/Logger for one request or CLI run. */
final class App
{
    private static array $config = [];
    private static string $root = '';
    private static ?Db $db = null;
    private static ?Logger $logger = null;

    public static function boot(array $config, string $root): void
    {
        self::$config = $config;
        self::$root = rtrim($root, '/');
        self::$db = null;
        self::$logger = null;
    }

    /** Dot-notation lookup: App::config('db.host'). */
    public static function config(string $key, mixed $default = null): mixed
    {
        $value = self::$config;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    /** Absolute path inside app/. */
    public static function path(string $relative = ''): string
    {
        return self::$root . ($relative === '' ? '' : '/' . ltrim($relative, '/'));
    }

    public static function isLocal(): bool
    {
        return self::config('env') === 'local';
    }

    public static function db(): Db
    {
        return self::$db ??= Db::connect(self::config('db'));
    }

    public static function logger(): Logger
    {
        return self::$logger ??= new Logger(self::path('storage/logs'));
    }
}
