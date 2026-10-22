<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Counts events per bucket and IP inside a time window.
 * Storage is pluggable so the rules can be tested without MySQL (see tests/RateLimiterTest.php).
 */
final class RateLimiter
{
    public function __construct(private RateStore $store)
    {
    }

    /**
     * @param array<int, array{0:string,1:int,2:int}> $limits  [bucket, max events, window seconds]
     * @return bool true when every limit still has room
     */
    public function allows(string $ipBinary, array $limits): bool
    {
        foreach ($limits as [$bucket, $max, $seconds]) {
            if ($this->store->count($bucket, $ipBinary, $seconds) >= $max) {
                return false;
            }
        }
        return true;
    }

    public function hit(string $bucket, string $ipBinary): void
    {
        $this->store->add($bucket, $ipBinary);
    }
}
