<?php
declare(strict_types=1);

namespace App\Core;

use App\Repository\LoginAttemptRepository;
use App\Repository\UserRepository;

/** Admin login. 5 failed attempts in 15 minutes (per email or per IP) lock the login for 15 minutes. */
final class Auth
{
    public const MAX_FAILURES = 5;
    public const LOCK_SECONDS = 900;

    private static ?array $user = null;
    private static bool $loaded = false;

    /** @return 'ok'|'locked'|'invalid' */
    public static function attempt(string $email, string $password, string $ipBinary): string
    {
        $db = App::db();
        $attempts = new LoginAttemptRepository($db);
        if ($attempts->recentFailures($email, $ipBinary, self::LOCK_SECONDS) >= self::MAX_FAILURES) {
            return 'locked';
        }

        $users = new UserRepository($db);
        $user = $users->findByEmail($email);
        // verify against a dummy hash when the user is unknown, so timing does not reveal which emails exist
        $hash = $user['password_hash'] ?? '$2y$12$1kslBDqBdT5hxm04CFDC4ukGOthiBHqrYwjZBPbBQsGgKpEhRbO82';
        $ok = password_verify($password, $hash) && $user !== null && (int) $user['is_active'] === 1;

        $attempts->record($email, $ipBinary, $ok);
        if (!$ok) {
            return 'invalid';
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $users->setPasswordHash((int) $user['id'], UserRepository::hash($password));
        }
        $users->touchLogin((int) $user['id']);
        Session::regenerate();
        Session::set('uid', (int) $user['id']);
        self::$user = $user;
        self::$loaded = true;
        return 'ok';
    }

    /** The logged-in, still active user, or null. */
    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $uid = Session::get('uid');
            if (is_int($uid)) {
                $u = (new UserRepository(App::db()))->find($uid);
                self::$user = $u !== null && (int) $u['is_active'] === 1 ? $u : null;
                if (self::$user === null) {
                    Session::forget('uid');
                }
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u === null ? null : (int) $u['id'];
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function logout(): void
    {
        self::$user = null;
        self::$loaded = true;
        Session::destroy();
    }
}
