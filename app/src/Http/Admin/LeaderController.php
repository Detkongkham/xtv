<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\LeaderRepository;
use App\Repository\MediaRepository;

/** /admin/leaders: owners and directors with their photo and life story on the about page. Editors and admins. */
final class LeaderController extends Controller
{
    public const FIELDS = [
        ['name', 'ຊື່ ແລະ ນາມສະກຸນ', 'input', 160, 'ໃສ່ຄຳນຳໜ້າໄດ້ ເຊັ່ນ ທ່ານ, ດຣ.'],
        ['position', 'ຕຳແໜ່ງ', 'input', 160, 'ເຊັ່ນ ປະທານບໍລິສັດ, ຜູ້ອຳນວຍການ.'],
        ['bio', 'ປະຫວັດ', 'textarea', 5000, 'ຂໍ້ຄວາມທຳມະດາ. ແຖວຫວ່າງ = ຂຶ້ນຫຍໍ້ໜ້າໃໝ່. ແຖວທີ່ຂຶ້ນຕົ້ນດ້ວຍ "- " ເປັນລາຍການ (ເຊັ່ນ ການສຶກສາ, ປະສົບການ).'],
    ];

    private function repo(): LeaderRepository
    {
        return new LeaderRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('leaders/index', 'ຜູ້ບໍລິຫານ', ['rows' => $this->repo()->all()]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'photo_id' => null, 'is_visible' => 1], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        if (Request::tooLarge()) {
            $this->back('/admin/leaders/new', 'error', 'ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ 20 MB).');
        }
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $errors, 422);
        }
        $this->contentChanged(['name' => $data['name_lo']], $this->repo()->create($data));
        $this->back('/admin/leaders', 'ok', 'ເພີ່ມແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function edit(int $id): never
    {
        $this->requireLogin();
        $this->form($this->found($id), []);
    }

    public function update(int $id): never
    {
        $this->requireLogin();
        if (Request::tooLarge()) {
            $this->back('/admin/leaders/' . $id, 'error', 'ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ 20 MB).');
        }
        $this->checkCsrf();
        $leader = $this->found($id);
        [$data, $errors] = $this->input($leader);
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $errors, 422);
        }
        $this->repo()->update($id, $data);
        $this->contentChanged();
        $this->back('/admin/leaders', 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $leader = $this->found($id);
        $this->repo()->delete($id);
        $this->contentChanged(['name' => $leader['name_lo']]);
        App::logger()->info('leaders: deleted', ['id' => $id, 'by' => Auth::id()]);
        $this->back('/admin/leaders', 'ok', 'ລຶບ "' . $leader['name_lo'] . '" ແລ້ວ. ຮູບຍັງຢູ່ໃນຄັງສື່.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $leader = $this->found($id);
        $this->repo()->update($id, ['is_visible' => (int) !(int) $leader['is_visible']]);
        $this->contentChanged(['visible' => !(int) $leader['is_visible']]);
        $this->back('/admin/leaders#leader' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/leaders#leader' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    private function found(int $id): array
    {
        $leader = $this->repo()->find($id);
        if ($leader === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $leader;
    }

    private function form(array $leader, array $errors, int $status = 200): never
    {
        $media = new MediaRepository(App::db());
        $this->page('leaders/edit', (int) $leader['id'] === 0 ? 'ເພີ່ມຜູ້ບໍລິຫານ' : 'ແກ້ໄຂຜູ້ບໍລິຫານ', [
            'leader' => $leader, 'errors' => $errors,
            'photo' => $leader['photo_id'] === null ? null : $media->find((int) $leader['photo_id']),
            'images' => $media->images(),
        ], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(?array $existing): array
    {
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        [$data['photo_id'], $err] = $this->pickedMedia('photo_id', $existing === null || $existing['photo_id'] === null ? null : (int) $existing['photo_id']);
        if ($err !== null) {
            $errors['photo_id'] = $err;
        }
        $data['is_visible'] = Request::post('is_visible') === '1' ? 1 : 0;
        return [$data, $errors];
    }
}
