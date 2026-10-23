<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Who did what in the admin, when and from which IP (activity_log, docs/plan/03-backend.md §2.13). */
final class ActivityRepository
{
    /** entity (the admin path, /admin/<entity>) => [label, table, name column]; table null = nothing to look up */
    public const ENTITIES = [
        'divisions' => ['ຂະແໜງ', 'divisions', 'name_lo'],
        'groups' => ['ກຸ່ມສິນຄ້າ', 'product_groups', 'name_lo'],
        'products' => ['ສິນຄ້າ', 'products', 'name'],
        'suppliers' => ['ຜູ້ສະໜອງ', 'suppliers', 'name'],
        'news' => ['ຂ່າວ', 'news', 'title_lo'],
        'downloads' => ['ໄຟລ໌ດາວໂຫຼດ', 'downloads', 'title_lo'],
        'hero' => ['Hero', 'hero_slides', 'title_lo'],
        'faqs' => ['FAQ', 'faqs', 'question_lo'],
        'leaders' => ['ຜູ້ບໍລິຫານ', 'leaders', 'name_lo'],
        'media' => ['ຄັງສື່', 'media', 'original_name'],
        'messages' => ['ຂໍ້ຄວາມ', 'messages', 'name'],
        'users' => ['ຜູ້ໃຊ້', 'users', 'name'],
        'seo' => ['SEO', null, null],
        'settings' => ['ການຕັ້ງຄ່າ', null, null],
        'theme' => ['ຕີມສີ', null, null],
        'publish' => ['ເວັບໄຊ', null, null],
    ];
    public const ACTIONS = [
        'create' => 'ເພີ່ມ', 'update' => 'ແກ້ໄຂ', 'delete' => 'ລຶບ', 'toggle' => 'ສະແດງ/ເຊື່ອງ', 'move' => 'ປ່ຽນລຳດັບ',
        'copy' => 'ສຳເນົາ', 'link' => 'ເຊື່ອມສິນຄ້າ', 'unlink' => 'ຖອນສິນຄ້າ', 'primary' => 'ຕັ້ງເປັນຜູ້ສະໜອງຫຼັກ',
        'upload' => 'ອັບໂຫຼດ', 'note' => 'ເພີ່ມບັນທຶກ', 'export' => 'Export CSV', 'publish' => 'ເຜີຍແຜ່',
        'rollback' => 'ກັບຄືນຊຸດເກົ່າ', 'login' => 'ເຂົ້າລະບົບ', 'password' => 'ປ່ຽນລະຫັດຜ່ານ',
    ];
    public const PER_PAGE = 50;

    public function __construct(private Db $db)
    {
    }

    public function add(?int $userId, string $action, ?string $entity, ?int $entityId, array $details, ?string $ipBinary): void
    {
        $this->db->insert('activity_log', [
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'details' => $details === [] ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'ip' => $ipBinary,
        ]);
    }

    /**
     * Newest first, with the user's name, the item's current name (item_name, null once it is deleted) and for a
     * deleted item the name the log kept when it was created or deleted (logged_name).
     * @param array{user?: string, entity?: string} $filters
     */
    public function page(array $filters, int $page): array
    {
        [$where, $params] = self::where($filters);
        $rows = $this->db->all('SELECT a.*, u.name AS user_name FROM activity_log a LEFT JOIN users u ON u.id = a.user_id' . $where
            . ' ORDER BY a.id DESC LIMIT ' . self::PER_PAGE . ' OFFSET ' . (max(1, $page) - 1) * self::PER_PAGE, $params);
        $names = $this->currentNames($rows);
        $gone = [];
        foreach ($rows as &$r) {
            $r['details'] = $r['details'] === null ? [] : json_decode((string) $r['details'], true);
            $r['item_name'] = $names[(string) $r['entity']][(int) $r['entity_id']] ?? null;
            if ($r['item_name'] === null && $r['entity_id'] !== null) {
                $gone[(string) $r['entity']][(int) $r['entity_id']] = true;
            }
        }
        $logged = $this->loggedNames($gone);
        foreach ($rows as &$r) {
            $r['logged_name'] = $r['details']['name'] ?? $logged[(string) $r['entity']][(int) $r['entity_id']] ?? null;
        }
        return $rows;
    }

    /**
     * Names kept in the log (details.name of create/delete) for items that no longer exist.
     * @param array<string, array<int, true>> $gone entity => id => true
     * @return array<string, array<int, string>>
     */
    private function loggedNames(array $gone): array
    {
        $out = [];
        foreach ($gone as $entity => $set) {
            $list = array_keys($set);
            $rows = $this->db->all("SELECT entity_id, JSON_UNQUOTE(JSON_EXTRACT(details, '$.name')) AS name FROM activity_log
                WHERE entity = ? AND entity_id IN (" . implode(',', array_fill(0, count($list), '?')) . ") AND JSON_EXTRACT(details, '$.name') IS NOT NULL
                ORDER BY id", array_merge([$entity], $list));
            foreach ($rows as $x) {
                $out[$entity][(int) $x['entity_id']] = (string) $x['name'];
            }
        }
        return $out;
    }

    public function count(array $filters): int
    {
        [$where, $params] = self::where($filters);
        return (int) $this->db->value('SELECT COUNT(*) FROM activity_log a' . $where, $params);
    }

    /** Users that appear in the log, for the filter. @return array<int, string> */
    public function users(): array
    {
        $out = [];
        foreach ($this->db->all('SELECT DISTINCT u.id, u.name FROM activity_log a JOIN users u ON u.id = a.user_id ORDER BY u.name') as $u) {
            $out[(int) $u['id']] = (string) $u['name'];
        }
        return $out;
    }

    public function purge(int $days): int
    {
        return $this->db->run('DELETE FROM activity_log WHERE created_at < NOW() - INTERVAL ' . $days . ' DAY')->rowCount();
    }

    /** @return array{0: string, 1: array} */
    private static function where(array $filters): array
    {
        $sql = [];
        $params = [];
        if (($filters['user'] ?? '') !== '') {
            $sql[] = 'a.user_id = ?';
            $params[] = (int) $filters['user'];
        }
        if (isset(self::ENTITIES[$filters['entity'] ?? ''])) {
            $sql[] = 'a.entity = ?';
            $params[] = $filters['entity'];
        }
        return [$sql === [] ? '' : ' WHERE ' . implode(' AND ', $sql), $params];
    }

    /** @return array<string, array<int, string>> entity => id => name, one query per entity on the page */
    private function currentNames(array $rows): array
    {
        $ids = [];
        foreach ($rows as $r) {
            if ($r['entity_id'] !== null && (self::ENTITIES[(string) $r['entity']][1] ?? null) !== null) {
                $ids[$r['entity']][(int) $r['entity_id']] = true;
            }
        }
        $out = [];
        foreach ($ids as $entity => $set) {
            [, $table, $col] = self::ENTITIES[$entity];
            $list = array_keys($set);
            $rowsOf = $this->db->all("SELECT id, `{$col}` AS name FROM `{$table}` WHERE id IN (" . implode(',', array_fill(0, count($list), '?')) . ')', $list);
            foreach ($rowsOf as $x) {
                $out[$entity][(int) $x['id']] = (string) $x['name'];
            }
        }
        return $out;
    }
}
