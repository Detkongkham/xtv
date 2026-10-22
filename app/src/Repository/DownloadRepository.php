<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Files on the downloads page (downloads): a PDF from the media library, for the company or one division. */
final class DownloadRepository
{
    public function __construct(private Db $db)
    {
    }

    /** In display order, with file and division. */
    public function all(): array
    {
        return $this->db->all(
            'SELECT dl.*, m.original_name, m.size_bytes, m.path, d.name_lo AS division_name
             FROM downloads dl JOIN media m ON m.id = dl.file_id LEFT JOIN divisions d ON d.id = dl.division_id ORDER BY dl.sort, dl.id'
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM downloads WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $data['sort'] = SortOrder::next($this->db, 'downloads');
        return $this->db->insert('downloads', $data);
    }

    public function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`{$c}` = :{$c}", array_keys($data)));
        $this->db->run("UPDATE downloads SET {$set} WHERE id = :id", $data + ['id' => $id]);
    }

    /** The PDF stays in the media library. */
    public function delete(int $id): void
    {
        $this->db->run('DELETE FROM downloads WHERE id = ?', [$id]);
    }

    public function move(int $id, int $dir): void
    {
        SortOrder::move($this->db, 'downloads', $id, $dir);
    }

    /** PDFs in the library for the file select: id => "name (size)". */
    public function pdfOptions(): array
    {
        $out = [];
        foreach ($this->db->all("SELECT id, original_name, size_bytes FROM media WHERE kind = 'pdf' ORDER BY id DESC") as $m) {
            $out[(int) $m['id']] = $m['original_name'] . ' (' . max(1, (int) round($m['size_bytes'] / 1024)) . ' KB)';
        }
        return $out;
    }
}
