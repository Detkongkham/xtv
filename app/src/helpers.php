<?php
declare(strict_types=1);

/** Escape text for HTML output. Every value printed in a template goes through this. */
function e(string|int|float|null $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
