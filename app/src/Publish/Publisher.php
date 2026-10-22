<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\Db;
use App\Repository\PublishLogRepository;

/**
 * Publishes the site (docs/plan/03-backend.md §4): lock → build into storage/build/<id>/ → check → swap into
 * public/ → keep the previous builds for rollback. A failed build or check never touches the live site.
 *
 * The live pages are the BUILD_ITEMS in public/. A swap moves them into a build folder of their own and moves the
 * new ones in, so the previous site is always a complete folder that a rollback can swap back.
 */
final class Publisher
{
    /** previous builds kept for rollback */
    public const KEEP = 3;
    private const ID = '/^\d{8}-\d{6}-[0-9a-f]{4}$/';

    private PublishLogRepository $log;
    private string $buildRoot;
    private string $lockFile;

    public function __construct(
        private Db $db,
        private string $storageDir,
        private string $publicDir,
        private ?string $baseUrl = null,
    ) {
        $this->log = new PublishLogRepository($db);
        $this->buildRoot = $storageDir . '/build';
        $this->lockFile = $storageDir . '/publish.lock';
    }

    /**
     * @return array{id: int, status: string, pages: ?int, ms: int, error: ?string, warnings: list<string>}
     * @throws PublishBusyException
     */
    public function publish(?int $userId): array
    {
        return $this->locked(function () use ($userId): array {
            $start = hrtime(true);
            $id = $this->log->start($userId);
            $buildId = self::newId();
            $dir = $this->buildRoot . '/' . $buildId;
            try {
                $builder = new SiteBuilder(SiteData::fromDb($this->db), $this->publicDir, '../assets', false, $this->baseUrl);
                $pages = $builder->build($dir);
                $this->check($dir, $builder->slugs(), $pages);
                $warnings = $this->warnings($dir);
                $this->swap($dir, $this->buildRoot . '/' . ($this->log->liveBuild() ?? self::newId()));
                rmdir($dir); // empty now: its pages are in public/
                $ms = self::ms($start);
                $this->log->finish($id, 'success', $pages, $ms, $buildId, null, $warnings ? implode("\n", $warnings) : null);
                $this->prune($buildId);
                return ['id' => $id, 'status' => 'success', 'pages' => $pages, 'ms' => $ms, 'error' => null, 'warnings' => $warnings];
            } catch (\Throwable $e) {
                self::remove($dir);
                $ms = self::ms($start);
                $this->log->finish($id, 'failed', null, $ms, null, $e->getMessage());
                return ['id' => $id, 'status' => 'failed', 'pages' => null, 'ms' => $ms, 'error' => $e->getMessage(), 'warnings' => []];
            }
        });
    }

    /**
     * Puts a previous build back online (default: the newest one). The current pages are kept as a build too.
     * @throws PublishBusyException
     */
    public function rollback(?int $userId, ?string $buildId = null): array
    {
        return $this->locked(function () use ($userId, $buildId): array {
            $start = hrtime(true);
            $buildId ??= $this->builds()[0] ?? null;
            if ($buildId === null || !preg_match(self::ID, $buildId) || !is_file($this->buildRoot . '/' . $buildId . '/lo/index.html')) {
                throw new \RuntimeException('ບໍ່ມີຊຸດເກົ່າໃຫ້ກັບຄືນ.');
            }
            $id = $this->log->start($userId);
            $this->swap($this->buildRoot . '/' . $buildId, $this->buildRoot . '/' . ($this->log->liveBuild() ?? self::newId()));
            rmdir($this->buildRoot . '/' . $buildId);
            $ms = self::ms($start);
            $this->log->finish($id, 'rolled_back', null, $ms, $buildId);
            $this->prune($buildId);
            return ['id' => $id, 'status' => 'rolled_back', 'pages' => null, 'ms' => $ms, 'error' => null, 'warnings' => []];
        });
    }

    /** @return list<string> previous builds that can be put back, newest first */
    public function builds(): array
    {
        $live = $this->log->liveBuild();
        $ids = array_filter(array_map('basename', glob($this->buildRoot . '/*', GLOB_ONLYDIR) ?: []),
            static fn ($d) => preg_match(self::ID, $d) && $d !== $live);
        rsort($ids);
        return array_values($ids);
    }

    public function isBusy(): bool
    {
        $h = @fopen($this->lockFile, 'c');
        if ($h === false) {
            return false;
        }
        $free = flock($h, LOCK_EX | LOCK_NB);
        if ($free) {
            flock($h, LOCK_UN);
        }
        fclose($h);
        return !$free;
    }

    private function locked(callable $fn): array
    {
        $h = fopen($this->lockFile, 'c');
        if ($h === false || !flock($h, LOCK_EX | LOCK_NB)) {
            throw new PublishBusyException('ມີຄົນກຳລັງເຜີຍແຜ່ຢູ່. ກະລຸນາລໍຖ້າຈັກໜ້ອຍ ແລ້ວລອງໃໝ່.');
        }
        try {
            $this->log->failStale();
            return $fn();
        } finally {
            flock($h, LOCK_UN);
            fclose($h);
        }
    }

    /** Every expected page is there and complete, and no internal link or image is broken. */
    private function check(string $dir, array $slugs, int $pages): void
    {
        if ($pages !== count($slugs) * 4 || $pages === 0) {
            throw new \RuntimeException("ຈຳນວນໜ້າບໍ່ຖືກ ({$pages}).");
        }
        foreach (['lo', 'en', 'th', 'zh'] as $lang) {
            foreach ($slugs as $slug) {
                $file = "{$dir}/{$lang}/{$slug}.html";
                if (!is_file($file) || !str_ends_with(rtrim((string) file_get_contents($file)), '</html>')) {
                    throw new \RuntimeException("ໜ້າ {$lang}/{$slug}.html ບໍ່ຄົບ.");
                }
            }
        }
        $broken = (new LinkChecker($dir, $this->publicDir))->broken(10);
        if ($broken) {
            throw new \RuntimeException("ພົບລິ້ງເສຍ:\n" . implode("\n", $broken));
        }
    }

    /** @return list<string> */
    private function warnings(string $dir): array
    {
        $missing = FontCheck::missing($dir, $this->publicDir . '/assets/fonts/noto-sans-sc-sub.txt');
        return $missing ? ['ຕົວອັກສອນຈີນ ' . count($missing) . ' ຕົວບໍ່ມີໃນຟອນ ແລະ ຈະສະແດງດ້ວຍຟອນຂອງເຄື່ອງ: ' . implode(' ', array_slice($missing, 0, 40))
            . '. ແຈ້ງນັກພັດທະນາໃຫ້ສ້າງຟອນຈີນໃໝ່.'] : [];
    }

    /** Moves the live items into $saveTo, then the items of $from into public/. Undone if any move fails. */
    private function swap(string $from, string $saveTo): void
    {
        if (!is_dir($saveTo) && !mkdir($saveTo, 0755, true)) {
            throw new \RuntimeException('Cannot create ' . $saveTo);
        }
        $done = [];
        try {
            foreach (LinkChecker::BUILD_ITEMS as $item) {
                $live = $this->publicDir . '/' . $item;
                if (file_exists($live)) {
                    self::move($live, $saveTo . '/' . $item);
                    $done[] = [$live, $saveTo . '/' . $item];
                }
                if (file_exists($from . '/' . $item)) {
                    self::move($from . '/' . $item, $live);
                    $done[] = [$from . '/' . $item, $live];
                }
            }
        } catch (\Throwable $e) {
            foreach (array_reverse($done) as [$a, $b]) {
                @rename($b, $a);
            }
            throw $e;
        }
    }

    /** Deletes old builds beyond KEEP, and leftovers of builds that never finished. */
    private function prune(string $current): void
    {
        foreach (array_slice($this->builds(), self::KEEP) as $old) {
            if ($old !== $current) {
                self::remove($this->buildRoot . '/' . $old);
            }
        }
    }

    private static function move(string $from, string $to): void
    {
        if (!rename($from, $to)) {
            throw new \RuntimeException("Cannot move {$from} to {$to}");
        }
    }

    private static function remove(string $path): void
    {
        if (is_dir($path) && !is_link($path)) {
            foreach (scandir($path) ?: [] as $f) {
                if ($f !== '.' && $f !== '..') {
                    self::remove($path . '/' . $f);
                }
            }
            rmdir($path);
        } elseif (file_exists($path) || is_link($path)) {
            unlink($path);
        }
    }

    private static function newId(): string
    {
        return date('Ymd-His') . '-' . bin2hex(random_bytes(2));
    }

    private static function ms(int $start): int
    {
        return (int) ((hrtime(true) - $start) / 1_000_000);
    }
}
