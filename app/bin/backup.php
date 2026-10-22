<?php
// Database backup (cron 03:30 on the server, phase S). Keeps the newest 14 files.
//   php bin/backup.php                                  dump to storage/backups/db-YYYYMMDD-HHMMSS.sql.gz
//   php bin/backup.php --restore=FILE --db=NAME         load a backup into database NAME (must already exist)
// The password goes to mysqldump/mysql through a temporary option file, never on the command line.
declare(strict_types=1);

use App\Core\App;

require __DIR__ . '/../bootstrap.php';

const KEEP = 14;

$c = App::config('db');
$opts = getopt('', ['restore:', 'db:']);
$dir = App::path('storage/backups');

$cnf = tempnam(sys_get_temp_dir(), 'xtkdb');
chmod($cnf, 0600);
file_put_contents($cnf, sprintf("[client]\nhost=%s\nport=%d\nuser=%s\npassword=\"%s\"\n", $c['host'], (int) ($c['port'] ?? 3306), $c['user'], addcslashes((string) $c['pass'], '"\\')));
register_shutdown_function(static fn () => @unlink($cnf));

if (isset($opts['restore'])) {
    $file = (string) $opts['restore'];
    $target = (string) ($opts['db'] ?? '');
    if (!is_file($file)) {
        fail("Backup not found: {$file}");
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $target)) {
        fail('Give the target database with --db=NAME (letters, digits, _).');
    }
    if ($target === $c['name'] && !in_array('--force', $argv, true)) {
        fail("Refusing to overwrite the live database {$target}. Add --force if you really mean it.");
    }
    $mysql = tool('mysql');
    $cmd = sprintf('set -o pipefail; gzip -dc %s | %s --defaults-extra-file=%s %s', escapeshellarg($file), escapeshellarg($mysql), escapeshellarg($cnf), escapeshellarg($target));
    run('bash -c ' . escapeshellarg($cmd));
    echo "Restored {$file} into {$target}\n";
    exit(0);
}

$file = $dir . '/db-' . date('Ymd-His') . '.sql.gz';
$dump = tool('mysqldump');
$cmd = sprintf(
    'set -o pipefail; %s --defaults-extra-file=%s --single-transaction --quick --no-tablespaces --default-character-set=utf8mb4 --set-gtid-purged=OFF %s | gzip -9 > %s',
    escapeshellarg($dump), escapeshellarg($cnf), escapeshellarg($c['name']), escapeshellarg($file)
);
run('bash -c ' . escapeshellarg($cmd), $file);
chmod($file, 0600);

$all = glob($dir . '/db-*.sql.gz') ?: [];
rsort($all, SORT_STRING);
foreach (array_slice($all, KEEP) as $old) {
    unlink($old);
}

$size = round(filesize($file) / 1024, 1);
App::logger()->info('backup', ['file' => basename($file), 'kb' => $size]);
echo "Backup written: {$file} ({$size} KB)\n";

function tool(string $name): string
{
    $configured = (string) App::config($name, '');
    if ($configured !== '') {
        return $configured;
    }
    $found = trim((string) shell_exec('command -v ' . escapeshellarg($name)));
    if ($found === '') {
        fail("{$name} not found. Set '{$name}' in config.php.");
    }
    return $found;
}

function run(string $cmd, ?string $cleanupOnFail = null): void
{
    exec($cmd . ' 2>&1', $out, $code);
    if ($code !== 0) {
        if ($cleanupOnFail !== null) {
            @unlink($cleanupOnFail);
        }
        fail("Command failed ({$code}):\n" . implode("\n", $out));
    }
}

function fail(string $msg): never
{
    App::logger()->error('backup: ' . strtok($msg, "\n"));
    fwrite(STDERR, $msg . "\n");
    exit(1);
}
