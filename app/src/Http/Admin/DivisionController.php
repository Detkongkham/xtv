<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Publish\PageList;
use App\Repository\DivisionRepository;
use App\Repository\MediaRepository;
use App\Repository\RedirectRepository;
use App\Service\Slugger;

/** /admin/divisions: the business divisions, each with its own page. Editors and admins. */
final class DivisionController extends Controller
{
    public const FIELDS = [
        ['name', 'ຊື່', 'input', 120],
        ['summary', 'ຄຳອະທິບາຍສັ້ນ', 'textarea', 300, 'ສະແດງໃນກາດຂະແໜງ ແລະ ຫົວໜ້າຂອງໜ້າຂະແໜງ.'],
        ['body', 'ຄຳອະທິບາຍຍາວ', 'textarea', 2000, 'ສະແດງໃນໜ້າລວມຜະລິດຕະພັນ ແລະ ພາກ "ກ່ຽວກັບຂະແໜງ".'],
    ];

    private function repo(): DivisionRepository
    {
        return new DivisionRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('divisions/index', 'ຂະແໜງທຸລະກິດ', ['rows' => $this->repo()->all()]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'slug' => '', 'icon' => 'box', 'image_id' => null, 'is_visible' => 1], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $errors, 422);
        }
        $id = $this->repo()->create($data);
        $this->contentChanged(['name' => $data['name_lo']], $id);
        $this->back('/admin/divisions/' . $id, 'ok', 'ເພີ່ມຂະແໜງແລ້ວ. ຕໍ່ໄປ: ເພີ່ມກຸ່ມສິນຄ້າໃຫ້ຂະແໜງນີ້.');
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
        $d = $this->found($id);
        [$data, $errors] = $this->input($d);
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $errors, 422);
        }
        if ($data['slug'] !== $d['slug'] && self::isPublished($d['slug'])) {
            (new RedirectRepository(App::db()))->add($d['slug'], $data['slug']);
        }
        $this->repo()->update($id, $data);
        $this->contentChanged();
        $this->back('/admin/divisions/' . $id, 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $d = $this->found($id);
        if ($this->repo()->countGroups($id) > 0) {
            $this->back('/admin/divisions/' . $id, 'error', 'ລຶບບໍ່ໄດ້: ຂະແໜງນີ້ຍັງມີກຸ່ມສິນຄ້າ. ຍ້າຍ ຫຼື ລຶບກຸ່ມກ່ອນ.');
        }
        $this->repo()->delete($id);
        if (self::isPublished($d['slug'])) {
            (new RedirectRepository(App::db()))->add($d['slug'], 'products');
        }
        $this->contentChanged(['name' => $d['name_lo']]);
        App::logger()->info('divisions: deleted', ['id' => $id, 'slug' => $d['slug'], 'by' => Auth::id()]);
        $this->back('/admin/divisions', 'ok', 'ລຶບ "' . $d['name_lo'] . '" ແລ້ວ.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $d = $this->found($id);
        $this->repo()->update($id, ['is_visible' => (int) !(int) $d['is_visible']]);
        $this->contentChanged(['visible' => !(int) $d['is_visible']]);
        $this->back('/admin/divisions#d' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/divisions#d' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    private function found(int $id): array
    {
        $d = $this->repo()->find($id);
        if ($d === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $d;
    }

    private function form(array $d, array $errors, int $status = 200): never
    {
        $media = new MediaRepository(App::db());
        $this->page('divisions/edit', (int) $d['id'] === 0 ? 'ເພີ່ມຂະແໜງ' : ($d['name_lo'] ?? 'ຂະແໜງ'), [
            'd' => $d,
            'errors' => $errors,
            'image' => $d['image_id'] ? $media->find((int) $d['image_id']) : null,
            'images' => $media->images(),
            'published' => (int) $d['id'] !== 0 && self::isPublished($d['slug']),
        ], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(?array $existing): array
    {
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        $data['slug'] = strtolower(trim(Request::post('slug'))) ?: Slugger::slugify((string) $data['name_en'], 64);
        $data['icon'] = Request::post('icon');
        $data['is_visible'] = Request::post('is_visible') === '1' ? 1 : 0;
        [$data['image_id'], $imageError] = $this->pickedMedia('image_id', isset($existing['image_id']) ? (int) $existing['image_id'] : null);

        if (!Slugger::isValid($data['slug'], 64)) {
            $errors['slug'] = 'ໃຊ້ໄດ້ສະເພາະ a-z, 0-9 ແລະ ຂີດ (-), ເຊັ່ນ healthcare.';
        } elseif (PageList::isReserved($data['slug'])) {
            $errors['slug'] = 'ຊື່ນີ້ໃຊ້ກັບໜ້າອື່ນຂອງເວັບແລ້ວ.';
        } elseif ($this->repo()->slugTaken($data['slug'], (int) ($existing['id'] ?? 0))) {
            $errors['slug'] = 'ມີຂະແໜງອື່ນໃຊ້ຊື່ນີ້ແລ້ວ.';
        }
        $icons = require App::path('templates/site/partials/icons.php');
        if (!isset($icons[$data['icon']])) {
            $errors['icon'] = 'ກະລຸນາເລືອກໄອຄອນ.';
        }
        if ($imageError !== null) {
            $errors['image_id'] = $imageError;
        }
        return [$data, $errors];
    }
}
