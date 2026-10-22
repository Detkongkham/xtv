<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** News items (news). Published ones with a body get a page news-<slug>.html. */
final class NewsRepository
{
    public const CATEGORIES = ['product', 'company', 'event'];
    public const STATUSES = ['draft', 'published'];

    public function __construct(private Db $db)
    {
    }

    /** Newest first. */
    public function all(string $status = ''): array
    {
        $where = in_array($status, self::STATUSES, true) ? 'WHERE n.status = ?' : '';
        return $this->db->all(
            "SELECT n.id, n.slug, n.category, n.status, n.published_on, n.title_lo, n.title_en, n.link_url, n.body_en <> '' AS has_page,
                    m.path AS cover_path, m.variants AS cover_variants
             FROM news n LEFT JOIN media m ON m.id = n.cover_id {$where} ORDER BY n.published_on DESC, n.id DESC",
            $where ? [$status] : []
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM news WHERE id = ?', [$id]);
    }

    public function slugTaken(string $slug, int $exceptId): bool
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM news WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }

    public function create(array $data): int
    {
        return $this->db->insert('news', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE news SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM news WHERE id = ?', [$id]);
    }
}
