<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Request;
use App\Repository\DashboardRepository;
use App\Repository\MessageRepository;
use App\Repository\PublishLogRepository;
use App\Repository\SupplierRepository;
use App\Repository\TranslationGapRepository;
use App\Service\Backup;

final class DashboardController extends Controller
{
    public const PERIODS = [7, 14, 30];

    public function index(): never
    {
        $user = $this->requireLogin();
        $days = (int) Request::query('days', '14');
        $days = in_array($days, self::PERIODS, true) ? $days : 14;
        $messages = new MessageRepository(App::db());
        $dash = new DashboardRepository(App::db());
        // this period ends tomorrow 00:00; the previous one is the same number of days before it
        $to = date('Y-m-d', strtotime('+1 day')) . ' 00:00:00';
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' day')) . ' 00:00:00';
        $prev = date('Y-m-d', strtotime('-' . (2 * $days - 1) . ' day')) . ' 00:00:00';
        $this->page('dashboard/index', 'ໜ້າຫຼັກ', [
            'days' => $days,
            'counts' => $messages->countByStatus(),
            'latest' => $messages->latestNew(5),
            'mailErrors' => $messages->countMailErrors(),
            'withoutSupplier' => (new SupplierRepository(App::db()))->countProductsWithoutSupplier(),
            'publish' => self::publishState(),
            'history' => (new PublishLogRepository(App::db()))->latest(3),
            'gaps' => (new TranslationGapRepository(App::db()))->all(),
            // admins only: when the last backups were made (files in storage/backups/)
            'backups' => $user['role'] === 'admin' ? Backup::latest(App::path('storage/backups')) : null,
            // both periods, oldest first: the template splits it in half
            'daily' => $dash->daily(2 * $days),
            'period' => $dash->messages($from, $to),
            'before' => $dash->messages($prev, $from),
            'publishes' => $dash->publishes($from, $to),
            'oldestNew' => $dash->oldestNew(),
            'content' => $dash->content(),
        ]);
    }
}
