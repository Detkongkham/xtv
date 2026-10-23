<?php
// Backups (docs/plan/07-deployment.md §8; cron in phase S: db every day 03:30, full every Sunday 04:00).
//   php bin/backup.php [db]                                       database → storage/backups/db-YYYYMMDD-HHMMSS.sql.gz (keeps 14)
//   php bin/backup.php full                                       database + public/uploads/ → full-YYYYMMDD-HHMMSS.tar.gz (keeps 4)
//   php bin/backup.php --list                                     backups on disk
//   php bin/backup.php --restore=FILE --db=NAME                   load a .sql.gz into database NAME (must already exist)
//   php bin/backup.php --restore=FULL.tar.gz --db=NAME --uploads=DIR   also unpack the files into DIR (empty or new)
// The live database or uploads folder is only overwritten with --force.
declare(strict_types=1);

use App\Core\App;
use App\Service\Backup;

require __DIR__ . '/../bootstrap.php';

$opts = getopt('', ['restore:', 'db:', 'uploads:', 'force', 'list'], $rest);
$mode = $argv[$rest] ?? 'db';
$dir = App::path('storage/backups');
$backup = new Backup(App::config('db'), $dir, dirname(App::path()) . '/public/uploads',
    ['mysqldump' => (string) App::config('mysqldump', ''), 'mysql' => (string) App::config('mysql', ''), 'tar' => (string) App::config('tar', '')]);

try {
    if (isset($opts['list'])) {
        foreach (['db-*.sql.gz', 'full-*.tar.gz', 'pre-migrate/db-*.sql.gz'] as $pattern) {
            foreach (glob($dir . '/' . $pattern) ?: [] as $f) {
                printf("%-44s %8.1f KB  %s\n", substr($f, strlen($dir) + 1), filesize($f) / 1024, date('Y-m-d H:i', (int) filemtime($f)));
            }
        }
        exit(0);
    }
    if (isset($opts['restore'])) {
        $r = $backup->restore((string) $opts['restore'], (string) ($opts['db'] ?? ''), $opts['uploads'] ?? null, isset($opts['force']));
        echo "Restored {$opts['restore']} into database {$r['db']}" . ($r['uploads'] !== null ? " and {$r['uploads']}/" : '') . "\n";
        if ($r['manifest'] !== null) {
            echo "Backup of {$r['manifest']['created']}: {$r['manifest']['upload_files']} upload files\n";
        }
        App::logger()->info('backup: restored', ['file' => basename((string) $opts['restore']), 'db' => $r['db'], 'uploads' => $r['uploads']]);
        exit(0);
    }
    $file = match ($mode) {
        'db' => $backup->db(),
        'full' => $backup->full(),
        default => throw new InvalidArgumentException("Unknown mode {$mode}: use db or full."),
    };
} catch (Throwable $e) {
    App::logger()->error('backup: ' . strtok($e->getMessage(), "\n"));
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
$size = round(filesize($file) / 1024, 1);
App::logger()->info('backup', ['file' => basename($file), 'kb' => $size]);
echo "Backup written: {$file} ({$size} KB)\n";
