<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Request;
use App\Repository\ActivityRepository;

/** /admin/activity (docs/plan/03-backend.md §2.13): who did what, when and from which IP. Admins only. */
final class ActivityController extends Controller
{
    public function index(): never
    {
        $this->requireAdmin();
        $repo = new ActivityRepository(App::db());
        $filters = ['user' => Request::query('user'), 'entity' => Request::query('entity')];
        $total = $repo->count($filters);
        $pages = max(1, (int) ceil($total / ActivityRepository::PER_PAGE));
        $page = min($pages, max(1, (int) Request::query('page', '1')));
        $this->page('activity/index', 'ປະຫວັດການແກ້ໄຂ', [
            'rows' => $repo->page($filters, $page),
            'filters' => $filters,
            'users' => $repo->users(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ]);
    }
}
