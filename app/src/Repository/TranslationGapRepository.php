<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/**
 * Items shown on the site whose Thai or Chinese text is missing although Lao or English is filled.
 * The site falls back to English for them (Lang::tr), so this is a to-do list, not an error.
 */
final class TranslationGapRepository
{
    public const LANGS = ['th' => 'ໄທ', 'zh' => 'ຈີນ'];

    /** table => [type label, admin path, name column, where, [column prefix => field label]] */
    private const SOURCES = [
        'divisions' => ['ຂະແໜງ', 'divisions', 'name_lo', 'is_visible = 1', ['name' => 'ຊື່', 'summary' => 'ຄຳອະທິບາຍສັ້ນ', 'body' => 'ຄຳອະທິບາຍຍາວ']],
        'product_groups' => ['ກຸ່ມສິນຄ້າ', 'groups', 'name_lo', 'is_visible = 1', ['name' => 'ຊື່', 'summary' => 'ຄຳອະທິບາຍ']],
        'products' => ['ສິນຄ້າ', 'products', 'name', 'is_visible = 1', ['summary' => 'ຄຳອະທິບາຍສັ້ນ', 'body' => 'ລາຍລະອຽດ', 'seo_title' => 'ຫົວຂໍ້ SEO', 'seo_desc' => 'ຄຳອະທິບາຍ SEO']],
        'suppliers' => ['ຜູ້ສະໜອງ', 'suppliers', 'name', 'is_visible = 1', ['summary' => 'ຄຳອະທິບາຍສັ້ນ', 'about' => 'ກ່ຽວກັບ']],
        'news' => ['ຂ່າວ', 'news', 'title_lo', "status = 'published'", ['title' => 'ຫົວຂໍ້', 'summary' => 'ສະຫຼຸບ', 'body' => 'ເນື້ອຫາ']],
        'faqs' => ['FAQ', 'faqs', 'question_lo', 'is_visible = 1', ['question' => 'ຄຳຖາມ', 'answer' => 'ຄຳຕອບ']],
        'downloads' => ['ໄຟລ໌ດາວໂຫຼດ', 'downloads', 'title_lo', 'is_visible = 1', ['title' => 'ຊື່', 'summary' => 'ຄຳອະທິບາຍສັ້ນ']],
    ];

    public function __construct(private Db $db)
    {
    }

    /**
     * @return list<array{type: string, name: string, url: string, missing: array<string, list<string>>}>
     *         missing: lang => field labels, only languages with something missing
     */
    public function all(): array
    {
        $out = [];
        foreach (self::SOURCES as $table => [$type, $path, $nameCol, $where, $fields]) {
            $specs = $table === 'products' ? $this->specGaps() : [];
            foreach ($this->db->all("SELECT * FROM {$table} WHERE {$where} ORDER BY id") as $row) {
                $missing = [];
                foreach (self::LANGS as $l => $_) {
                    foreach ($fields as $col => $label) {
                        if (self::gap($row, $col, $l)) {
                            $missing[$l][] = $label;
                        }
                    }
                    if (isset($specs[(int) $row['id']][$l])) {
                        $missing[$l][] = 'ຂໍ້ມູນຈຳເພາະ';
                    }
                }
                if ($missing !== []) {
                    $out[] = ['type' => $type, 'name' => (string) $row[$nameCol], 'url' => '/admin/' . $path . '/' . $row['id'], 'missing' => $missing];
                }
            }
        }
        return $out;
    }

    /** @return array<int, array<string, true>> product id => lang => true */
    private function specGaps(): array
    {
        $out = [];
        foreach ($this->db->all('SELECT * FROM product_specs ORDER BY product_id, sort') as $s) {
            foreach (self::LANGS as $l => $_) {
                if (self::gap($s, 'label', $l) || self::gap($s, 'value', $l)) {
                    $out[(int) $s['product_id']][$l] = true;
                }
            }
        }
        return $out;
    }

    private static function gap(array $row, string $col, string $lang): bool
    {
        $filled = static fn (string $c): bool => trim((string) ($row[$c] ?? '')) !== '';
        return ($filled($col . '_lo') || $filled($col . '_en')) && !$filled($col . '_' . $lang);
    }
}
