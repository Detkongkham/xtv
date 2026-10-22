<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Countries;
use App\Core\Request;
use App\Core\Validator;
use App\Repository\RedirectRepository;
use App\Repository\SupplierRepository;
use App\Service\MediaService;
use App\Service\Slugger;

/**
 * /admin/suppliers (docs/plan/08-suppliers.md §5). Editors and admins.
 * The most important part is linking products: that is what tells visitors which products XTK sells from whom.
 */
final class SupplierController extends Controller
{
    public const PARTNERSHIP_NAMES = ['exclusive' => 'ຕົວແທນຈຳໜ່າຍແຕ່ຜູ້ດຽວ', 'authorized' => 'ຕົວແທນຈຳໜ່າຍທີ່ໄດ້ຮັບອະນຸຍາດ', 'distributor' => 'ຕົວແທນຈຳໜ່າຍ'];
    public const LANG_NAMES = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
    private const ABOUT_MAX = 3000;

    private function repo(): SupplierRepository
    {
        return new SupplierRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('suppliers/index', 'ຜູ້ສະໜອງ', [
            'rows' => $this->repo()->all(),
            'withoutSupplier' => $this->repo()->countProductsWithoutSupplier(),
        ]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'name' => '', 'slug' => '', 'country_code' => '', 'website_url' => '', 'logo_id' => null, 'logo_path' => null,
            'logo_on_dark' => 0, 'partnership' => null, 'since_year' => null, 'is_featured' => 0, 'is_visible' => 1]
            + array_fill_keys(array_map(static fn ($l) => 'about_' . $l, SupplierRepository::LANGS), ''), []);
    }

    public function store(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0, 'logo_path' => null] + $data, $errors, 422);
        }
        $id = $this->repo()->create($data);
        $this->contentChanged();
        $this->back('/admin/suppliers/' . $id, 'ok', 'ເພີ່ມຜູ້ສະໜອງແລ້ວ. ຕໍ່ໄປ: ເລືອກສິນຄ້າທີ່ XTK ຂາຍຈາກຜູ້ສະໜອງນີ້.');
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
        $s = $this->found($id);
        [$data, $errors] = $this->input($s);
        if ($errors !== []) {
            $this->form(['id' => $id, 'logo_path' => $s['logo_path']] + $data, $errors, 422);
        }
        // a published page keeps working under its old name
        if ($data['slug'] !== $s['slug'] && self::isPublished('supplier-' . $s['slug'])) {
            (new RedirectRepository(App::db()))->add('supplier-' . $s['slug'], 'supplier-' . $data['slug']);
        }
        $this->repo()->update($id, $data);
        $this->contentChanged();
        $this->back('/admin/suppliers/' . $id, 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $s = $this->found($id);
        $this->repo()->delete($id);
        if (self::isPublished('supplier-' . $s['slug'])) {
            (new RedirectRepository(App::db()))->add('supplier-' . $s['slug'], 'suppliers');
        }
        $this->contentChanged();
        App::logger()->info('suppliers: deleted', ['id' => $id, 'slug' => $s['slug'], 'by' => Auth::id()]);
        $this->back('/admin/suppliers', 'ok', 'ລຶບ "' . $s['name'] . '" ແລ້ວ. ສິນຄ້າບໍ່ຖືກລຶບ.');
    }

    /** Featured / visible switches in the list. */
    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $s = $this->found($id);
        $field = Request::post('field');
        if (!in_array($field, ['is_featured', 'is_visible'], true)) {
            $this->back('/admin/suppliers', 'error', 'ຄຳສັ່ງບໍ່ຖືກຕ້ອງ.');
        }
        $on = !(int) $s[$field];
        if ($field === 'is_featured' && $on && $this->repo()->countFeatured($id) >= SupplierRepository::MAX_FEATURED) {
            $this->back('/admin/suppliers', 'error', 'ຜູ້ສະໜອງຫຼັກມີໄດ້ສູງສຸດ ' . SupplierRepository::MAX_FEATURED . ' ລາຍ. ກະລຸນາປິດອັນອື່ນກ່ອນ.');
        }
        $this->repo()->update($id, [$field => (int) $on]);
        $this->contentChanged();
        $this->back('/admin/suppliers#s' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, Request::post('dir') === 'up' ? -1 : 1);
        $this->contentChanged();
        $this->back('/admin/suppliers#s' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    public function addProducts(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $ids = Request::postIds('product_ids');
        if ($ids === []) {
            $this->back('/admin/suppliers/' . $id . '#products', 'error', 'ກະລຸນາເລືອກສິນຄ້າຢ່າງໜ້ອຍ 1 ລາຍການ.');
        }
        $n = $this->repo()->link($id, $ids);
        $this->contentChanged();
        $this->back('/admin/suppliers/' . $id . '#products', 'ok', "ເພີ່ມ {$n} ສິນຄ້າແລ້ວ.");
    }

    public function removeProduct(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->unlink($id, (int) Request::post('product_id'));
        $this->contentChanged();
        $this->back('/admin/suppliers/' . $id . '#products', 'ok', 'ເອົາສິນຄ້າອອກແລ້ວ.');
    }

    public function primary(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->makePrimary($id, (int) Request::post('product_id'));
        $this->contentChanged();
        $this->back('/admin/suppliers/' . $id . '#products', 'ok', 'ຕັ້ງເປັນຜູ້ສະໜອງຫຼັກຂອງສິນຄ້ານີ້ແລ້ວ.');
    }

    private function found(int $id): array
    {
        $s = $this->repo()->find($id);
        if ($s === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $s;
    }

    private function form(array $s, array $errors, int $status = 200): never
    {
        $isNew = (int) $s['id'] === 0;
        $this->page('suppliers/edit', $isNew ? 'ເພີ່ມຜູ້ສະໜອງ' : $s['name'], [
            's' => $s,
            'errors' => $errors,
            'countries' => Countries::options('lo'),
            'products' => $isNew ? [] : $this->repo()->products((int) $s['id']),
            'unlinked' => $isNew ? [] : $this->repo()->unlinkedProducts((int) $s['id']),
            'published' => !$isNew && self::isPublished('supplier-' . $s['slug']),
        ], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(?array $existing): array
    {
        $id = (int) ($existing['id'] ?? 0);
        $data = [
            'name' => Validator::clean(Request::post('name')),
            'slug' => strtolower(trim(Request::post('slug'))),
            'country_code' => strtoupper(trim(Request::post('country_code'))),
            'website_url' => trim(Request::post('website_url')) ?: null,
            'logo_on_dark' => Request::post('logo_on_dark') === '1' ? 1 : 0,
            'partnership' => Request::post('partnership') ?: null,
            'since_year' => trim(Request::post('since_year')) === '' ? null : (int) Request::post('since_year'),
            'is_featured' => Request::post('is_featured') === '1' ? 1 : 0,
            'is_visible' => Request::post('is_visible') === '1' ? 1 : 0,
            'logo_id' => $existing['logo_id'] ?? null,
        ];
        foreach (SupplierRepository::LANGS as $l) {
            $text = trim(str_replace("\r\n", "\n", Request::post('about_' . $l)));
            $data['about_' . $l] = $text === '' ? null : $text;
        }
        if ($data['slug'] === '') {
            $data['slug'] = Slugger::slugify($data['name']);
        }

        $errors = [];
        $len = mb_strlen($data['name']);
        if ($len < 1 || $len > 160) {
            $errors['name'] = 'ກະລຸນາໃສ່ຊື່ (ສູງສຸດ 160 ຕົວອັກສອນ).';
        }
        if (!Slugger::isValid($data['slug'])) {
            $errors['slug'] = 'ໃຊ້ໄດ້ສະເພາະ a-z, 0-9 ແລະ ຂີດ (-), ເຊັ່ນ mindray-medical.';
        } elseif ($this->repo()->slugTaken($data['slug'], $id)) {
            $errors['slug'] = 'ມີຜູ້ສະໜອງອື່ນໃຊ້ຊື່ URL ນີ້ແລ້ວ.';
        }
        if (!Countries::exists($data['country_code'])) {
            $errors['country_code'] = 'ກະລຸນາເລືອກປະເທດ.';
        }
        if ($data['website_url'] !== null && (!preg_match('#^https?://#i', $data['website_url']) || filter_var($data['website_url'], FILTER_VALIDATE_URL) === false || strlen($data['website_url']) > 255)) {
            $errors['website_url'] = 'ລິ້ງຕ້ອງຂຶ້ນຕົ້ນດ້ວຍ https:// (ຫຼື http://).';
        }
        if ($data['partnership'] !== null && !in_array($data['partnership'], SupplierRepository::PARTNERSHIPS, true)) {
            $errors['partnership'] = 'ກະລຸນາເລືອກຈາກລາຍການ.';
        }
        if ($data['since_year'] !== null && ($data['since_year'] < 1950 || $data['since_year'] > (int) date('Y'))) {
            $errors['since_year'] = 'ປີບໍ່ຖືກຕ້ອງ.';
        }
        foreach (SupplierRepository::LANGS as $l) {
            if (mb_strlen((string) $data['about_' . $l]) > self::ABOUT_MAX) {
                $errors['about_' . $l] = 'ຍາວເກີນ ' . self::ABOUT_MAX . ' ຕົວອັກສອນ.';
            }
        }
        if ($data['is_featured'] && $this->repo()->countFeatured($id) >= SupplierRepository::MAX_FEATURED) {
            $errors['is_featured'] = 'ຜູ້ສະໜອງຫຼັກມີໄດ້ສູງສຸດ ' . SupplierRepository::MAX_FEATURED . ' ລາຍ.';
        }

        if (Request::post('remove_logo') === '1') {
            $data['logo_id'] = null;
        }
        $file = Request::file('logo');
        if ($file !== null && $errors === []) {
            try {
                $data['logo_id'] = (new MediaService(App::db(), dirname(App::path()) . '/public'))->storeLogo($file, Auth::id());
            } catch (\InvalidArgumentException $e) {
                $errors['logo'] = $e->getMessage();
            }
        } elseif ($file !== null) {
            $errors['logo'] = 'ກະລຸນາເລືອກໄຟລ໌ໂລໂກ້ອີກຄັ້ງ ຫຼັງແກ້ຂໍ້ຜິດພາດອື່ນ.';
        }
        return [$data, $errors];
    }
}
