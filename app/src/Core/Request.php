<?php
declare(strict_types=1);

namespace App\Core;

/** Read-only helpers over the PHP superglobals. */
final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /** Path without query string, e.g. "/admin/messages/12". */
    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return is_string($path) ? rawurldecode($path) : '/';
    }

    /** Client IP. The server is not behind a proxy, so REMOTE_ADDR is the real address. */
    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** IP packed for VARBINARY(16) columns (same as MySQL INET6_ATON). */
    public static function ipBinary(?string $ip = null): string
    {
        $packed = @inet_pton($ip ?? self::ip());
        return $packed === false ? inet_pton('0.0.0.0') : $packed;
    }

    public static function post(string $key, string $default = ''): string
    {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? $v : $default;
    }

    /** name[]=1&name[]=2 -> [1, 2]: positive integers only */
    public static function postIds(string $key): array
    {
        $v = $_POST[$key] ?? [];
        return is_array($v) ? array_values(array_unique(array_filter(array_map('intval', $v), static fn ($i) => $i > 0))) : [];
    }

    /** One uploaded file, or null when none was chosen. */
    public static function file(string $key): ?array
    {
        $f = $_FILES[$key] ?? null;
        return is_array($f) && is_string($f['name'] ?? null) && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ? $f : null;
    }

    /** files[]: the chosen files as separate $_FILES-style entries. */
    public static function files(string $key): array
    {
        $f = $_FILES[$key] ?? null;
        if (!is_array($f) || !is_array($f['name'] ?? null)) {
            return [];
        }
        $out = [];
        foreach (array_keys($f['name']) as $i) {
            if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $out[] = ['name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
            }
        }
        return $out;
    }

    /** True when the body was bigger than post_max_size: PHP then drops all of $_POST and $_FILES. */
    public static function tooLarge(): bool
    {
        $max = ini_parse_quantity((string) ini_get('post_max_size'));
        return self::isPost() && $max > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $max;
    }

    public static function query(string $key, string $default = ''): string
    {
        $v = $_GET[$key] ?? $default;
        return is_string($v) ? $v : $default;
    }

    public static function header(string $name): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return (string) ($_SERVER[$key] ?? '');
    }

    /** True when the browser sent the form with fetch() and expects JSON back. */
    public static function wantsJson(): bool
    {
        return str_contains(self::header('Accept'), 'application/json');
    }
}
