<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Product groups (product_groups): sections of a division page, anchored as <division>.html#<code>. */
final class GroupRepository
{
    public function __construct(private Db $db)
    {
    }

    /** All groups, by division then display order, with product counts. */
    public function all(): array
    {
        return $this->db->all(
            'SELECT g.*, d.name_lo AS division_name, d.slug AS division_slug, d.is_visible AS division_visible,
                    (SELECT COUNT(*) FROM products p WHERE p.group_id = g.id) AS product_count
             FROM product_groups g JOIN divisions d ON d.id = g.division_id ORDER BY d.sort, d.id, g.sort, g.id'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM product_groups WHERE id = ?', [$id]);
    }

    public function codeTaken(string $code, int $exceptId = 0): bool
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM product_groups WHERE code = ? AND id <> ?', [$code, $exceptId]) > 0;
    }

    /** A new group goes last in its division. */
    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'product_groups', 'division_id', (int) $data['division_id']);
        return $this->db->insert('product_groups', $data);
    }

    /** Moving to another division puts the group last there. */
    public function update(int $id, array $data, bool $divisionChanged): void
    {
        if ($divisionChanged) {
            $data['sort'] = SortOrder::next($this->db, 'product_groups', 'division_id', (int) $data['division_id']);
        }
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE product_groups SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    public function countProducts(int $id): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM products WHERE group_id = ?', [$id]);
    }

    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM product_groups WHERE id = ?', [$id]);
    }

    /** Within its own division. */
    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'product_groups', $id, $dir, 'division_id');
    }
}
