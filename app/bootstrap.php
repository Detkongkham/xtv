<?php
// Loaded first by every entry point: public/api/contact.php, public/admin/index.php and bin/*.php.
declare(strict_types=1);

use App\Core\App;

date_default_timezone_set('Asia/Vientiane');
mb_internal_encoding('UTF-8');

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit("Run `composer install` in app/ first.\n");
}
require $autoload;

$configFile = __DIR__ . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit("Missing app/config/config.php (copy config.example.php).\n");
}
App::boot(require $configFile, __DIR__);

$isCli = PHP_SAPI === 'cli';
error_reporting(E_ALL);
ini_set('display_errors', App::isLocal() || $isCli ? '1' : '0');
ini_set('log_errors', '1');

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false; // silenced with @
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $e) use ($isCli): void {
    App::logger()->error(get_class($e) . ': ' . $e->getMessage(), ['at' => $e->getFile() . ':' . $e->getLine()]);
    if ($isCli) {
        fwrite(STDERR, $e . "\n");
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo '{"ok":false,"error":"server"}';
        return;
    }
    echo App::isLocal()
        ? '<pre>' . e((string) $e) . '</pre>'
        : '<!doctype html><meta charset="utf-8"><title>Error</title><p>ຂໍອະໄພ, ເກີດຂໍ້ຜິດພາດ. ກະລຸນາລອງໃໝ່ພາຍຫຼັງ.</p>';
});
