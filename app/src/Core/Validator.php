<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Small rule-based validator. Rules per field, checked in order; the first failure wins.
 *
 *   required        not empty after trimming
 *   len:min,max     length in characters (mb_strlen); empty values skip this rule
 *   email           valid address
 *   phone           digits, + - ( ) . and spaces, with at least 6 digits
 *   in:a,b,c        one of the listed values; empty values skip this rule
 *   accepted        checkbox ticked ("on", "1", "yes", "true")
 *
 * Error codes (sent to the browser, which translates them): required, too_short, too_long, invalid.
 */
final class Validator
{
    /** Trim, normalise line breaks and drop control characters. Newlines are kept only when $multiline. */
    public static function clean(string $value, bool $multiline = false): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        // control characters (Cc) only: zero-width marks used in Lao/Thai text are kept
        if ($multiline) {
            $value = (string) preg_replace('/[^\P{Cc}\n\t]/u', '', $value);
            $value = (string) preg_replace("/\n{3,}/", "\n\n", $value);
        } else {
            $value = (string) preg_replace('/[\p{Cc}\s]+/u', ' ', $value);
        }
        return trim($value);
    }

    /**
     * @param array<string, string> $data  cleaned input
     * @param array<string, string[]> $rules
     * @return array<string, string> field => error code; empty when everything is valid
     */
    public static function check(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $list) {
            $value = (string) ($data[$field] ?? '');
            foreach ($list as $rule) {
                $code = self::apply($rule, $value);
                if ($code !== null) {
                    $errors[$field] = $code;
                    break;
                }
            }
        }
        return $errors;
    }

    private static function apply(string $rule, string $value): ?string
    {
        [$name, $arg] = array_pad(explode(':', $rule, 2), 2, '');
        $empty = $value === '';

        switch ($name) {
            case 'required':
                return $empty ? 'required' : null;
            case 'accepted':
                return in_array(strtolower($value), ['on', '1', 'yes', 'true'], true) ? null : 'required';
            case 'len':
                if ($empty) {
                    return null;
                }
                [$min, $max] = array_map('intval', explode(',', $arg));
                $n = mb_strlen($value);
                return $n < $min ? 'too_short' : ($n > $max ? 'too_long' : null);
            case 'email':
                return $empty || self::isEmail($value) ? null : 'invalid';
            case 'phone':
                return $empty || self::isPhone($value) ? null : 'invalid';
            case 'in':
                return $empty || in_array($value, explode(',', $arg), true) ? null : 'invalid';
        }
        throw new \InvalidArgumentException('Unknown rule: ' . $rule);
    }

    public static function isEmail(string $v): bool
    {
        return mb_strlen($v) <= 190 && filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isPhone(string $v): bool
    {
        if (!preg_match('/^[0-9+\-().\s]{6,30}$/', $v)) {
            return false;
        }
        return preg_match_all('/\d/', $v) >= 6;
    }
}
