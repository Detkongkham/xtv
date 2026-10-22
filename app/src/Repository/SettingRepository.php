<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Key/value settings stored as JSON (docs/plan/04-database.md §5). */
final class SettingRepository
{
    public function __construct(private Db $db)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $raw = $this->db->value('SELECT value FROM settings WHERE `key` = ?', [$key]);
        if ($raw === null) {
            return $default;
        }
        return json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(string $key, mixed $value): void
    {
        $this->db->run(
            'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [$key, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)]
        );
    }
}
