<?php
declare(strict_types=1);

namespace App\Core;

interface RateStore
{
    /** Events in $bucket from $ipBinary during the last $seconds. */
    public function count(string $bucket, string $ipBinary, int $seconds): int;

    public function add(string $bucket, string $ipBinary): void;
}
