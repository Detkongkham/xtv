<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Questions on the FAQ page (faqs). */
final class FaqRepository
{
    public function __construct(private Db $db)
    {
    }

    /** In display order. */
    public function all(): array
    {
        return $this->db->all('SELECT * FROM faqs ORDER BY sort, id');
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM faqs WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'faqs');
        return $this->db->insert('faqs', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE faqs SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM faqs WHERE id = ?', [$id]);
    }

    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'faqs', $id, $dir);
    }
}
