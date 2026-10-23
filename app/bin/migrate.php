<?php
// Apply database/migrations/*.sql that are not yet in schema_migrations, in file-name order.
//   php bin/migrate.php            apply pending migrations
//   php bin/migrate.php --status   list applied / pending
// Before changing a database that already has migrations, the database is backed up to
// storage/backups/pre-migrate/ (keeps 5). --no-backup skips that (not on the server).
declare(strict_types=1);

use App\Core\App;
use App\Service\Backup;

require __DIR__ . '/../bootstrap.php';

$db = App::db();
$db->pdo()->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
  version VARCHAR(64) NOT NULL PRIMARY KEY,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$applied = array_flip(array_column($db->all('SELECT version FROM schema_migrations'), 'version'));
$files = glob(App::path('database/migrations/*.sql')) ?: [];
sort($files, SORT_STRING);

if (in_array('--status', $argv, true)) {
    foreach ($files as $file) {
        $v = basename($file, '.sql');
        echo (isset($applied[$v]) ? '[x] ' : '[ ] ') . $v . "\n";
    }
    exit(0);
}

$pending = array_filter($files, static fn (string $f): bool => !isset($applied[basename($f, '.sql')]));
if ($pending !== [] && $applied !== [] && !in_array('--no-backup', $argv, true)) {
    $backup = new Backup(App::config('db'), App::path('storage/backups'), dirname(App::path()) . '/public/uploads',
        ['mysqldump' => (string) App::config('mysqldump', '')]);
    echo 'Backup before migrating: ' . $backup->preMigrate() . "\n";
}

$count = 0;
foreach ($files as $file) {
    $version = basename($file, '.sql');
    if (isset($applied[$version])) {
        continue;
    }
    echo "Applying {$version} ... ";
    foreach (split_sql((string) file_get_contents($file)) as $statement) {
        $db->pdo()->exec($statement);
    }
    $db->run('INSERT INTO schema_migrations (version) VALUES (?)', [$version]);
    echo "done\n";
    $count++;
}
echo $count === 0 ? "Nothing to migrate.\n" : "Applied {$count} migration(s).\n";

/** Split on semicolons at the end of a line, skipping `--` comment lines. Migrations must not contain procedures. */
function split_sql(string $sql): array
{
    $lines = array_filter(explode("\n", $sql), static fn ($l) => !str_starts_with(ltrim($l), '--'));
    $parts = preg_split('/;\s*$/m', implode("\n", $lines)) ?: [];
    return array_values(array_filter(array_map('trim', $parts), static fn ($s) => $s !== ''));
}
