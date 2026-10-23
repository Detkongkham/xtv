<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Core\Lang;
use App\Publish\SiteData;
use App\Publish\SiteView;
use App\Service\Turnstile;
use PHPUnit\Framework\TestCase;

/** Turnstile on the contact form (P3-D8): off without keys, the server check, the widget on the page. */
final class TurnstileTest extends TestCase
{
    private const KEYS = ['site_key' => '1x00000000000000000000AA', 'secret' => '1x0000000000000000000000000000000AA'];

    protected function tearDown(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
    }

    public function testOffUntilBothKeysAreSet(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        self::assertNull(Turnstile::fromConfig());
        App::boot(['env' => 'test', 'turnstile' => ['site_key' => self::KEYS['site_key'], 'secret' => '']], dirname(__DIR__));
        self::assertNull(Turnstile::fromConfig());
        self::assertSame('', Turnstile::siteKey());
        App::boot(['env' => 'test', 'turnstile' => self::KEYS], dirname(__DIR__));
        self::assertInstanceOf(Turnstile::class, Turnstile::fromConfig());
        self::assertSame(self::KEYS['site_key'], Turnstile::siteKey());
    }

    public function testVerify(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        $sent = [];
        $answer = static function (?string $body) use (&$sent): callable {
            return static function (string $url, array $fields) use ($body, &$sent): ?string {
                $sent = $fields;
                return $body;
            };
        };
        self::assertSame('ok', (new Turnstile('s', $answer('{"success":true}')))->verify('tok', '203.0.113.5'));
        self::assertSame(['secret' => 's', 'response' => 'tok', 'remoteip' => '203.0.113.5'], $sent);
        self::assertSame('failed', (new Turnstile('s', $answer('{"success":false,"error-codes":["invalid-input-response"]}')))->verify('tok', '::1'));
        self::assertSame('unavailable', (new Turnstile('s', $answer(null)))->verify('tok', '::1'));
        self::assertSame('unavailable', (new Turnstile('s', $answer('<html>')))->verify('tok', '::1'));
        $sent = [];
        self::assertSame('failed', (new Turnstile('s', $answer('{"success":true}')))->verify('', '::1'));
        self::assertSame([], $sent);   // no token: Cloudflare is not asked
    }

    public function testWidgetOnTheContactPageButNotInPreviews(): void
    {
        App::boot(['env' => 'test', 'turnstile' => self::KEYS], dirname(__DIR__));
        $data = SiteData::fromSeedFile(App::path('database/seed/seed.json'));
        $public = dirname(__DIR__, 2) . '/public';
        $live = (new SiteView(new Lang('en'), $data, $public))->partial('contact-form');
        self::assertStringContainsString('data-turnstile="' . self::KEYS['site_key'] . '"', $live);
        self::assertStringContainsString('data-turnstile-box hidden', $live);
        self::assertStringNotContainsString(self::KEYS['secret'], $live);
        $preview = (new SiteView(new Lang('en'), $data, $public, '../assets', true))->partial('contact-form');
        self::assertStringNotContainsString('data-turnstile', $preview);
    }
}
