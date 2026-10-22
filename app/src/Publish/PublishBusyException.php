<?php
declare(strict_types=1);

namespace App\Publish;

/** Someone else is publishing right now. */
final class PublishBusyException extends \RuntimeException
{
}
