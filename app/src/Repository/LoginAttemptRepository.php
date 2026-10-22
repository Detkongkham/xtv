<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

final class LoginAttemptRepository
{
    public function __construct(private Db $db)
    {
    }

    public function record(string $email, string $ipBinary, bool $success): void
    {
        $this->db->insert('login_attempts', [
            'email' => mb_substr(mb_strtolower(trim($email)), 0, 190),
            'ip' => $ipBinary,
            'success' => $success ? 1 : 0,
        ]);
    }

    /** Failed attempts in the last $seconds for this email OR this IP, whichever is higher. */
    public function recentFailures(string $email, string $ipBinary, int $seconds): int
    {
        $byEmail = (int) $this->db->value(
            'SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0 AND created_at > NOW() - INTERVAL ? SECOND',
            [mb_strtolower(trim($email)), $seconds]
        );
        $byIp = (int) $this->db->value(
            'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND created_at > NOW() - INTERVAL ? SECOND',
            [$ipBinary, $seconds]
        );
        return max($byEmail, $byIp);
    }
}
