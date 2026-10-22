<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Signed timestamp for the contact form: "<unix time>.<hmac>".
 * JS fetches one when the form is first focused; a submit is accepted 3 s – 24 h after that.
 */
final class TimeToken
{
    public const MIN_AGE = 3;
    public const MAX_AGE = 86400;

    public function __construct(private string $secret)
    {
        if (strlen($secret) < 32) {
            throw new \RuntimeException('hmac_secret in config.php must be at least 32 characters');
        }
    }

    public function issue(?int $now = null): string
    {
        $ts = (string) ($now ?? time());
        return $ts . '.' . $this->sign($ts);
    }

    /** @return 'ok'|'invalid'|'too_fast'|'expired' */
    public function verify(string $token, ?int $now = null): string
    {
        if (!preg_match('/^(\d{9,11})\.([0-9a-f]{64})$/', $token, $m)) {
            return 'invalid';
        }
        if (!hash_equals($this->sign($m[1]), $m[2])) {
            return 'invalid';
        }
        $age = ($now ?? time()) - (int) $m[1];
        if ($age < self::MIN_AGE) {
            return 'too_fast';
        }
        return $age > self::MAX_AGE ? 'expired' : 'ok';
    }

    private function sign(string $ts): string
    {
        return hash_hmac('sha256', 'contact|' . $ts, $this->secret);
    }
}
