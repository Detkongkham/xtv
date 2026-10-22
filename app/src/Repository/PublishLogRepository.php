<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** History of publishes and rollbacks (publish_log). */
final class PublishLogRepository
{
    public function __construct(private Db $db)
    {
    }

    public function start(?int $userId): int
    {
        return $this->db->insert('publish_log', ['user_id' => $userId, 'status' => 'running']);
    }

    public function finish(int $id, string $status, ?int $pages, int $ms, ?string $buildDir, ?string $error = null, ?string $warnings = null): void
    {
        $this->db->run(
            'UPDATE publish_log SET status = ?, pages_written = ?, duration_ms = ?, build_dir = ?, error = ?, warnings = ?, finished_at = NOW() WHERE id = ?',
            [$status, $pages, $ms, $buildDir, $error, $warnings, $id]
        );
    }

    /** The build that is online now: the last successful publish or rollback. */
    public function liveBuild(): ?string
    {
        $dir = $this->db->value("SELECT build_dir FROM publish_log WHERE status IN ('success','rolled_back') AND build_dir IS NOT NULL ORDER BY id DESC LIMIT 1");
        return $dir === null ? null : (string) $dir;
    }

    public function latest(int $limit = 15): array
    {
        return $this->db->all(
            'SELECT l.*, u.name AS user_name FROM publish_log l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT ' . max(1, $limit)
        );
    }

    public function lastSuccess(): ?array
    {
        return $this->db->one("SELECT * FROM publish_log WHERE status = 'success' ORDER BY id DESC LIMIT 1");
    }

    /** The publish or rollback that put the online site in place, with who did it. */
    public function lastLive(): ?array
    {
        return $this->db->one(
            "SELECT l.*, u.name AS user_name FROM publish_log l LEFT JOIN users u ON u.id = l.user_id
             WHERE l.status IN ('success','rolled_back') ORDER BY l.id DESC LIMIT 1"
        );
    }

    /**
     * Why the online site may differ from the data, or null when it is up to date.
     * never: nothing published yet. rolled_back: an older build is online. changed: edits after the last publish.
     * An edit in the same second as the publish counts as unpublished: a false alarm is safer than a missed change.
     */
    public static function pendingReason(?array $live, ?string $changedAt): ?string
    {
        return match (true) {
            $live === null => 'never',
            $live['status'] === 'rolled_back' => 'rolled_back',
            $changedAt !== null && strtotime($changedAt) >= strtotime((string) $live['started_at']) => 'changed',
            default => null,
        };
    }

    /** A publish that died without finishing (e.g. PHP was killed) stays "running"; mark it so it does not confuse the history. */
    public function failStale(): void
    {
        $this->db->run("UPDATE publish_log SET status = 'failed', error = 'ຢຸດກາງຄັນ (ບໍ່ສຳເລັດ)', finished_at = NOW() WHERE status = 'running'");
    }
}
