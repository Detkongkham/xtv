<?php
declare(strict_types=1);

namespace App\Core;

/** Admin session. Files live in storage/sessions; the cookie is HttpOnly + SameSite=Lax (+ Secure on HTTPS). */
final class Session
{
    public const IDLE_SECONDS = 7200; // log out after 2 hours without activity

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_save_path(App::path('storage/sessions'));
        session_name('xtk_admin');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) self::IDLE_SECONDS);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/admin',
            'secure' => (bool) App::config('cookie_secure', false),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        $now = time();
        $last = $_SESSION['_last'] ?? $now;
        if ($now - $last > self::IDLE_SECONDS) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['_expired'] = true;
        }
        $_SESSION['_last'] = $now;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Take a value once (used for the "session expired" notice). */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $v = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $v;
    }

    /** New session id after login, keeping the data. */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 3600] + array_intersect_key($p, array_flip(['path', 'domain', 'secure', 'httponly', 'samesite'])));
        }
        session_destroy();
    }

    /** Flash message shown on the next page: type is 'ok' or 'error'. */
    public static function flash(string $type, string $text): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'text' => $text];
    }

    public static function takeFlash(): array
    {
        $f = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $f;
    }
}
