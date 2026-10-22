<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\RateLimiter;
use App\Core\RateStore;
use PHPUnit\Framework\TestCase;

/** In-memory store with a clock the test controls. */
final class MemoryRateStore implements RateStore
{
    public int $now = 1_000_000;
    /** @var array<int, array{0:string,1:string,2:int}> */
    private array $events = [];

    public function count(string $bucket, string $ipBinary, int $seconds): int
    {
        $n = 0;
        foreach ($this->events as [$b, $ip, $t]) {
            if ($b === $bucket && $ip === $ipBinary && $t > $this->now - $seconds) {
                $n++;
            }
        }
        return $n;
    }

    public function add(string $bucket, string $ipBinary): void
    {
        $this->events[] = [$bucket, $ipBinary, $this->now];
    }
}

final class RateLimiterTest extends TestCase
{
    private const WITH_TOKEN = [['contact', 20, 86400], ['contact', 5, 600]];
    private const NO_TOKEN = [['contact', 20, 86400], ['contact', 5, 600], ['contact_nojs', 2, 600]];

    private MemoryRateStore $store;
    private RateLimiter $limiter;
    private string $ip;

    protected function setUp(): void
    {
        $this->store = new MemoryRateStore();
        $this->limiter = new RateLimiter($this->store);
        $this->ip = inet_pton('203.0.113.7');
    }

    public function testFiveSubmitsPerTenMinutesWithToken(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($this->limiter->allows($this->ip, self::WITH_TOKEN), "submit #{$i}");
            $this->limiter->hit('contact', $this->ip);
            $this->store->now += 10;
        }
        $this->assertFalse($this->limiter->allows($this->ip, self::WITH_TOKEN));
    }

    public function testWindowSlides(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->limiter->hit('contact', $this->ip);
        }
        $this->assertFalse($this->limiter->allows($this->ip, self::WITH_TOKEN));
        $this->store->now += 601;
        $this->assertTrue($this->limiter->allows($this->ip, self::WITH_TOKEN));
    }

    public function testNoTokenAllowsOnlyTwo(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue($this->limiter->allows($this->ip, self::NO_TOKEN));
            $this->limiter->hit('contact', $this->ip);
            $this->limiter->hit('contact_nojs', $this->ip);
        }
        $this->assertFalse($this->limiter->allows($this->ip, self::NO_TOKEN));
        // the same visitor with JS still has room under the normal limit
        $this->assertTrue($this->limiter->allows($this->ip, self::WITH_TOKEN));
    }

    public function testTwentyPerDay(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->assertTrue($this->limiter->allows($this->ip, self::WITH_TOKEN), "submit #{$i}");
            $this->limiter->hit('contact', $this->ip);
            $this->store->now += 601; // always past the 10-minute window
        }
        $this->assertFalse($this->limiter->allows($this->ip, self::WITH_TOKEN));
        $this->store->now += 86400;
        $this->assertTrue($this->limiter->allows($this->ip, self::WITH_TOKEN));
    }

    public function testIpsAreCountedSeparately(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->limiter->hit('contact', $this->ip);
        }
        $this->assertFalse($this->limiter->allows($this->ip, self::WITH_TOKEN));
        $this->assertTrue($this->limiter->allows(inet_pton('2001:db8::1'), self::WITH_TOKEN));
    }
}
