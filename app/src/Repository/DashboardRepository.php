<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Read-only numbers for the dashboard: messages, edits and publishes per period. */
final class DashboardRepository
{
    public function __construct(private Db $db)
    {
    }

    /**
     * One row per day for the last $days days, oldest first.
     * @return array<string, array{messages:int, spam:int, edits:int}> keyed by Y-m-d
     */
    public function daily(int $days): array
    {
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $out[date('Y-m-d', strtotime("-{$i} day"))] = ['messages' => 0, 'spam' => 0, 'edits' => 0];
        }
        $from = array_key_first($out) . ' 00:00:00';
        foreach ($this->db->all(
            "SELECT DATE(created_at) AS d, SUM(status <> 'spam') AS n, SUM(status = 'spam') AS s FROM messages WHERE created_at >= ? GROUP BY d",
            [$from]
        ) as $r) {
            if (isset($out[$r['d']])) {
                $out[$r['d']]['messages'] = (int) $r['n'];
                $out[$r['d']]['spam'] = (int) $r['s'];
            }
        }
        foreach ($this->db->all(
            "SELECT DATE(created_at) AS d, COUNT(*) AS n FROM activity_log WHERE created_at >= ? AND action NOT IN ('login', 'export') GROUP BY d",
            [$from]
        ) as $r) {
            if (isset($out[$r['d']])) {
                $out[$r['d']]['edits'] = (int) $r['n'];
            }
        }
        return $out;
    }

    /**
     * Totals for messages created in [$from, $to).
     * @return array{total:int, spam:int, answered:int, done:int, mailErrors:int, subjects:array<string,int>, langs:array<string,int>}
     */
    public function messages(string $from, string $to): array
    {
        $r = $this->db->one(
            "SELECT SUM(status <> 'spam') AS total, SUM(status = 'spam') AS spam,
                    SUM(status IN ('in_progress', 'done')) AS answered, SUM(status = 'done') AS done,
                    SUM(mail_error IS NOT NULL AND status <> 'spam') AS mail_errors
             FROM messages WHERE created_at >= ? AND created_at < ?",
            [$from, $to]
        ) ?? [];
        $out = [
            'total' => (int) ($r['total'] ?? 0), 'spam' => (int) ($r['spam'] ?? 0), 'answered' => (int) ($r['answered'] ?? 0),
            'done' => (int) ($r['done'] ?? 0), 'mailErrors' => (int) ($r['mail_errors'] ?? 0),
            'subjects' => array_fill_keys(MessageRepository::SUBJECTS, 0), 'langs' => [],
        ];
        foreach (['subject' => 'subjects', 'lang' => 'langs'] as $col => $key) {
            foreach ($this->db->all(
                "SELECT {$col} AS k, COUNT(*) AS n FROM messages WHERE status <> 'spam' AND created_at >= ? AND created_at < ? GROUP BY {$col} ORDER BY n DESC",
                [$from, $to]
            ) as $row) {
                $out[$key][$row['k']] = (int) $row['n'];
            }
        }
        return $out;
    }

    /** @return array{success:int, failed:int} publishes started in [$from, $to) */
    public function publishes(string $from, string $to): array
    {
        $r = $this->db->one(
            "SELECT SUM(status = 'success') AS ok, SUM(status = 'failed') AS failed FROM publish_log WHERE started_at >= ? AND started_at < ?",
            [$from, $to]
        ) ?? [];
        return ['success' => (int) ($r['ok'] ?? 0), 'failed' => (int) ($r['failed'] ?? 0)];
    }

    /** Created_at of the oldest message still marked new, or null. */
    public function oldestNew(): ?string
    {
        $v = $this->db->value("SELECT MIN(created_at) FROM messages WHERE status = 'new'");
        return $v === null || $v === false ? null : (string) $v;
    }

    /** @return array{products:int, news:int, drafts:int, downloads:int, faqs:int} what the public site shows */
    public function content(): array
    {
        $r = $this->db->one(
            "SELECT (SELECT COUNT(*) FROM products WHERE is_visible = 1) AS products,
                    (SELECT COUNT(*) FROM news WHERE status = 'published') AS news,
                    (SELECT COUNT(*) FROM news WHERE status = 'draft') AS drafts,
                    (SELECT COUNT(*) FROM downloads WHERE is_visible = 1) AS downloads,
                    (SELECT COUNT(*) FROM faqs WHERE is_visible = 1) AS faqs"
        ) ?? [];
        return array_map('intval', $r + ['products' => 0, 'news' => 0, 'drafts' => 0, 'downloads' => 0, 'faqs' => 0]);
    }
}
