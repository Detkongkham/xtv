<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Owners and directors on the about page (leaders). */
final class LeaderRepository
{
    public function __construct(private Db $db)
    {
    }

    /** In display order, with the photo's path and variants (null without a photo). */
    public function all(): array
    {
        return $this->db->all('SELECT l.*, m.path, m.variants FROM leaders l LEFT JOIN media m ON m.id = l.photo_id ORDER BY l.sort, l.id');
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM leaders WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'leaders');
        return $this->db->insert('leaders', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE leaders SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM leaders WHERE id = ?', [$id]);
    }

    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'leaders', $id, $dir);
    }
}
