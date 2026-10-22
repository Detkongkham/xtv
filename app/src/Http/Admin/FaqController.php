<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\FaqRepository;

/** /admin/faqs (docs/plan/03-backend.md §2.10): questions on the FAQ page. Editors and admins. */
final class FaqController extends Controller
{
    public const FIELDS = [
        ['question', 'ຄຳຖາມ', 'input', 300],
        ['answer', 'ຄຳຕອບ', 'textarea', 2000, 'ຂໍ້ຄວາມທຳມະດາ. {tel} {mobile} {email} {address} ຈະປ່ຽນເປັນຂໍ້ມູນຕິດຕໍ່ຈາກໜ້າການຕັ້ງຄ່າ.'],
    ];

    private function repo(): FaqRepository
    {
        return new FaqRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('faqs/index', 'ຄຳຖາມທີ່ພົບເລື້ອຍ', ['rows' => $this->repo()->all()]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'is_visible' => 1], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        [$data, $errors] = $this->input();
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $errors, 422);
        }
        $this->repo()->create($data);
        $this->contentChanged();
        $this->back('/admin/faqs', 'ok', 'ເພີ່ມແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
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
        $this->found($id);
        [$data, $errors] = $this->input();
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $errors, 422);
        }
        $this->repo()->update($id, $data);
        $this->contentChanged();
        $this->back('/admin/faqs', 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $faq = $this->found($id);
        $this->repo()->delete($id);
        $this->contentChanged();
        App::logger()->info('faqs: deleted', ['id' => $id, 'by' => Auth::id()]);
        $this->back('/admin/faqs', 'ok', 'ລຶບ "' . $faq['question_lo'] . '" ແລ້ວ.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $faq = $this->found($id);
        $this->repo()->update($id, ['is_visible' => (int) !(int) $faq['is_visible']]);
        $this->contentChanged();
        $this->back('/admin/faqs#faq' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/faqs#faq' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    private function found(int $id): array
    {
        $faq = $this->repo()->find($id);
        if ($faq === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $faq;
    }

    private function form(array $faq, array $errors, int $status = 200): never
    {
        $this->page('faqs/edit', (int) $faq['id'] === 0 ? 'ເພີ່ມຄຳຖາມ' : 'ແກ້ໄຂຄຳຖາມ', ['faq' => $faq, 'errors' => $errors], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(): array
    {
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        $data['is_visible'] = Request::post('is_visible') === '1' ? 1 : 0;
        return [$data, $errors];
    }
}
