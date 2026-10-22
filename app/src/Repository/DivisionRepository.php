<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** The business divisions (divisions); each has its own page <slug>.html. */
final class DivisionRepository
{
    public function __construct(private Db $db)
    {
    }

    /** All divisions in display order, with picture and counts. */
    public function all(): array
    {
        return $this->db->all(
            'SELECT d.*, m.path AS image_path, m.variants AS image_variants,
                    (SELECT COUNT(*) FROM product_groups g WHERE g.division_id = d.id) AS group_count,
                    (SELECT COUNT(*) FROM products p JOIN product_groups g ON g.id = p.group_id WHERE g.division_id = d.id) AS product_count
             FROM divisions d LEFT JOIN media m ON m.id = d.image_id ORDER BY d.sort, d.id'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM divisions WHERE id = ?', [$id]);
    }

    /** id => Lao name, in order (for selects) */
    public function names(): array
    {
        return array_column($this->db->all('SELECT id, name_lo FROM divisions ORDER BY sort, id'), 'name_lo', 'id');
    }

    public function slugTaken(string $slug, int $exceptId): bool
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM divisions WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }

    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'divisions');
        return $this->db->insert('divisions', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE divisions SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    public function countGroups(int $id): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM product_groups WHERE division_id = ?', [$id]);
    }

    /** Only when it has no groups (the caller checks; the foreign key also refuses). */
    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM divisions WHERE id = ?', [$id]);
    }

    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'divisions', $id, $dir);
    }
}
