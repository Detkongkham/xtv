<?php
declare(strict_types=1);

namespace App\Core;

/** Plain-PHP templates from app/templates. Variables are extracted into the template scope. */
final class View
{
    public static function render(string $template, array $vars = []): string
    {
        $file = App::path('templates/' . $template . '.php');
        if (!is_file($file)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
