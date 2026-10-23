<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Backups in storage/backups/ (docs/plan/07-deployment.md §8):
 *   db-YYYYMMDD-HHMMSS.sql.gz         database, every day, newest 14 kept
 *   full-YYYYMMDD-HHMMSS.tar.gz       database + public/uploads/, every week, newest 4 kept
 *   pre-migrate/db-....sql.gz         before migrate.php changes the database, newest 5 kept
 * A full backup holds db.sql.gz, uploads/ and manifest.json. The database password reaches mysqldump/mysql through
 * a temporary option file, never on the command line.
 */
final class Backup
{
    public const KEEP = ['db' => 14, 'full' => 4, 'pre-migrate' => 5];

    /** temporary MySQL option file with the password, removed in __destruct */
    private ?string $cnf = null;

    /**
     * @param array{host: string, port?: int, name: string, user: string, pass: string} $db
     * @param array<string, string> $tools  optional full paths: mysqldump, mysql, tar
     */
    public function __construct(private array $db, private string $dir, private string $uploadsDir, private array $tools = [])
    {
    }

    /** Daily database backup. @return string the file */
    public function db(): string
    {
        $file = $this->dir . '/db-' . date('Ymd-His') . '.sql.gz';
        $this->dump($file);
        self::prune($this->dir, 'db-*.sql.gz', self::KEEP['db']);
        return $file;
    }

    public function preMigrate(): string
    {
        $dir = $this->dir . '/pre-migrate';
        if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
            throw new \RuntimeException('Cannot create ' . $dir);
        }
        $file = $dir . '/db-' . date('Ymd-His') . '.sql.gz';
        $this->dump($file);
        self::prune($dir, 'db-*.sql.gz', self::KEEP['pre-migrate']);
        return $file;
    }

    /** Weekly backup of the database and every uploaded file. */
    public function full(): string
    {
        $file = $this->dir . '/full-' . date('Ymd-His') . '.tar.gz';
        $tmp = $this->tempDir();
        try {
            $this->dump($tmp . '/db.sql.gz');
            $files = 0;
            $bytes = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->uploadsDir, \FilesystemIterator::SKIP_DOTS)) as $f) {
                $files++;
                $bytes += $f->getSize();
            }
            file_put_contents($tmp . '/manifest.json', json_encode([
                'created' => date('c'), 'database' => $this->db['name'], 'upload_files' => $files, 'upload_bytes' => $bytes,
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
            // uploads/ is taken from its parent, so it unpacks as uploads/
            self::run(sprintf('%s -czf %s -C %s db.sql.gz manifest.json -C %s %s', escapeshellarg($this->tool('tar')), escapeshellarg($file),
                escapeshellarg($tmp), escapeshellarg(dirname($this->uploadsDir)), escapeshellarg(basename($this->uploadsDir))), $file);
            chmod($file, 0600);
        } finally {
            self::removeDir($tmp);
        }
        self::prune($this->dir, 'full-*.tar.gz', self::KEEP['full']);
        return $file;
    }

    /**
     * Loads a backup into database $target (which must exist). A full backup also unpacks its uploads/ into
     * $uploadsTarget, which must be empty or not exist yet. The live database and uploads need $force.
     * @return array{db: string, uploads: ?string, manifest: ?array}
     */
    public function restore(string $file, string $target, ?string $uploadsTarget = null, bool $force = false): array
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException("Backup not found: {$file}");
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $target)) {
            throw new \InvalidArgumentException('Give the target database with --db=NAME (letters, digits, _).');
        }
        if ($target === $this->db['name'] && !$force) {
            throw new \InvalidArgumentException("Refusing to overwrite the live database {$target}. Add --force if you really mean it.");
        }
        if (!str_ends_with($file, '.tar.gz')) {
            $this->load($file, $target);
            return ['db' => $target, 'uploads' => null, 'manifest' => null];
        }

        if ($uploadsTarget === null) {
            throw new \InvalidArgumentException('A full backup also needs --uploads=DIR for the files (an empty or new folder).');
        }
        $uploadsTarget = rtrim($uploadsTarget, '/');
        if (realpath($uploadsTarget) === realpath($this->uploadsDir) && !$force) {
            throw new \InvalidArgumentException('Refusing to overwrite the live uploads folder. Add --force if you really mean it.');
        }
        if (is_dir($uploadsTarget) && (new \FilesystemIterator($uploadsTarget))->valid() && realpath($uploadsTarget) !== realpath($this->uploadsDir)) {
            throw new \InvalidArgumentException("{$uploadsTarget} is not empty.");
        }
        $tmp = $this->tempDir();
        try {
            self::run(sprintf('%s -xzf %s -C %s', escapeshellarg($this->tool('tar')), escapeshellarg($file), escapeshellarg($tmp)));
            if (!is_file($tmp . '/db.sql.gz') || !is_dir($tmp . '/uploads')) {
                throw new \RuntimeException("{$file} is not a full backup (no db.sql.gz or uploads/).");
            }
            $manifest = is_file($tmp . '/manifest.json') ? json_decode((string) file_get_contents($tmp . '/manifest.json'), true) : null;
            $this->load($tmp . '/db.sql.gz', $target);
            if (!is_dir($uploadsTarget) && !mkdir($uploadsTarget, 0755, true)) {
                throw new \RuntimeException('Cannot create ' . $uploadsTarget);
            }
            // cp -R dir/. copies the contents, dot files (.htaccess) included
            self::run(sprintf('cp -R %s %s', escapeshellarg($tmp . '/uploads/.'), escapeshellarg($uploadsTarget)));
        } finally {
            self::removeDir($tmp);
        }
        return ['db' => $target, 'uploads' => $uploadsTarget, 'manifest' => $manifest];
    }

    /**
     * Newest backup of each kind, for the dashboard.
     * @return array{db: ?array{file: string, time: int, bytes: int}, full: ?array{file: string, time: int, bytes: int}}
     */
    public static function latest(string $dir): array
    {
        $out = [];
        foreach (['db' => 'db-*.sql.gz', 'full' => 'full-*.tar.gz'] as $kind => $pattern) {
            $files = glob($dir . '/' . $pattern) ?: [];
            rsort($files, SORT_STRING);
            $out[$kind] = $files === [] ? null : ['file' => basename($files[0]), 'time' => (int) filemtime($files[0]), 'bytes' => (int) filesize($files[0])];
        }
        return $out;
    }

    /** Keeps the newest $keep files matching $pattern (names sort by date). @return list<string> removed */
    public static function prune(string $dir, string $pattern, int $keep): array
    {
        $files = glob($dir . '/' . $pattern) ?: [];
        rsort($files, SORT_STRING);
        $old = array_slice($files, $keep);
        array_map('unlink', $old);
        return $old;
    }

    private function dump(string $file): void
    {
        $cmd = sprintf('set -o pipefail; %s --defaults-extra-file=%s --single-transaction --quick --no-tablespaces --default-character-set=utf8mb4 --set-gtid-purged=OFF %s | gzip -9 > %s',
            escapeshellarg($this->tool('mysqldump')), escapeshellarg($this->optionFile()), escapeshellarg($this->db['name']), escapeshellarg($file));
        self::run('bash -c ' . escapeshellarg($cmd), $file);
        chmod($file, 0600);
    }

    private function load(string $gz, string $target): void
    {
        $cmd = sprintf('set -o pipefail; gzip -dc %s | %s --defaults-extra-file=%s %s',
            escapeshellarg($gz), escapeshellarg($this->tool('mysql')), escapeshellarg($this->optionFile()), escapeshellarg($target));
        self::run('bash -c ' . escapeshellarg($cmd));
    }

    private function optionFile(): string
    {
        if ($this->cnf === null) {
            $this->cnf = (string) tempnam(sys_get_temp_dir(), 'xtkdb');
            chmod($this->cnf, 0600);
            file_put_contents($this->cnf, sprintf("[client]\nhost=%s\nport=%d\nuser=%s\npassword=\"%s\"\n", $this->db['host'],
                (int) ($this->db['port'] ?? 3306), $this->db['user'], addcslashes((string) $this->db['pass'], '"\\')));
        }
        return $this->cnf;
    }

    public function __destruct()
    {
        if ($this->cnf !== null) {
            @unlink($this->cnf);
        }
    }

    private function tool(string $name): string
    {
        if (($this->tools[$name] ?? '') !== '') {
            return $this->tools[$name];
        }
        $found = trim((string) shell_exec('command -v ' . escapeshellarg($name)));
        return $found !== '' ? $found : throw new \RuntimeException("{$name} not found. Set '{$name}' in config.php.");
    }

    private function tempDir(): string
    {
        $tmp = $this->dir . '/.tmp-' . bin2hex(random_bytes(4));
        if (!mkdir($tmp, 0700, true)) {
            throw new \RuntimeException('Cannot create ' . $tmp);
        }
        return $tmp;
    }

    private static function run(string $cmd, ?string $cleanupOnFail = null): void
    {
        exec($cmd . ' 2>&1', $out, $code);
        if ($code !== 0) {
            if ($cleanupOnFail !== null) {
                @unlink($cleanupOnFail);
            }
            throw new \RuntimeException("Command failed ({$code}):\n" . implode("\n", $out));
        }
    }

    private static function removeDir(string $dir): void
    {
        if (is_dir($dir)) {
            exec('rm -rf ' . escapeshellarg($dir));
        }
    }
}
