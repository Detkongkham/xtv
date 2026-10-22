<?php
declare(strict_types=1);

namespace App\Core;

/** RateStore on the `rate_limits` table. Rows older than 1 day are removed by bin/cleanup.php. */
final class DbRateStore implements RateStore
{
    public function __construct(private Db $db)
    {
    }

    public function count(string $bucket, string $ipBinary, int $seconds): int
    {
        return (int) $this->db->value(
            'SELECT COUNT(*) FROM rate_limits WHERE bucket = ? AND ip = ? AND created_at > NOW() - INTERVAL ? SECOND',
            [$bucket, $ipBinary, $seconds]
        );
    }

    public function add(string $bucket, string $ipBinary): void
    {
        $this->db->insert('rate_limits', ['bucket' => $bucket, 'ip' => $ipBinary]);
    }
}
