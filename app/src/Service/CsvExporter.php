<?php
declare(strict_types=1);

namespace App\Service;

/** Streams a CSV download. Starts with a UTF-8 BOM so Excel shows Lao, Thai and Chinese correctly. */
final class CsvExporter
{
    /**
     * @param string[] $header
     * @param iterable<array> $rows
     */
    public static function download(string $filename, array $header, iterable $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');
        $out = fopen('php://output', 'wb');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $header, ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'safe'], $row), ',', '"', '');
        }
        fclose($out);
        exit;
    }

    /** Stop spreadsheet formula injection: a cell starting with = + - @ is prefixed with a quote. */
    public static function safe(mixed $value): string
    {
        $s = (string) $value;
        return $s !== '' && strpbrk($s[0], "=+-@\t\r") !== false ? "'" . $s : $s;
    }
}
