<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

final class MessageRepository
{
    public const STATUSES = ['new', 'in_progress', 'done', 'spam'];
    public const SUBJECTS = ['general', 'sales', 'service', 'other'];
    public const PER_PAGE = 25;
    /** list order: new messages first, newest first, oldest first */
    public const SORTS = ['priority', 'newest', 'oldest'];

    public function __construct(private Db $db)
    {
    }

    public function create(array $data): int
    {
        return $this->db->insert('messages', $data);
    }

    public function setMailError(int $id, ?string $error): void
    {
        $this->db->run('UPDATE messages SET mail_error = ? WHERE id = ?', [$error === null ? null : mb_substr($error, 0, 255), $id]);
    }

    public function find(int $id): ?array
    {
        return $this->db->one(
            'SELECT m.*, INET6_NTOA(m.ip) AS ip_text, u.name AS assigned_name
             FROM messages m LEFT JOIN users u ON u.id = m.assigned_to WHERE m.id = ?',
            [$id]
        );
    }

    /**
     * @param array{status?:string,subject?:string,from?:string,to?:string,q?:string,sort?:string} $f
     * @return array{rows: array, total: int}
     */
    public function search(array $f, int $page = 1, ?int $perPage = self::PER_PAGE): array
    {
        [$where, $params] = $this->where($f);
        $total = (int) $this->db->value("SELECT COUNT(*) FROM messages m {$where}", $params);
        $sql = "SELECT m.id, m.name, m.org, m.phone, m.email, m.subject, m.body, m.lang, m.status, m.mail_error, m.created_at,
                       u.name AS assigned_name
                FROM messages m LEFT JOIN users u ON u.id = m.assigned_to {$where}
                ORDER BY " . match ($f['sort'] ?? '') {
                    'newest' => 'm.created_at DESC, m.id DESC',
                    'oldest' => 'm.created_at, m.id',
                    default => "FIELD(m.status, 'new', 'in_progress', 'done', 'spam'), m.created_at DESC, m.id DESC",
                };
        if ($perPage !== null) {
            $sql .= ' LIMIT :lim OFFSET :off';
            $params['lim'] = $perPage;
            $params['off'] = max(0, ($page - 1) * $perPage);
        }
        return ['rows' => $this->db->all($sql, $params), 'total' => $total];
    }

    /** @return array{0:string,1:array} */
    private function where(array $f): array
    {
        $w = [];
        $p = [];
        if (in_array($f['status'] ?? '', self::STATUSES, true)) {
            $w[] = 'm.status = :status';
            $p['status'] = $f['status'];
        } elseif (($f['status'] ?? '') === '') {
            $w[] = "m.status <> 'spam'"; // spam is hidden unless asked for
        }
        if (in_array($f['subject'] ?? '', self::SUBJECTS, true)) {
            $w[] = 'm.subject = :subject';
            $p['subject'] = $f['subject'];
        }
        if (self::isDate($f['from'] ?? '')) {
            $w[] = 'm.created_at >= :from';
            $p['from'] = $f['from'] . ' 00:00:00';
        }
        if (self::isDate($f['to'] ?? '')) {
            $w[] = 'm.created_at < :to + INTERVAL 1 DAY';
            $p['to'] = $f['to'];
        }
        $q = trim($f['q'] ?? '');
        if ($q !== '') {
            $w[] = '(m.name LIKE :q1 OR m.email LIKE :q2 OR m.phone LIKE :q3 OR m.org LIKE :q4 OR m.body LIKE :q5)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $p += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like];
        }
        return [$w === [] ? '' : 'WHERE ' . implode(' AND ', $w), $p];
    }

    private static function isDate(string $v): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false;
    }

    public function countByStatus(): array
    {
        $out = array_fill_keys(self::STATUSES, 0);
        foreach ($this->db->all('SELECT status, COUNT(*) AS n FROM messages GROUP BY status') as $r) {
            $out[$r['status']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return array<string, int> messages per subject, spam left out */
    public function countBySubject(): array
    {
        $out = array_fill_keys(self::SUBJECTS, 0);
        foreach ($this->db->all("SELECT subject, COUNT(*) AS n FROM messages WHERE status <> 'spam' GROUP BY subject") as $r) {
            $out[$r['subject']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return array{today:int, done7:int, mine:int} received since midnight, closed in 7 days, open and assigned to $userId */
    public function inboxStats(?int $userId): array
    {
        $r = $this->db->one(
            "SELECT SUM(status <> 'spam' AND created_at >= CURDATE()) AS today,
                    SUM(status = 'done' AND updated_at >= NOW() - INTERVAL 7 DAY) AS done7,
                    SUM(status = 'in_progress' AND assigned_to = :u) AS mine
             FROM messages",
            ['u' => $userId ?? 0]
        ) ?? [];
        return ['today' => (int) ($r['today'] ?? 0), 'done7' => (int) ($r['done7'] ?? 0), 'mine' => (int) ($r['mine'] ?? 0)];
    }

    /**
     * Same status for several messages; $assignTo fills in the handler only where nobody is assigned yet.
     * @param int[] $ids
     * @return int[] ids that exist
     */
    public function setStatus(array $ids, string $status, ?int $assignTo = null): array
    {
        if ($ids === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $found = array_map('intval', array_column($this->db->all("SELECT id FROM messages WHERE id IN ({$in})", $ids), 'id'));
        if ($found !== []) {
            $in = implode(',', array_fill(0, count($found), '?'));
            $this->db->run("UPDATE messages SET status = ?, assigned_to = COALESCE(assigned_to, ?) WHERE id IN ({$in})", [$status, $assignTo, ...$found]);
        }
        return $found;
    }

    public function countSince(string $interval): int
    {
        // $interval comes from code: '7 DAY', '30 DAY'
        return (int) $this->db->value("SELECT COUNT(*) FROM messages WHERE status <> 'spam' AND created_at >= NOW() - INTERVAL {$interval}");
    }

    public function latestNew(int $limit = 5): array
    {
        return $this->db->all(
            "SELECT id, name, org, subject, lang, created_at, mail_error FROM messages WHERE status = 'new' ORDER BY created_at DESC LIMIT :n",
            ['n' => $limit]
        );
    }

    public function countMailErrors(): int
    {
        return (int) $this->db->value("SELECT COUNT(*) FROM messages WHERE mail_error IS NOT NULL AND status <> 'spam'");
    }

    public function update(int $id, string $status, ?int $assignedTo): void
    {
        $this->db->run('UPDATE messages SET status = ?, assigned_to = ? WHERE id = ?', [$status, $assignedTo, $id]);
    }

    public function notes(int $messageId): array
    {
        return $this->db->all(
            'SELECT n.note, n.created_at, u.name AS user_name FROM message_notes n
             LEFT JOIN users u ON u.id = n.user_id WHERE n.message_id = ? ORDER BY n.created_at, n.id',
            [$messageId]
        );
    }

    public function addNote(int $messageId, ?int $userId, string $note): void
    {
        $this->db->insert('message_notes', ['message_id' => $messageId, 'user_id' => $userId, 'note' => $note]);
    }

    /** Remove spam older than $days. Returns rows deleted. */
    public function purgeSpam(int $days): int
    {
        return $this->db->run("DELETE FROM messages WHERE status = 'spam' AND created_at < NOW() - INTERVAL ? DAY", [$days])->rowCount();
    }
}
