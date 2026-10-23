<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Title and description of the fixed pages (seo_pages), one row per page that has something entered. */
final class SeoPageRepository
{
    public function __construct(private Db $db)
    {
    }

    /** @return array<string, array> page => row */
    public function all(): array
    {
        $out = [];
        foreach ($this->db->all('SELECT * FROM seo_pages') as $row) {
            $out[$row['page']] = $row;
        }
        return $out;
    }

    public function find(string $page): ?array
    {
        return $this->db->one('SELECT * FROM seo_pages WHERE page = ?', [$page]);
    }

    /** All values empty = the row is removed and the page uses its own title and description again. */
    public function save(string $page, array $data): void
    {
        if (array_filter($data, static fn ($v) => $v !== null) === []) {
            $this->db->run('DELETE FROM seo_pages WHERE page = ?', [$page]);
            return;
        }
        $cols = array_keys($data);
        $this->db->run('INSERT INTO seo_pages (page, ' . implode(', ', array_map(static fn ($c) => "`{$c}`", $cols)) . ') VALUES (:page, '
            . implode(', ', array_map(static fn ($c) => ":{$c}", $cols)) . ') ON DUPLICATE KEY UPDATE '
            . implode(', ', array_map(static fn ($c) => "`{$c}` = VALUES(`{$c}`)", $cols)), $data + ['page' => $page]);
    }
}
