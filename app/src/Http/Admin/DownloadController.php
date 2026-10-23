<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\DivisionRepository;
use App\Repository\DownloadRepository;

/** /admin/downloads (docs/plan/03-backend.md §2.6): PDFs on the downloads page. Editors and admins. */
final class DownloadController extends Controller
{
    public const FIELDS = [
        ['title', 'ຊື່ເອກະສານ', 'input', 200],
        ['summary', 'ຄຳອະທິບາຍສັ້ນ', 'input', 300, 'ແຖວນ້ອຍໃຕ້ຊື່. ຫວ່າງ = ຊື່ຂະແໜງ ຫຼື "XTK".', true],
    ];

    private function repo(): DownloadRepository
    {
        return new DownloadRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('downloads/index', 'ໄຟລ໌ດາວໂຫຼດ', ['rows' => $this->repo()->all()]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'division_id' => null, 'file_id' => null, 'is_visible' => 1], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        if (Request::tooLarge()) {
            $this->back('/admin/downloads/new', 'error', 'ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ 20 MB).');
        }
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $errors, 422);
        }
        $this->contentChanged(['name' => $data['title_lo']], $this->repo()->create($data));
        $this->back('/admin/downloads', 'ok', 'ເພີ່ມແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
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
            $this->back('/admin/downloads/' . $id, 'error', 'ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ 20 MB).');
        }
        $this->checkCsrf();
        $dl = $this->found($id);
        [$data, $errors] = $this->input($dl);
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $errors, 422);
        }
        $this->repo()->update($id, $data);
        $this->contentChanged();
        $this->back('/admin/downloads', 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $dl = $this->found($id);
        $this->repo()->delete($id);
        $this->contentChanged(['name' => $dl['title_lo']]);
        App::logger()->info('downloads: deleted', ['id' => $id, 'by' => Auth::id()]);
        $this->back('/admin/downloads', 'ok', 'ລຶບ "' . $dl['title_lo'] . '" ແລ້ວ. ໄຟລ໌ PDF ຍັງຢູ່ໃນຄັງສື່.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $dl = $this->found($id);
        $this->repo()->update($id, ['is_visible' => (int) !(int) $dl['is_visible']]);
        $this->contentChanged(['visible' => !(int) $dl['is_visible']]);
        $this->back('/admin/downloads#dl' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/downloads#dl' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    private function found(int $id): array
    {
        $dl = $this->repo()->find($id);
        if ($dl === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $dl;
    }

    private function form(array $dl, array $errors, int $status = 200): never
    {
        $this->page('downloads/edit', (int) $dl['id'] === 0 ? 'ເພີ່ມໄຟລ໌ດາວໂຫຼດ' : ($dl['title_lo'] ?? 'ໄຟລ໌'), [
            'dl' => $dl, 'errors' => $errors,
            'divisions' => (new DivisionRepository(App::db()))->names(),
            'pdfs' => $this->repo()->pdfOptions(),
        ], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(?array $existing): array
    {
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        $division = (int) Request::post('division_id');
        $data['division_id'] = $division > 0 ? $division : null;
        $data['is_visible'] = Request::post('is_visible') === '1' ? 1 : 0;
        $data['file_id'] = (int) Request::post('file_id') ?: ($existing['file_id'] ?? null);
        if ($division > 0 && !isset((new DivisionRepository(App::db()))->names()[$division])) {
            $errors['division_id'] = 'ກະລຸນາເລືອກຈາກລາຍການ.';
        }
        $file = Request::file('file_upload');
        if ($file !== null) {
            try {
                $data['file_id'] = MediaController::service()->storePdf($file, Auth::id());
            } catch (\InvalidArgumentException $e) {
                $errors['file_id'] = $e->getMessage();
            }
        } elseif ($data['file_id'] === null || !isset($this->repo()->pdfOptions()[(int) $data['file_id']])) {
            $errors['file_id'] = 'ກະລຸນາອັບໄຟລ໌ PDF ຫຼື ເລືອກຈາກຄັງສື່.';
        }
        return [$data, $errors];
    }
}
