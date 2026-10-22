<?php
declare(strict_types=1);

namespace App\Core;

/** One token per session, sent in every admin POST form as `_csrf`. */
final class Csrf
{
    public static function token(): string
    {
        $t = Session::get('_csrf');
        if (!is_string($t) || $t === '') {
            $t = bin2hex(random_bytes(32));
            Session::set('_csrf', $t);
        }
        return $t;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function valid(string $given): bool
    {
        $t = Session::get('_csrf');
        return is_string($t) && $t !== '' && hash_equals($t, $given);
    }
}
