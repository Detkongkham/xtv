<?php
// Publish the site from the database into public/ (same code as the Publish button in the admin).
//   php bin/publish.php               build, check, swap
//   php bin/publish.php --rollback    put the previous build back (--rollback=ID for an older one)
//   php bin/publish.php --list        builds that can be put back
// Exit code 1 when the publish fails (the live site is then unchanged) or another publish is running.
declare(strict_types=1);

use App\Core\App;
use App\Publish\Publisher;
use App\Publish\PublishBusyException;

require __DIR__ . '/../bootstrap.php';

$opt = getopt('', ['rollback::', 'list']);
$publisher = new Publisher(App::db(), App::path('storage'), dirname(App::path()) . '/public', App::config('base_url'));

if (isset($opt['list'])) {
    foreach ($publisher->builds() as $id) {
        echo $id, "\n";
    }
    exit(0);
}

try {
    $r = array_key_exists('rollback', $opt)
        ? $publisher->rollback(null, is_string($opt['rollback']) ? $opt['rollback'] : null)
        : $publisher->publish(null);
} catch (PublishBusyException|RuntimeException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

if ($r['status'] === 'failed') {
    fwrite(STDERR, "Publish failed (the live site was not changed):\n" . $r['error'] . "\n");
    exit(1);
}
printf("%s: %s pages, %d ms (publish_log #%d)\n", $r['status'], $r['pages'] ?? '-', $r['ms'], $r['id']);
foreach ($r['warnings'] as $w) {
    echo "warning: {$w}\n";
}
