<?php
declare(strict_types=1);

namespace App\Tests;

use App\Http\Admin\Controller;
use App\Repository\ActivityRepository;
use PHPUnit\Framework\TestCase;

/** What contentChanged() writes to activity_log for each admin POST URL. */
final class ActivityPathTest extends TestCase
{
    public function testActionsFromTheUrl(): void
    {
        self::assertSame(['create', 'products', null, []], Controller::activityOf('/admin/products/new'));
        self::assertSame(['update', 'products', 12, []], Controller::activityOf('/admin/products/12'));
        self::assertSame(['toggle', 'faqs', 3, []], Controller::activityOf('/admin/faqs/3/toggle'));
        self::assertSame(['delete', 'news', 7, []], Controller::activityOf('/admin/news/7/delete'));
        self::assertSame(['link', 'suppliers', 2, []], Controller::activityOf('/admin/suppliers/2/products'));
        self::assertSame(['unlink', 'suppliers', 2, []], Controller::activityOf('/admin/suppliers/2/products/remove'));
        self::assertSame(['update', 'seo', null, ['page' => 'about']], Controller::activityOf('/admin/seo/about'));
        self::assertSame(['update', 'settings', null, []], Controller::activityOf('/admin/settings'));
    }

    public function testEveryPostRouteHasALabel(): void
    {
        preg_match_all("#->post\\('(/admin/[^']+)'#", (string) file_get_contents(dirname(__DIR__, 2) . '/public/admin/index.php'), $m);
        foreach ($m[1] as $route) {
            [$action, $entity] = Controller::activityOf(str_replace(['{id}', '{page}'], ['1', 'about'], $route));
            if (in_array($entity, ['login', 'logout', 'account', 'mode', 'translate'], true)) {
                continue; // logged by hand (login, password) or not at all (logout, light/dark choice, translate saves nothing)
            }
            self::assertArrayHasKey($action, ActivityRepository::ACTIONS, $route);
            self::assertArrayHasKey($entity, ActivityRepository::ENTITIES, $route);
        }
    }
}
