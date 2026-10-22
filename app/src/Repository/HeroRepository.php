<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Slides at the top of the home page (hero_slides). */
final class HeroRepository
{
    public const MAX = 5;

    public function __construct(private Db $db)
    {
    }

    /** In display order, with the picture or video and the poster, for thumbnails. */
    public function all(): array
    {
        return $this->db->all(
            'SELECT h.*, m.kind, m.path, m.variants, m.original_name, p.path AS poster_path, p.variants AS poster_variants
             FROM hero_slides h JOIN media m ON m.id = h.media_id LEFT JOIN media p ON p.id = h.poster_id ORDER BY h.sort, h.id'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM hero_slides WHERE id = ?', [$id]);
    }

    public function count(): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM hero_slides');
    }

    public function countVisible(): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM hero_slides WHERE is_visible = 1');
    }

    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'hero_slides');
        return $this->db->insert('hero_slides', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE hero_slides SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    /** The picture or video stays in the media library. */
    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM hero_slides WHERE id = ?', [$id]);
    }

    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'hero_slides', $id, $dir);
    }
}
