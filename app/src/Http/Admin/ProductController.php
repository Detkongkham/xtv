<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\DivisionRepository;
use App\Repository\MediaRepository;
use App\Repository\ProductRepository;
use App\Repository\RedirectRepository;
use App\Service\Slugger;

/**
 * /admin/products (docs/plan/03-backend.md §2.4). A product with a picture gets its own page product-<slug>.html;
 * without one it is an "ask for a price" card. Editors and admins.
 */
final class ProductController extends Controller
{
    public const FIELDS = [
        ['summary', 'ຄຳອະທິບາຍສັ້ນ', 'textarea', 400, '1–2 ປະໂຫຍກ. ຫວ່າງ = ໃຊ້ຄຳອະທິບາຍຂອງກຸ່ມ.', true],
        ['body', 'ລາຍລະອຽດ', 'textarea', 5000, 'ສະແດງໃນພາກ "ການນຳໃຊ້" ຂອງໜ້າສິນຄ້າ. ແຖວຫວ່າງ = ຂຶ້ນຫຍໍ້ໜ້າໃໝ່. ຫວ່າງ = ໃຊ້ຂໍ້ຄວາມຂອງກຸ່ມ ແລະ ຂະແໜງ.', true],
    ];
    public const SEO_FIELDS = [
        ['seo_title', 'ຫົວຂໍ້ SEO', 'input', 160, 'ຫວ່າງ = ຊື່ສິນຄ້າ.', true],
        ['seo_desc', 'ຄຳອະທິບາຍ SEO', 'textarea', 300, 'ຫວ່າງ = "ຊື່ — ຂະແໜງ".', true],
    ];
    public const SHOW_NAMES = ['' => 'ທັງໝົດ', 'nosupplier' => 'ຍັງບໍ່ມີຜູ້ສະໜອງ', 'noimage' => 'ບໍ່ມີຮູບ (ກາດສອບຖາມລາຄາ)', 'hidden' => 'ເຊື່ອງໄວ້', 'featured' => 'ສິນຄ້າແນະນຳ'];
    private const SPEC_LANGS = ['lo', 'en', 'th', 'zh'];

    private function repo(): ProductRepository
    {
        return new ProductRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $f = ['q' => trim(Request::query('q')), 'division' => (int) Request::query('division'), 'group' => (int) Request::query('group'), 'show' => Request::query('show')];
        if (!isset(self::SHOW_NAMES[$f['show']])) {
            $f['show'] = '';
        }
        $page = max(1, (int) Request::query('page', '1'));
        $result = $this->repo()->search($f, $page);
        $this->page('products/index', 'ສິນຄ້າ', [
            'rows' => $result['rows'], 'total' => $result['total'], 'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / ProductRepository::PER_PAGE)),
            'filters' => $f, 'groups' => $this->repo()->groupOptions(), 'divisions' => (new DivisionRepository(App::db()))->names(),
            'featured' => $this->repo()->countFeatured(),
        ]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'name' => '', 'slug' => '', 'group_id' => (int) Request::query('group'), 'image_id' => null, 'variants_note' => '',
            'is_visible' => 1, 'is_featured' => 0], [], [], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        [$data, $specs, $suppliers, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $specs, $suppliers, $errors, 422);
        }
        $id = $this->repo()->save(null, $data, $specs, $suppliers);
        $this->contentChanged(['name' => $data['name']], $id);
        $this->back('/admin/products/' . $id, 'ok', 'ເພີ່ມສິນຄ້າແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function edit(int $id): never
    {
        $this->requireLogin();
        $p = $this->found($id);
        $this->form($p, $this->repo()->specs($id), $this->repo()->supplierIds($id), []);
    }

    public function update(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $p = $this->found($id);
        [$data, $specs, $suppliers, $errors] = $this->input($p);
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $specs, $suppliers, $errors, 422);
        }
        // the old address keeps working
        if ($data['slug'] !== $p['slug'] && self::isPublished('product-' . $p['slug'])) {
            (new RedirectRepository(App::db()))->add('product-' . $p['slug'], 'product-' . $data['slug']);
        }
        $this->repo()->save($id, $data, $specs, $suppliers);
        $this->contentChanged();
        $this->back('/admin/products/' . $id, 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function copy(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $new = $this->repo()->copy($id);
        $this->contentChanged(['copy_id' => $new]);
        $this->back('/admin/products/' . $new, 'ok', 'ສຳເນົາແລ້ວ. ສະບັບສຳເນົາຖືກເຊື່ອງໄວ້: ແກ້ຊື່ ແລະ ຂໍ້ມູນ ແລ້ວເປີດ "ສະແດງ".');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $p = $this->found($id);
        $this->repo()->delete($id);
        if (self::isPublished('product-' . $p['slug'])) {
            (new RedirectRepository(App::db()))->add('product-' . $p['slug'], $p['division_slug']);
        }
        $this->contentChanged(['name' => $p['name']]);
        App::logger()->info('products: deleted', ['id' => $id, 'slug' => $p['slug'], 'by' => Auth::id()]);
        $this->back('/admin/products?group=' . $p['group_id'], 'ok', 'ລຶບ "' . $p['name'] . '" ແລ້ວ.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $p = $this->found($id);
        $back = Request::post('back') ?: '/admin/products';
        $back = str_starts_with($back, '/admin/products') ? $back : '/admin/products';
        if (Request::post('field') === 'is_featured') {
            $on = !(int) $p['is_featured'];
            if ($on && $p['image_id'] === null) {
                $this->back($back, 'error', 'ສິນຄ້າແນະນຳຕ້ອງມີຮູບ.');
            }
            if ($on && $this->repo()->countFeatured($id) >= ProductRepository::MAX_FEATURED) {
                $this->back($back, 'error', 'ສິນຄ້າແນະນຳມີໄດ້ສູງສຸດ ' . ProductRepository::MAX_FEATURED . ' ລາຍການ. ກະລຸນາປິດອັນອື່ນກ່ອນ.');
            }
            $this->repo()->setFeatured($id, $on);
        } else {
            $this->repo()->update($id, ['is_visible' => (int) !(int) $p['is_visible']]);
        }
        $this->contentChanged(Request::post('field') === 'is_featured' ? ['featured' => !(int) $p['is_featured']] : ['visible' => !(int) $p['is_visible']]);
        $this->back($back . '#p' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $p = $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/products?group=' . $p['group_id'] . '#p' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    private function found(int $id): array
    {
        $p = $this->repo()->find($id);
        if ($p === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $p;
    }

    private function form(array $p, array $specs, array $suppliers, array $errors, int $status = 200): never
    {
        $media = new MediaRepository(App::db());
        $this->page('products/edit', (int) $p['id'] === 0 ? 'ເພີ່ມສິນຄ້າ' : $p['name'], [
            'p' => $p, 'specs' => $specs, 'supplierIds' => $suppliers, 'errors' => $errors,
            'groups' => $this->repo()->groupOptions(), 'supplierOptions' => $this->repo()->supplierOptions(),
            'image' => $p['image_id'] ? $media->find((int) $p['image_id']) : null, 'images' => $media->images(),
            'published' => (int) $p['id'] !== 0 && self::isPublished('product-' . $p['slug']),
        ], $status);
    }

    /** @return array{0: array, 1: array, 2: list<int>, 3: array<string, string>} product data, spec rows, supplier ids, errors */
    private function input(?array $existing): array
    {
        $id = (int) ($existing['id'] ?? 0);
        [$data, $errors] = $this->translatedInput(array_merge(self::FIELDS, self::SEO_FIELDS));
        $data += [
            'name' => trim(preg_replace('/\s+/u', ' ', Request::post('name')) ?? ''),
            'slug' => strtolower(trim(Request::post('slug'))),
            'group_id' => (int) Request::post('group_id'),
            'variants_note' => trim(Request::post('variants_note')) ?: null,
            'is_visible' => Request::post('is_visible') === '1' ? 1 : 0,
            'is_featured' => Request::post('is_featured') === '1' ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = $this->repo()->freeSlug(Slugger::slugify($data['name']) ?: 'product');
        }
        [$data['image_id'], $imageError] = $this->pickedMedia('image_id', isset($existing['image_id']) ? (int) $existing['image_id'] : null);

        $len = mb_strlen($data['name']);
        if ($len < 1 || $len > 160) {
            $errors['name'] = 'ກະລຸນາໃສ່ຊື່ສິນຄ້າ (ສູງສຸດ 160 ຕົວອັກສອນ).';
        }
        if (!Slugger::isValid($data['slug'])) {
            $errors['slug'] = 'ໃຊ້ໄດ້ສະເພາະ a-z, 0-9 ແລະ ຂີດ (-), ເຊັ່ນ ct-scan.';
        } elseif ($this->repo()->slugTaken($data['slug'], $id)) {
            $errors['slug'] = 'ມີສິນຄ້າອື່ນໃຊ້ຊື່ນີ້ແລ້ວ.';
        }
        if (!isset($this->repo()->groupOptions()[$data['group_id']])) {
            $errors['group_id'] = 'ກະລຸນາເລືອກກຸ່ມ.';
        }
        if (mb_strlen((string) $data['variants_note']) > 400) {
            $errors['variants_note'] = 'ຍາວເກີນ 400 ຕົວອັກສອນ.';
        }
        if ($imageError !== null) {
            $errors['image_id'] = $imageError;
        }
        if ($data['is_featured'] && $data['image_id'] === null) {
            $errors['is_featured'] = 'ສິນຄ້າແນະນຳຕ້ອງມີຮູບ.';
        } elseif ($data['is_featured'] && !(int) ($existing['is_featured'] ?? 0) && $this->repo()->countFeatured($id) >= ProductRepository::MAX_FEATURED) {
            $errors['is_featured'] = 'ສິນຄ້າແນະນຳມີໄດ້ສູງສຸດ ' . ProductRepository::MAX_FEATURED . ' ລາຍການ.';
        }
        [$specs, $specErrors] = self::specsInput();
        $errors += $specErrors;

        // suppliers: the chosen primary one first, then the others in list order
        $options = $this->repo()->supplierOptions();
        $chosen = array_values(array_filter(Request::postIds('supplier_ids'), static fn ($s) => isset($options[$s])));
        $primary = (int) Request::post('primary_supplier');
        if (in_array($primary, $chosen, true)) {
            $chosen = array_merge([$primary], array_values(array_diff($chosen, [$primary])));
        }
        return [$data, $specs, $chosen, $errors];
    }

    /** Spec rows from specs[n][label_lo] … in the order they were sent; fully empty rows are dropped. */
    private static function specsInput(): array
    {
        $rows = [];
        $errors = [];
        foreach (is_array($_POST['specs'] ?? null) ? $_POST['specs'] : [] as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $row = [];
            foreach (['label' => 120, 'value' => 400] as $field => $max) {
                foreach (self::SPEC_LANGS as $l) {
                    $v = trim(is_string($raw[$field . '_' . $l] ?? null) ? $raw[$field . '_' . $l] : '');
                    $row[$field . '_' . $l] = $v === '' ? null : mb_substr($v, 0, $max);
                }
            }
            if (array_filter($row) === []) {
                continue;
            }
            $n = count($rows);
            foreach (['label_lo', 'label_en', 'value_lo', 'value_en'] as $k) {
                if ($row[$k] === null) {
                    $errors['specs'] = 'ແຖວຂໍ້ມູນຈຳເພາະທີ ' . ($n + 1) . ': ກະລຸນາໃສ່ປ້າຍ ແລະ ຄ່າ ທັງພາສາລາວ ແລະ ອັງກິດ.';
                }
            }
            $rows[] = $row;
        }
        return [$rows, $errors];
    }
}
