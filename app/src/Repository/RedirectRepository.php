<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

/** Old page names that must keep working after a rename or delete; the Publisher writes a redirect page for each. */
final class RedirectRepository
{
    public function __construct(private Db $db)
    {
    }

    /** $from and $to are page names without .html. Pages that pointed at $from now point at $to as well. */
    public function add(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }
        $this->db->run('INSERT INTO redirects (from_page, to_page) VALUES (?, ?) ON DUPLICATE KEY UPDATE to_page = VALUES(to_page)', [$from, $to]);
        $this->db->run('UPDATE redirects SET to_page = ? WHERE to_page = ?', [$to, $from]);
        // a page name that is in use again is no longer a redirect
        $this->db->run('DELETE FROM redirects WHERE from_page = ?', [$to]);
    }

    public function remove(string $from): void
    {
        $this->db->run('DELETE FROM redirects WHERE from_page = ?', [$from]);
    }
}
