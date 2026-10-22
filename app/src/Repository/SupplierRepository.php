<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Suppliers and their links to products (suppliers, product_suppliers). */
final class SupplierRepository
{
    public const MAX_FEATURED = 6;
    public const PARTNERSHIPS = ['exclusive', 'authorized', 'distributor'];
    public const LANGS = ['lo', 'en', 'th', 'zh'];

    public function __construct(private Db $db)
    {
    }

    /** Every supplier in display order, with its logo and number of linked products. */
    public function all(): array
    {
        return $this->db->all(
            'SELECT s.*, m.path AS logo_path, (SELECT COUNT(*) FROM product_suppliers ps WHERE ps.supplier_id = s.id) AS product_count
             FROM suppliers s LEFT JOIN media m ON m.id = s.logo_id ORDER BY s.sort, s.id'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT s.*, m.path AS logo_path FROM suppliers s LEFT JOIN media m ON m.id = s.logo_id WHERE s.id = ?', [$id]);
    }

    public function slugTaken(string $slug, int $exceptId): bool
    {
        return $this->db->value('SELECT COUNT(*) FROM suppliers WHERE slug = ? AND id <> ?', [$slug, $exceptId]) > 0;
    }

    public function countFeatured(int $exceptId = 0): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM suppliers WHERE is_featured = 1 AND id <> ?', [$exceptId]);
    }

    /** New suppliers go to the end of the list. */
    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'suppliers');
        return $this->db->insert('suppliers', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE suppliers SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    /** Links to products go with it (ON DELETE CASCADE); the products stay. */
    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM suppliers WHERE id = ?', [$id]);
    }

    /** Moves a supplier one place up (-1) or down (+1) in the list. */
    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'suppliers', $id, $dir);
    }

    /** Products this supplier is linked to, in site order, with whether it is the product's primary (first) supplier. */
    public function products(int $supplierId): array
    {
        return $this->db->all(
            'SELECT p.id, p.name, p.slug, p.image_id, p.is_visible, g.name_lo AS group_name, d.name_lo AS division_name,
                    ps.sort = (SELECT MIN(x.sort) FROM product_suppliers x WHERE x.product_id = p.id) AS is_primary,
                    (SELECT COUNT(*) FROM product_suppliers x WHERE x.product_id = p.id) AS supplier_count
             FROM product_suppliers ps JOIN products p ON p.id = ps.product_id
             JOIN product_groups g ON g.id = p.group_id JOIN divisions d ON d.id = g.division_id
             WHERE ps.supplier_id = ? ORDER BY d.sort, g.sort, p.sort, p.id',
            [$supplierId]
        );
    }

    /** Products not linked to this supplier yet, for the "add products" picker. */
    public function unlinkedProducts(int $supplierId): array
    {
        return $this->db->all(
            'SELECT p.id, p.name, p.is_visible, g.name_lo AS group_name, d.name_lo AS division_name, d.id AS division_id
             FROM products p JOIN product_groups g ON g.id = p.group_id JOIN divisions d ON d.id = g.division_id
             WHERE p.id NOT IN (SELECT product_id FROM product_suppliers WHERE supplier_id = ?)
             ORDER BY d.sort, g.sort, p.sort, p.id',
            [$supplierId]
        );
    }

    /** Adds products; for a product that already has suppliers this one comes last (not primary). */
    public function link(int $supplierId, array $productIds): int
    {
        return $this->db->transaction(function (Db $db) use ($supplierId, $productIds): int {
            $n = 0;
            foreach ($productIds as $pid) {
                $n += $db->run(
                    'INSERT IGNORE INTO product_suppliers (product_id, supplier_id, sort)
                     SELECT p.id, ?, COALESCE((SELECT MAX(sort) + 1 FROM product_suppliers WHERE product_id = p.id), 0) FROM products p WHERE p.id = ?',
                    [$supplierId, $pid]
                )->rowCount();
            }
            return $n;
        });
    }

    public function unlink(int $supplierId, int $productId): void
    {
        $this->db->run('DELETE FROM product_suppliers WHERE supplier_id = ? AND product_id = ?', [$supplierId, $productId]);
    }

    /** Makes this supplier the one shown first for the product. */
    public function makePrimary(int $supplierId, int $productId): void
    {
        $this->db->transaction(function (Db $db) use ($supplierId, $productId): void {
            $ids = array_map('intval', array_column($db->all('SELECT supplier_id FROM product_suppliers WHERE product_id = ? ORDER BY sort, supplier_id', [$productId]), 'supplier_id'));
            if (!in_array($supplierId, $ids, true)) {
                return;
            }
            $ids = array_merge([$supplierId], array_diff($ids, [$supplierId]));
            foreach (array_values($ids) as $n => $sid) {
                $db->run('UPDATE product_suppliers SET sort = ? WHERE product_id = ? AND supplier_id = ?', [$n, $productId, $sid]);
            }
        });
    }

    /** Visible products that no supplier is linked to yet (dashboard). */
    public function countProductsWithoutSupplier(): int
    {
        return (int) $this->db->value('SELECT COUNT(*) FROM products p WHERE p.is_visible = 1 AND NOT EXISTS (SELECT 1 FROM product_suppliers ps WHERE ps.product_id = p.id)');
    }
}
