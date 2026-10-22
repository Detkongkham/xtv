<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\CsvExporter;

final class MessageController extends Controller
{
    public const STATUS_NAMES = ['new' => 'ໃໝ່', 'in_progress' => 'ກຳລັງດຳເນີນການ', 'done' => 'ສຳເລັດ', 'spam' => 'Spam'];
    public const SUBJECT_NAMES = ['general' => 'ສອບຖາມທົ່ວໄປ', 'sales' => 'ຝ່າຍຂາຍ', 'service' => 'ບໍລິການຫຼັງການຂາຍ', 'other' => 'ອື່ນໆ'];
    public const LANG_NAMES = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];

    private function repo(): MessageRepository
    {
        return new MessageRepository(App::db());
    }

    /** Filters from the query string, kept as plain strings for the form and for links. */
    private function filters(): array
    {
        return [
            'status' => Request::query('status'),
            'subject' => Request::query('subject'),
            'from' => Request::query('from'),
            'to' => Request::query('to'),
            'q' => mb_substr(trim(Request::query('q')), 0, 100),
        ];
    }

    public function index(): never
    {
        $this->requireLogin();
        $filters = $this->filters();
        $page = max(1, (int) Request::query('page', '1'));
        $result = $this->repo()->search($filters, $page);
        $this->page('messages/index', 'ກ່ອງຂໍ້ຄວາມ', [
            'filters' => $filters,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / MessageRepository::PER_PAGE)),
            'query' => array_filter($filters, static fn ($v) => $v !== ''),
        ]);
    }

    public function show(int $id): never
    {
        $this->requireLogin();
        $m = $this->repo()->find($id);
        if ($m === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        $this->page('messages/show', 'ຂໍ້ຄວາມ #' . $id, [
            'm' => $m,
            'notes' => $this->repo()->notes($id),
            'users' => (new UserRepository(App::db()))->active(),
        ]);
    }

    public function update(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $repo = $this->repo();
        if ($repo->find($id) === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        $status = Request::post('status');
        if (!in_array($status, MessageRepository::STATUSES, true)) {
            $this->back('/admin/messages/' . $id, 'error', 'ສະຖານະບໍ່ຖືກຕ້ອງ.');
        }
        $assigned = (int) Request::post('assigned_to');
        $validIds = array_map('intval', array_column((new UserRepository(App::db()))->active(), 'id'));
        $repo->update($id, $status, in_array($assigned, $validIds, true) ? $assigned : null);
        $this->back('/admin/messages/' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function addNote(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $repo = $this->repo();
        $m = $repo->find($id);
        if ($m === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        $note = Validator::clean(Request::post('note'), true);
        if ($note === '' || mb_strlen($note) > 5000) {
            $this->back('/admin/messages/' . $id, 'error', 'ບັນທຶກຕ້ອງມີ 1–5000 ຕົວອັກສອນ.');
        }
        $repo->addNote($id, Auth::id(), $note);
        // first note on a new message: it is now being handled
        if ($m['status'] === 'new') {
            $repo->update($id, 'in_progress', $m['assigned_to'] === null ? Auth::id() : (int) $m['assigned_to']);
        }
        $this->back('/admin/messages/' . $id . '#notes', 'ok', 'ເພີ່ມບັນທຶກແລ້ວ.');
    }

    public function export(): never
    {
        $user = $this->requireLogin();
        $filters = $this->filters();
        $rows = $this->repo()->search($filters, 1, null)['rows'];
        App::logger()->info('messages: CSV export', ['user' => (int) $user['id'], 'filters' => array_filter($filters), 'rows' => count($rows)]);
        CsvExporter::download(
            'xtk-messages-' . date('Ymd-His') . '.csv',
            ['ID', 'ວັນທີ', 'ຊື່', 'ອົງກອນ', 'ເບີໂທ', 'ອີເມວ', 'ຫົວຂໍ້', 'ພາສາ', 'ສະຖານະ', 'ຜູ້ຮັບຜິດຊອບ', 'ຂໍ້ຄວາມ'],
            (static function () use ($rows) {
                foreach ($rows as $r) {
                    yield [
                        $r['id'], $r['created_at'], $r['name'], $r['org'], $r['phone'], $r['email'],
                        self::SUBJECT_NAMES[$r['subject']] ?? $r['subject'], self::LANG_NAMES[$r['lang']] ?? $r['lang'],
                        self::STATUS_NAMES[$r['status']] ?? $r['status'], $r['assigned_name'], $r['body'],
                    ];
                }
            })()
        );
    }
}
