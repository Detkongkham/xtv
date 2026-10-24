<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\DivisionRepository;
use App\Repository\GroupRepository;
use App\Repository\ProductRepository;
use App\Service\Slugger;

/**
 * /admin/groups: product groups inside a division. A group's code is its anchor on the division page
 * (#g_imaging) and is used in links, so it is set once when the group is created.
 */
final class GroupController extends Controller
{
    public const FIELDS = [
        ['name', 'ຊື່ກຸ່ມ', 'input', 120],
        ['summary', 'ຄຳອະທິບາຍ', 'textarea', 400, 'ສະແດງໃຕ້ຊື່ກຸ່ມໃນໜ້າຂະແໜງ ແລະ ໃນສິນຄ້າທີ່ບໍ່ມີຄຳອະທິບາຍຂອງຕົນເອງ.'],
    ];

    private function repo(): GroupRepository
    {
        return new GroupRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $byDivision = [];
        foreach ($this->repo()->all() as $g) {
            $byDivision[(int) $g['division_id']][] = $g;
        }
        $show = Request::query('show');
        $this->page('groups/index', 'ກຸ່ມສິນຄ້າ', [
            'byDivision' => $byDivision,
            'divisions' => (new DivisionRepository(App::db()))->all(),
            'filters' => [
                'show' => in_array($show, ['visible', 'hidden', 'empty'], true) ? $show : '',
                'division' => (int) Request::query('division'),
                'q' => trim(Request::query('q')),
            ],
        ]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'code' => '', 'division_id' => (int) Request::query('division'), 'icon' => 'box', 'is_visible' => 1], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0, 'code' => ''] + $data, $errors, 422);
        }
        $data['code'] = $this->newCode((string) $data['name_en']);
        $id = $this->repo()->create($data);
        $this->contentChanged(['name' => $data['name_lo']], $id);
        $this->back('/admin/groups#g' . $id, 'ok', 'ເພີ່ມກຸ່ມແລ້ວ.');
    }

    public function edit(int $id): never
    {
        $this->requireLogin();
        $this->form($this->found($id), []);
    }

    public function update(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $g = $this->found($id);
        [$data, $errors] = $this->input($g);
        if ($errors !== []) {
            $this->form(['id' => $id, 'code' => $g['code']] + $data, $errors, 422);
        }
        $this->repo()->update($id, $data, (int) $data['division_id'] !== (int) $g['division_id']);
        $this->contentChanged();
        $this->back('/admin/groups#g' . $id, 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $g = $this->found($id);
        if ($this->repo()->countProducts($id) > 0) {
            $this->back('/admin/groups/' . $id, 'error', 'ລຶບບໍ່ໄດ້: ກຸ່ມນີ້ຍັງມີສິນຄ້າ. ຍ້າຍ ຫຼື ລຶບສິນຄ້າກ່ອນ.');
        }
        $this->repo()->delete($id);
        $this->contentChanged(['name' => $g['name_lo']]);
        App::logger()->info('groups: deleted', ['id' => $id, 'code' => $g['code'], 'by' => Auth::id()]);
        $this->back('/admin/groups', 'ok', 'ລຶບ "' . $g['name_lo'] . '" ແລ້ວ.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $g = $this->found($id);
        $this->repo()->update($id, ['is_visible' => (int) !(int) $g['is_visible']], false);
        $this->contentChanged(['visible' => !(int) $g['is_visible']]);
        $this->back('/admin/groups#g' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/groups#g' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    /** "Imaging & hospital equipment" -> g_imaging_hospital_equipment (unique) */
    private function newCode(string $nameEn): string
    {
        $base = 'g_' . substr(str_replace('-', '_', Slugger::slugify($nameEn, 40)) ?: 'group', 0, 26);
        $code = $base;
        for ($n = 2; $this->repo()->codeTaken($code); $n++) {
            $code = $base . '_' . $n;
        }
        return $code;
    }

    private function found(int $id): array
    {
        $g = $this->repo()->find($id);
        if ($g === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $g;
    }

    private function form(array $g, array $errors, int $status = 200): never
    {
        $id = (int) $g['id'];
        // the saved row (not the posted one) says which division the group sits in and where
        $saved = $id ? $this->repo()->find($id) : null;
        $siblings = $saved === null ? [] : array_values(array_filter($this->repo()->all(), static fn (array $s): bool => (int) $s['division_id'] === (int) $saved['division_id']));
        $products = $id ? (new ProductRepository(App::db()))->search(['group' => $id], 1) : ['rows' => [], 'total' => 0];
        $this->page('groups/edit', $id === 0 ? 'ເພີ່ມກຸ່ມສິນຄ້າ' : ($g['name_lo'] ?? 'ກຸ່ມ'), [
            'g' => $g,
            'errors' => $errors,
            'divisions' => (new DivisionRepository(App::db()))->names(),
            'productCount' => $products['total'],
            'products' => $products['rows'],
            'saved' => $saved,
            'siblings' => $siblings,
        ], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(?array $existing): array
    {
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        $data['division_id'] = (int) Request::post('division_id');
        $data['icon'] = Request::post('icon');
        $data['is_visible'] = Request::post('is_visible') === '1' ? 1 : 0;
        if (!isset((new DivisionRepository(App::db()))->names()[$data['division_id']])) {
            $errors['division_id'] = 'ກະລຸນາເລືອກຂະແໜງ.';
        }
        $icons = require App::path('templates/site/partials/icons.php');
        if (!isset($icons[$data['icon']])) {
            $errors['icon'] = 'ກະລຸນາເລືອກໄອຄອນ.';
        }
        return [$data, $errors];
    }
}
