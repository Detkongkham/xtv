<?php
declare(strict_types=1);

namespace App\Service;

use App\Core\App;

/**
 * Cloudflare Turnstile check for the contact form (docs/plan/06-phases.md P3-D8). Off until both keys are set in
 * config.php ('turnstile' => ['site_key', 'secret']); the site key reaches the pages when they are published.
 * Cloudflare's test keys: site 1x00000000000000000000AA, secret 1x0000000000000000000000000000000AA (always passes)
 * or 2x0000000000000000000000000000000AA (always fails).
 */
final class Turnstile
{
    public const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** @var callable(string, array<string, string>): ?string POST form fields, response body or null on a network error */
    private $post;

    public function __construct(private string $secret, ?callable $post = null)
    {
        $this->post = $post ?? self::curlPost(...);
    }

    /** null when Turnstile is off (a key missing in config.php). */
    public static function fromConfig(): ?self
    {
        $secret = (string) App::config('turnstile.secret', '');
        return $secret !== '' && self::siteKey() !== '' ? new self($secret) : null;
    }

    /** Public key for the widget; '' = off. */
    public static function siteKey(): string
    {
        return (string) App::config('turnstile.secret', '') !== '' ? (string) App::config('turnstile.site_key', '') : '';
    }

    /**
     * 'ok', 'failed' (no token, or Cloudflare says no) or 'unavailable' (Cloudflare could not be reached: the form
     * is let through, honeypot, time token and rate limits still apply, so customers are not locked out).
     */
    public function verify(string $token, string $ip): string
    {
        if ($token === '' || strlen($token) > 2048) {
            return 'failed';
        }
        $body = ($this->post)(self::VERIFY_URL, ['secret' => $this->secret, 'response' => $token, 'remoteip' => $ip]);
        $data = $body === null ? null : json_decode($body, true);
        if (!is_array($data)) {
            App::logger()->warning('turnstile: no answer from Cloudflare', ['body' => $body === null ? null : substr($body, 0, 200)]);
            return 'unavailable';
        }
        if (($data['success'] ?? false) !== true) {
            App::logger()->info('turnstile: rejected', ['codes' => $data['error-codes'] ?? []]);
            return 'failed';
        }
        return 'ok';
    }

    private static function curlPost(string $url, array $fields): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
        ]);
        $body = curl_exec($ch);
        return is_string($body) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200 ? $body : null;
    }
}
