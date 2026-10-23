<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Products with their specification rows and suppliers (products, product_specs, product_suppliers). */
final class ProductRepository
{
    public const MAX_FEATURED = 8;
    public const PER_PAGE = 50;
    public const LANGS = ['lo', 'en', 'th', 'zh'];

    public function __construct(private Db $db)
    {
    }

    /**
     * @param array{q?: string, division?: int, group?: int, show?: string} $f show: hidden | noimage | nosupplier | featured
     * @return array{rows: array, total: int}
     */
    public function search(array $f, int $page): array
    {
        $w = [];
        $p = [];
        if (!empty($f['q'])) {
            $w[] = 'p.name LIKE ?';
            $p[] = '%' . addcslashes($f['q'], '%_\\') . '%';
        }
        if (!empty($f['group'])) {
            $w[] = 'p.group_id = ?';
            $p[] = (int) $f['group'];
        } elseif (!empty($f['division'])) {
            $w[] = 'g.division_id = ?';
            $p[] = (int) $f['division'];
        }
        $w[] = match ($f['show'] ?? '') {
            'hidden' => 'p.is_visible = 0',
            'noimage' => 'p.image_id IS NULL',
            'nosupplier' => 'NOT EXISTS (SELECT 1 FROM product_suppliers ps WHERE ps.product_id = p.id)',
            'featured' => 'p.is_featured = 1',
            default => '1 = 1',
        };
        $where = 'WHERE ' . implode(' AND ', $w);
        $from = 'FROM products p JOIN product_groups g ON g.id = p.group_id JOIN divisions d ON d.id = g.division_id';
        $total = (int) $this->db->value("SELECT COUNT(*) {$from} {$where}", $p);
        $order = ($f['show'] ?? '') === 'featured' ? 'p.featured_sort, p.id' : 'd.sort, d.id, g.sort, g.id, p.sort, p.id';
        $rows = $this->db->all(
            "SELECT p.id, p.name, p.slug, p.group_id, p.image_id, p.is_visible, p.is_featured, g.name_lo AS group_name, d.name_lo AS division_name,
                    m.path AS image_path, m.variants AS image_variants,
                    (SELECT GROUP_CONCAT(s.name ORDER BY ps.sort SEPARATOR ', ') FROM product_suppliers ps JOIN suppliers s ON s.id = ps.supplier_id WHERE ps.product_id = p.id) AS supplier_names
             {$from} LEFT JOIN media m ON m.id = p.image_id {$where} ORDER BY {$order}
             LIMIT " . self::PER_PAGE . ' OFFSET ' . max(0, ($page - 1) * self::PER_PAGE),
            $p
        );
        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->one(
            'SELECT p.*, g.division_id, d.slug AS division_slug FROM products p JOIN product_groups g ON g.id = p.group_id JOIN divisions d ON d.id = g.division_id WHERE p.id = ?',
            [$id]
        );
    }

    public function slugTaken(string $slug, int $exceptId): bool
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }

    /** "ct-scan" -> "ct-scan-2", "ct-scan-3", ... until free */
    public function freeSlug(string $slug): string
    {
        $base = substr($slug, 0, 110);
        for ($n = 2, $s = $base; $this->slugTaken($s, 0); $n++) {
            $s = $base . '-' . $n;
        }
        return $s;
    }

    public function countFeatured(int $exceptId = 0): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM products WHERE is_featured = 1 AND id <> ?', [$exceptId]);
    }

    /** Saves the product, its specification rows and its suppliers together. */
    public function save(?int $id, array $data, array $specs, array $supplierIds): int
    {
        return $this->db->transaction(function (Db $db) use ($id, $data, $specs, $supplierIds): int {
            if ($id === null) {
                $data['sort'] = SortOrder::next($db, 'products', 'group_id', (int) $data['group_id']);
                $data['featured_sort'] = $data['is_featured'] ? $this->nextFeaturedSort() : 0;
                $id = $db->insert('products', $data);
            } else {
                $old = $db->one('SELECT group_id, is_featured FROM products WHERE id = ?', [$id]);
                if ((int) $old['group_id'] !== (int) $data['group_id']) {
                    $data['sort'] = SortOrder::next($db, 'products', 'group_id', (int) $data['group_id']); // last in the new group
                }
                if ($data['is_featured'] && !(int) $old['is_featured']) {
                    $data['featured_sort'] = $this->nextFeaturedSort();
                }
                $this->update($id, $data);
            }
            $db->run('DELETE FROM product_specs WHERE product_id = ?', [$id]);
            foreach (array_values($specs) as $n => $row) {
                $db->insert('product_specs', ['product_id' => $id, 'sort' => ($n + 1) * 10] + $row);
            }
            $db->run('DELETE FROM product_suppliers WHERE product_id = ?', [$id]);
            foreach (array_values($supplierIds) as $n => $sid) {
                $db->insert('product_suppliers', ['product_id' => $id, 'supplier_id' => $sid, 'sort' => $n]);
            }
            return $id;
        });
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE products SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    public function setFeatured(int $id, bool $on): void
    {
        $this->update($id, ['is_featured' => (int) $on, 'featured_sort' => $on ? $this->nextFeaturedSort() : 0]);
    }

    /** Specification rows in order. */
    public function specs(int $id): array
    {
        return $this->db->all('SELECT * FROM product_specs WHERE product_id = ? ORDER BY sort, id', [$id]);
    }

    /** Supplier ids of the product, primary first. */
    public function supplierIds(int $id): array
    {
        return array_map('intval', array_column($this->db->all('SELECT supplier_id FROM product_suppliers WHERE product_id = ? ORDER BY sort, supplier_id', [$id]), 'supplier_id'));
    }

    /** A hidden copy with "(copy)" in the name: same group, picture, texts, specs and suppliers. */
    public function copy(int $id): int
    {
        $p = $this->db->one('SELECT * FROM products WHERE id = ?', [$id]);
        unset($p['id'], $p['created_at'], $p['updated_at']);
        $p['name'] = mb_substr($p['name'] . ' (copy)', 0, 160);
        $p['slug'] = $this->freeSlug($p['slug'] . '-copy');
        $p['is_visible'] = 0;
        $p['is_featured'] = 0;
        $p['featured_sort'] = 0;
        $specs = array_map(static function ($s) {
            unset($s['id'], $s['product_id'], $s['sort']);
            return $s;
        }, $this->specs($id));
        return $this->save(null, $p, $specs, $this->supplierIds($id));
    }

    /** Specs and supplier links go with it (ON DELETE CASCADE). */
    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM products WHERE id = ?', [$id]);
    }

    /** Within its group. */
    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'products', $id, $dir, 'group_id');
    }

    /** Groups for selects: [id, "Division › Group"] in site order. */
    public function groupOptions(): array
    {
        return array_column($this->db->all(
            "SELECT g.id, CONCAT(d.name_lo, ' › ', g.name_lo) AS label FROM product_groups g JOIN divisions d ON d.id = g.division_id ORDER BY d.sort, d.id, g.sort, g.id"
        ), 'label', 'id');
    }

    /** @return array{all:int, hidden:int, featured:int, noimage:int, nosupplier:int} counts for the list's stat cards and tabs */
    public function counts(): array
    {
        $r = $this->db->one(
            'SELECT COUNT(*) AS `all`, SUM(is_visible = 0) AS hidden, SUM(is_featured = 1) AS featured, SUM(image_id IS NULL) AS noimage,
                    SUM(NOT EXISTS (SELECT 1 FROM product_suppliers ps WHERE ps.product_id = p.id)) AS nosupplier
             FROM products p'
        ) ?? [];
        return array_map('intval', array_merge(['all' => 0, 'hidden' => 0, 'featured' => 0, 'noimage' => 0, 'nosupplier' => 0], array_filter($r, 'is_numeric')));
    }

    /** @return list<array{id:int|string, division_id:int|string, name_lo:string, n:int|string}> groups in site order with their product count */
    public function groupCounts(): array
    {
        return $this->db->all(
            'SELECT g.id, g.division_id, g.name_lo, COUNT(p.id) AS n FROM product_groups g JOIN divisions d ON d.id = g.division_id
             LEFT JOIN products p ON p.group_id = g.id GROUP BY g.id ORDER BY d.sort, d.id, g.sort, g.id'
        );
    }

    /** Suppliers for the product form: id => name, in list order. */
    public function supplierOptions(): array
    {
        return array_column($this->db->all('SELECT id, name FROM suppliers ORDER BY sort, id'), 'name', 'id');
    }

    private function nextFeaturedSort(): int
    {
        return (int) $this->db->value('SELECT COALESCE(MAX(featured_sort), 0) + 10 FROM products WHERE is_featured = 1');
    }
}
