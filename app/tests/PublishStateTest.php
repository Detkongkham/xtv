<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Repository\PublishLogRepository as Log;
use PHPUnit\Framework\TestCase;

/** Dashboard "unpublished changes" badge (PublishLogRepository::pendingReason). */
final class PublishStateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
    }

    public function testNeverPublished(): void
    {
        self::assertSame('never', Log::pendingReason(null, null));
        self::assertSame('never', Log::pendingReason(null, '2026-10-22T10:00:00'));
    }

    public function testUpToDate(): void
    {
        $live = ['status' => 'success', 'started_at' => '2026-10-22 10:00:05'];
        self::assertNull(Log::pendingReason($live, null));
        self::assertNull(Log::pendingReason($live, '2026-10-22T10:00:04'));
    }

    public function testEditAfterPublish(): void
    {
        $live = ['status' => 'success', 'started_at' => '2026-10-22 10:00:05'];
        self::assertSame('changed', Log::pendingReason($live, '2026-10-22T10:30:00'));
        // Same second: the publish may have read the data before the edit.
        self::assertSame('changed', Log::pendingReason($live, '2026-10-22T10:00:05'));
    }

    public function testRollbackIsAlwaysPending(): void
    {
        $live = ['status' => 'rolled_back', 'started_at' => '2026-10-22 10:00:05'];
        self::assertSame('rolled_back', Log::pendingReason($live, '2026-10-22T09:00:00'));
    }
}
