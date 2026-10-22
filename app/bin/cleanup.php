<?php
// Daily housekeeping (cron 03:00 on the server, phase S). Safe to run by hand at any time.
//   rate_limits older than 1 day, login_attempts older than 30 days,
//   spam messages older than 30 days, log files older than 30 days.
declare(strict_types=1);

use App\Core\App;
use App\Repository\MessageRepository;

require __DIR__ . '/../bootstrap.php';

$db = App::db();
$done = [
    'rate_limits' => $db->run('DELETE FROM rate_limits WHERE created_at < NOW() - INTERVAL 1 DAY')->rowCount(),
    'login_attempts' => $db->run('DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL 30 DAY')->rowCount(),
    'spam_messages' => (new MessageRepository($db))->purgeSpam(30),
    'log_files' => 0,
];

$cutoff = time() - 30 * 86400;
foreach (glob(App::path('storage/logs/app-*.log')) ?: [] as $file) {
    if (filemtime($file) < $cutoff && unlink($file)) {
        $done['log_files']++;
    }
}

App::logger()->info('cleanup', $done);
foreach ($done as $what => $n) {
    echo str_pad($what, 16) . $n . "\n";
}
