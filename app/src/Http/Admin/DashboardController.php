<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Repository\MessageRepository;
use App\Repository\PublishLogRepository;
use App\Repository\SupplierRepository;
use App\Repository\TranslationGapRepository;

final class DashboardController extends Controller
{
    public function index(): never
    {
        $this->requireLogin();
        $messages = new MessageRepository(App::db());
        $this->page('dashboard/index', 'ໜ້າຫຼັກ', [
            'counts' => $messages->countByStatus(),
            'week' => $messages->countSince('7 DAY'),
            'month' => $messages->countSince('30 DAY'),
            'latest' => $messages->latestNew(5),
            'mailErrors' => $messages->countMailErrors(),
            'withoutSupplier' => (new SupplierRepository(App::db()))->countProductsWithoutSupplier(),
            'publish' => self::publishState(),
            'history' => (new PublishLogRepository(App::db()))->latest(3),
            'gaps' => (new TranslationGapRepository(App::db()))->all(),
        ]);
    }
}
