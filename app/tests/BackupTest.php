<?php
declare(strict_types=1);

namespace App\Tests;

use App\Service\Backup;
use PHPUnit\Framework\TestCase;

/** File handling of backups (no database: dump and restore are checked by hand, see 06-phases P3-D7). */
final class BackupTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/xtk-backup-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->dir));
    }

    public function testPruneKeepsTheNewest(): void
    {
        foreach (['20261001-033000', '20261002-033000', '20261003-033000', '20261004-033000'] as $stamp) {
            touch($this->dir . "/db-{$stamp}.sql.gz");
        }
        touch($this->dir . '/full-20261001-040000.tar.gz');
        $removed = Backup::prune($this->dir, 'db-*.sql.gz', 2);
        self::assertSame([$this->dir . '/db-20261002-033000.sql.gz', $this->dir . '/db-20261001-033000.sql.gz'], $removed);
        self::assertFileExists($this->dir . '/db-20261004-033000.sql.gz');
        self::assertFileExists($this->dir . '/full-20261001-040000.tar.gz');   // other kind untouched
    }

    public function testLatestOfEachKind(): void
    {
        self::assertSame(['db' => null, 'full' => null], Backup::latest($this->dir));
        file_put_contents($this->dir . '/db-20261001-033000.sql.gz', 'a');
        file_put_contents($this->dir . '/db-20261002-033000.sql.gz', 'abc');
        $latest = Backup::latest($this->dir);
        self::assertSame('db-20261002-033000.sql.gz', $latest['db']['file']);
        self::assertSame(3, $latest['db']['bytes']);
        self::assertNull($latest['full']);
    }

    public function testRestoreRefusesTheLiveDatabaseAndAFullBackupWithoutAFolder(): void
    {
        $b = new Backup(['host' => 'x', 'name' => 'live_db', 'user' => 'u', 'pass' => ''], $this->dir, $this->dir . '/uploads');
        touch($this->dir . '/db-20261001-033000.sql.gz');
        touch($this->dir . '/full-20261001-040000.tar.gz');
        foreach ([
            [$this->dir . '/db-20261001-033000.sql.gz', 'live_db', null, 'live database'],
            [$this->dir . '/db-20261001-033000.sql.gz', 'bad-name', null, '--db=NAME'],
            [$this->dir . '/full-20261001-040000.tar.gz', 'test_db', null, '--uploads=DIR'],
            [$this->dir . '/nope.sql.gz', 'test_db', null, 'not found'],
        ] as [$file, $db, $uploads, $msg]) {
            try {
                $b->restore($file, $db, $uploads);
                self::fail('restore should refuse: ' . $msg);
            } catch (\InvalidArgumentException $e) {
                self::assertStringContainsString($msg, $e->getMessage());
            }
        }
    }
}
