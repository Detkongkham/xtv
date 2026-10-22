<?php
declare(strict_types=1);

namespace App\Tests;

use App\Service\TimeToken;
use PHPUnit\Framework\TestCase;

final class TimeTokenTest extends TestCase
{
    private TimeToken $token;

    protected function setUp(): void
    {
        $this->token = new TimeToken(str_repeat('k', 64));
    }

    public function testAcceptedBetweenThreeSecondsAndOneDay(): void
    {
        $t = $this->token->issue(1_800_000_000);
        $this->assertSame('too_fast', $this->token->verify($t, 1_800_000_002));
        $this->assertSame('ok', $this->token->verify($t, 1_800_000_003));
        $this->assertSame('ok', $this->token->verify($t, 1_800_086_400));
        $this->assertSame('expired', $this->token->verify($t, 1_800_086_401));
    }

    public function testTamperedTokenIsInvalid(): void
    {
        $t = $this->token->issue(1_800_000_000);
        $forged = '1700000000' . substr($t, 10); // older time, same signature
        $this->assertSame('invalid', $this->token->verify($forged, 1_800_000_100));
        $this->assertSame('invalid', $this->token->verify('garbage', 1_800_000_100));
        $this->assertSame('invalid', (new TimeToken(str_repeat('x', 64)))->verify($t, 1_800_000_100));
    }

    public function testShortSecretIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        new TimeToken('change-me');
    }
}
