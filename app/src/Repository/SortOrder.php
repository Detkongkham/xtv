<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Moves a row one place up or down in a `sort` column, optionally within a parent (e.g. groups of one division). */
final class SortOrder
{
    /** $table and $scopeColumn come from code, never from input. */
    public static function move(Db $db, string $table, int $id, int $dir, ?string $scopeColumn = null): void
    {
        $db->transaction(static function (Db $db) use ($table, $id, $dir, $scopeColumn): void {
            $where = '';
            $params = [];
            if ($scopeColumn !== null) {
                $where = " WHERE `{$scopeColumn}` = (SELECT x.`{$scopeColumn}` FROM (SELECT `{$scopeColumn}` FROM `{$table}` WHERE id = ?) x)";
                $params[] = $id;
            }
            $ids = array_map('intval', array_column($db->all("SELECT id FROM `{$table}`{$where} ORDER BY sort, id", $params), 'id'));
            $i = array_search($id, $ids, true);
            $j = $i === false ? -1 : $i + $dir;
            if ($j < 0 || $j >= count($ids)) {
                return;
            }
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            foreach ($ids as $n => $rowId) {
                $db->run("UPDATE `{$table}` SET sort = ? WHERE id = ?", [($n + 1) * 10, $rowId]);
            }
        });
    }

    /** Sort value that puts a new row last (within its parent). */
    public static function next(Db $db, string $table, ?string $scopeColumn = null, ?int $scopeValue = null): int
    {
        return $scopeColumn === null
            ? (int) $db->value("SELECT COALESCE(MAX(sort), 0) + 10 FROM `{$table}`")
            : (int) $db->value("SELECT COALESCE(MAX(sort), 0) + 10 FROM `{$table}` WHERE `{$scopeColumn}` = ?", [$scopeValue]);
    }
}
