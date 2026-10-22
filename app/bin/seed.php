<?php
// Load the first site content into an empty database (docs/plan/04-database.md §6). Runs once, after migrate.
//   php bin/seed.php                 database/seed/seed.json
//   php bin/seed.php --file=FILE
//   php bin/seed.php --only=faqs     one table added after the first seed (phase 3), into a database that has content
// Refuses when content is already there, so it can never overwrite what was edited in the admin.
declare(strict_types=1);

use App\Core\App;
use App\Core\Db;
use App\Repository\SettingRepository;

require __DIR__ . '/../bootstrap.php';

$opt = getopt('', ['file:', 'only:']);
$file = $opt['file'] ?? App::path('database/seed/seed.json');
$seed = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
$db = App::db();
$later = ['faqs'];

if (isset($opt['only'])) {
    $table = (string) $opt['only'];
    if (!in_array($table, $later, true)) {
        fwrite(STDERR, "--only takes one of: " . implode(', ', $later) . "\n");
        exit(1);
    }
    if ((int) $db->value("SELECT COUNT(*) FROM `{$table}`") > 0) {
        fwrite(STDERR, "{$table} already has rows; nothing was changed.\n");
        exit(1);
    }
    $db->transaction(static function (Db $db) use ($seed, $table): void {
        foreach ($seed[$table] ?? [] as $row) {
            $db->insert($table, $row);
        }
    });
    printf("%-18s %d\n", $table, count($seed[$table] ?? []));
    exit(0);
}

if ((int) $db->value('SELECT COUNT(*) FROM divisions') > 0) {
    fwrite(STDERR, "The database already has content; nothing was changed.\n");
    exit(1);
}

// parents before children, because of the foreign keys
$tables = ['media', 'divisions', 'product_groups', 'products', 'suppliers', 'product_suppliers', 'news', ...$later];
$counts = $db->transaction(static function (Db $db) use ($seed, $tables): array {
    $counts = [];
    foreach ($tables as $table) {
        foreach ($seed[$table] ?? [] as $row) {
            $db->insert($table, $row);
        }
        $counts[$table] = count($seed[$table] ?? []);
    }
    // settings the admin may already have changed (contact from 001_core) are kept
    $settings = new SettingRepository($db);
    foreach ($seed['settings'] ?? [] as $key => $value) {
        if ($settings->get($key) === null) {
            $settings->set($key, $value);
        }
    }
    $counts['settings'] = count($seed['settings'] ?? []);
    return $counts;
});

foreach ($counts as $table => $n) {
    printf("%-18s %d\n", $table, $n);
}
echo "Seeded from " . basename($file) . ".\n";
