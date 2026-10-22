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
