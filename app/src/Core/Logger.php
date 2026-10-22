<?php
declare(strict_types=1);

namespace App\Core;

/** One file per day: storage/logs/app-YYYY-MM-DD.log. Old files are removed by bin/cleanup.php. */
final class Logger
{
    public function __construct(private string $dir)
    {
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $line = sprintf('[%s] %s %s', date('Y-m-d H:i:s'), $level, $message);
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        $file = $this->dir . '/app-' . date('Y-m-d') . '.log';
        if (@file_put_contents($file, $line . "\n", FILE_APPEND | LOCK_EX) === false) {
            error_log($line);
        }
    }
}
