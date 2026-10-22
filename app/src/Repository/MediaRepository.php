<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** The media library (media) and where each file is used. */
final class MediaRepository
{
    public const PER_PAGE = 48;
    public const LANGS = ['lo', 'en', 'th', 'zh'];

    /** column that points at media => [table, label column, admin link prefix, what it is] */
    private const USES = [
        ['divisions', 'image_id', 'name_lo', '/admin/divisions/', 'ຮູບຂະແໜງ'],
        ['products', 'image_id', 'name', '/admin/products/', 'ຮູບສິນຄ້າ'],
        ['news', 'cover_id', 'title_lo', '/admin/news/', 'ຮູບປົກຂ່າວ'],
        ['suppliers', 'logo_id', 'name', '/admin/suppliers/', 'ໂລໂກ້ຜູ້ສະໜອງ'],
        ['suppliers', 'certificate_id', 'name', '/admin/suppliers/', 'ໃບຢັ້ງຢືນຜູ້ສະໜອງ'],
        ['downloads', 'file_id', 'title_lo', '/admin/downloads/', 'ໄຟລ໌ດາວໂຫຼດ'],
        ['hero_slides', 'media_id', 'title_lo', '/admin/hero/', 'Hero'],
        ['hero_slides', 'poster_id', 'title_lo', '/admin/hero/', 'Poster ຂອງ Hero'],
    ];

    public function __construct(private Db $db)
    {
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM media WHERE id = ?', [$id]);
    }

    /** @return array{rows: array, total: int} newest first */
    public function search(string $kind, string $q, int $page): array
    {
        [$where, $params] = $this->where($kind, $q);
        $total = (int) $this->db->value("SELECT COUNT(*) FROM media {$where}", $params);
        $rows = $this->db->all("SELECT * FROM media {$where} ORDER BY id DESC LIMIT " . self::PER_PAGE . ' OFFSET ' . max(0, ($page - 1) * self::PER_PAGE), $params);
        $counts = $this->usageCounts(array_map('intval', array_column($rows, 'id')));
        foreach ($rows as &$r) {
            $r['used'] = $counts[(int) $r['id']] ?? 0;
        }
        return ['rows' => $rows, 'total' => $total];
    }

    /** Images for a picker, newest first. */
    public function images(): array
    {
        return $this->db->all("SELECT id, path, variants, original_name, width, height FROM media WHERE kind = 'image' ORDER BY id DESC");
    }

    /** @return list<array{label: string, href: string, what: string}> */
    public function usage(int $id): array
    {
        $out = [];
        foreach (self::USES as [$table, $col, $label, $href, $what]) {
            foreach ($this->db->all("SELECT id, `{$label}` AS label FROM `{$table}` WHERE `{$col}` = ?", [$id]) as $r) {
                $out[] = ['label' => (string) $r['label'], 'href' => $href . $r['id'], 'what' => $what];
            }
        }
        return $out;
    }

    /** @return array<int, int> media id => number of places it is used */
    public function usageCounts(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $in = implode(',', array_map('intval', $ids));
        $parts = array_map(static fn ($u) => "SELECT `{$u[1]}` AS mid FROM `{$u[0]}` WHERE `{$u[1]}` IN ({$in})", self::USES);
        $out = [];
        foreach ($this->db->all('SELECT mid, COUNT(*) AS n FROM (' . implode(' UNION ALL ', $parts) . ') u GROUP BY mid') as $r) {
            $out[(int) $r['mid']] = (int) $r['n'];
        }
        return $out;
    }

    public function updateAlt(int $id, array $alt): void
    {
        $data = [];
        foreach (self::LANGS as $l) {
            $data['alt_' . $l] = ($alt[$l] ?? '') === '' ? null : mb_substr($alt[$l], 0, 255);
        }
        $this->db->run('UPDATE media SET alt_lo = :alt_lo, alt_en = :alt_en, alt_th = :alt_th, alt_zh = :alt_zh WHERE id = :id', $data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM media WHERE id = ?', [$id]);
    }

    /** Videos for the hero picker, newest first. */
    public function videos(): array
    {
        return $this->db->all("SELECT id, path, original_name, size_bytes FROM media WHERE kind = 'video' ORDER BY id DESC");
    }

    /** Files that came with the old site and have no sizes yet (bin/import-media.php). */
    public function legacyImages(): array
    {
        return $this->db->all("SELECT * FROM media WHERE kind = 'image' AND variants IS NULL AND path LIKE 'assets/img/%' ORDER BY id");
    }

    private function where(string $kind, string $q): array
    {
        $w = [];
        $p = [];
        if (in_array($kind, ['image', 'pdf', 'video'], true)) {
            $w[] = 'kind = ?';
            $p[] = $kind;
        }
        if ($q !== '') {
            $w[] = '(original_name LIKE ? OR alt_lo LIKE ? OR alt_en LIKE ?)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            array_push($p, $like, $like, $like);
        }
        return [$w ? 'WHERE ' . implode(' AND ', $w) : '', $p];
    }
}
