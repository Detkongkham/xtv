<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Lang;
use App\Core\Request;
use App\Publish\PageList;
use App\Publish\SiteData;
use App\Repository\HeroRepository;
use App\Repository\MediaRepository;
use App\Service\MediaService;

/** /admin/hero (docs/plan/03-backend.md §2.10): up to 5 slides at the top of the home page. Editors and admins. */
final class HeroController extends Controller
{
    public const FIELDS = [
        ['eyebrow', 'ຫົວຂໍ້ນ້ອຍ', 'input', 120, 'ແຖວນ້ອຍເທິງຫົວຂໍ້.', true],
        ['title', 'ຫົວຂໍ້', 'input', 200, 'ຫົວຂໍ້ຂອງ slide ທຳອິດເປັນ h1 ຂອງໜ້າຫຼັກ.'],
        ['text', 'ຂໍ້ຄວາມ', 'textarea', 400, '', true],
        ['button', 'ປ້າຍປຸ່ມ', 'input', 60, 'ຫວ່າງ = ມີແຕ່ປຸ່ມ "ຕິດຕໍ່".', true],
    ];

    /** fixed pages => lang key of their name */
    private const FIXED = ['index' => 'home', 'about' => 'about_profile', 'vision' => 'vision', 'products' => 'products',
        'services' => 'services', 'news' => 'news', 'contact' => 'contact', 'downloads' => 'downloads', 'faq' => 'faq', 'suppliers' => 'sup_h'];

    private function repo(): HeroRepository
    {
        return new HeroRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('hero/index', 'Hero ໜ້າຫຼັກ', ['rows' => $this->repo()->all()]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->checkRoom();
        $this->form(['id' => 0, 'media_id' => null, 'poster_id' => null, 'button_page' => 'products', 'is_visible' => 1], [], 'image');
    }

    public function store(): never
    {
        $this->requireLogin();
        if (Request::tooLarge()) {
            $this->back('/admin/hero/new', 'error', 'ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ 20 MB).');
        }
        $this->checkCsrf();
        $this->checkRoom();
        [$data, $errors, $kind] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $errors, $kind, 422);
        }
        $this->contentChanged(['name' => $data['title_lo']], $this->repo()->create($data));
        $this->back('/admin/hero', 'ok', 'ເພີ່ມແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function edit(int $id): never
    {
        $this->requireLogin();
        $slide = $this->found($id);
        $this->form($slide, [], $this->kindOf($slide));
    }

    public function update(int $id): never
    {
        $this->requireLogin();
        if (Request::tooLarge()) {
            $this->back('/admin/hero/' . $id, 'error', 'ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ 20 MB).');
        }
        $this->checkCsrf();
        $slide = $this->found($id);
        [$data, $errors, $kind] = $this->input($slide);
        if ((int) $slide['is_visible'] === 1 && $data['is_visible'] === 0 && $this->repo()->countVisible() === 1) {
            $errors['is_visible'] = 'ນີ້ແມ່ນ slide ດຽວທີ່ສະແດງຢູ່. ໜ້າຫຼັກຕ້ອງມີຢ່າງໜ້ອຍ 1 slide.';
        }
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $errors, $kind, 422);
        }
        $this->repo()->update($id, $data);
        $this->contentChanged();
        $this->back('/admin/hero', 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $slide = $this->found($id);
        if ((int) $slide['is_visible'] === 1 && $this->repo()->countVisible() === 1) {
            $this->back('/admin/hero/' . $id, 'error', 'ລຶບບໍ່ໄດ້: ນີ້ແມ່ນ slide ດຽວທີ່ສະແດງຢູ່. ເພີ່ມ ຫຼື ເປີດ slide ອື່ນກ່ອນ.');
        }
        $this->repo()->delete($id);
        $this->contentChanged(['name' => $slide['title_lo']]);
        App::logger()->info('hero: deleted', ['id' => $id, 'by' => Auth::id()]);
        $this->back('/admin/hero', 'ok', 'ລຶບ "' . $slide['title_lo'] . '" ແລ້ວ. ຮູບ ຫຼື ວິດີໂອຍັງຢູ່ໃນຄັງສື່.');
    }

    public function toggle(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $slide = $this->found($id);
        $on = (int) $slide['is_visible'] === 1;
        if ($on && $this->repo()->countVisible() === 1) {
            $this->back('/admin/hero#hero' . $id, 'error', 'ໜ້າຫຼັກຕ້ອງມີຢ່າງໜ້ອຍ 1 slide ທີ່ສະແດງ.');
        }
        $this->repo()->update($id, ['is_visible' => (int) !$on]);
        $this->contentChanged(['visible' => !$on]);
        $this->back('/admin/hero#hero' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function move(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $this->repo()->move($id, self::direction());
        $this->contentChanged();
        $this->back('/admin/hero#hero' . $id, 'ok', 'ປ່ຽນລຳດັບແລ້ວ.');
    }

    private function checkRoom(): void
    {
        if ($this->repo()->count() >= HeroRepository::MAX) {
            $this->back('/admin/hero', 'error', 'ມີຄົບ ' . HeroRepository::MAX . ' slide ແລ້ວ. ລຶບອັນເກົ່າອອກກ່ອນ ຫຼື ແກ້ໄຂອັນທີ່ມີຢູ່.');
        }
    }

    private function found(int $id): array
    {
        $slide = $this->repo()->find($id);
        if ($slide === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $slide;
    }

    private function kindOf(array $slide): string
    {
        $m = $slide['media_id'] === null ? null : (new MediaRepository(App::db()))->find((int) $slide['media_id']);
        return ($m['kind'] ?? 'image') === 'video' ? 'video' : 'image';
    }

    private function form(array $slide, array $errors, string $kind, int $status = 200): never
    {
        $media = new MediaRepository(App::db());
        $find = static fn ($id) => $id === null ? null : $media->find((int) $id);
        $current = $find($slide['media_id']);
        $this->page('hero/edit', (int) $slide['id'] === 0 ? 'ເພີ່ມ slide' : 'ແກ້ໄຂ slide', [
            'slide' => $slide, 'errors' => $errors, 'kind' => $kind,
            'image' => ($current['kind'] ?? '') === 'image' ? $current : null,
            'video' => ($current['kind'] ?? '') === 'video' ? $current : null,
            'poster' => $find($slide['poster_id']),
            'images' => $media->images(), 'videos' => $media->videos(),
            'pages' => $this->pageOptions((string) ($slide['button_page'] ?? '')),
        ], $status);
    }

    /**
     * Pages a button may link to, grouped for the select: group => [page => label]. A page that is no longer on
     * the site but is the current choice stays in the list, marked, so saving does not change it silently.
     */
    private function pageOptions(string $current): array
    {
        $site = SiteData::fromDb(App::db());
        $lo = new Lang('lo');
        $groups = ['ໜ້າຫຼັກຂອງເວັບ' => [], 'ຂະແໜງ' => [], 'ສິນຄ້າ' => [], 'ຂ່າວ' => [], 'ຜູ້ສະໜອງ' => []];
        $names = array_flip(PageList::names($site));
        foreach (self::FIXED as $page => $key) {
            if (isset($names[$page])) {
                $groups['ໜ້າຫຼັກຂອງເວັບ'][$page] = $lo->t($key);
            }
        }
        foreach ($site->divisions() as $d) {
            $groups['ຂະແໜງ'][$d['slug']] = $d['name_lo'];
        }
        foreach ($site->allProducts() as $place) {
            $page = 'product-' . $place['product']['slug'];
            if (isset($names[$page])) {
                $groups['ສິນຄ້າ'][$page] = $place['product']['name'];
            }
        }
        foreach ($site->news() as $n) {
            if (isset($names['news-' . $n['slug']])) {
                $groups['ຂ່າວ']['news-' . $n['slug']] = $n['title_lo'];
            }
        }
        foreach ($site->suppliers() as $s) {
            $groups['ຜູ້ສະໜອງ']['supplier-' . $s['slug']] = $s['name'];
        }
        if ($current !== '' && !isset($names[$current])) {
            $groups['ໜ້າຫຼັກຂອງເວັບ'] = [$current => $current . ' (ບໍ່ມີໃນເວັບແລ້ວ: ປຸ່ມພາໄປໜ້າສິນຄ້າທັງໝົດ)'] + $groups['ໜ້າຫຼັກຂອງເວັບ'];
        }
        return array_filter($groups);
    }

    /** @return array{0: array, 1: array<string, string>, 2: string} data, errors, 'image' or 'video' */
    private function input(?array $existing): array
    {
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        $kind = Request::post('kind') === 'video' ? 'video' : 'image';
        $wasKind = $existing === null ? null : $this->kindOf($existing);
        $currentMedia = $existing !== null && $wasKind === $kind ? (int) $existing['media_id'] : null;

        if ($kind === 'image') {
            [$data['media_id'], $err] = $this->pickedMedia('media_id', $currentMedia);
            $data['poster_id'] = null;
            if ($err !== null || $data['media_id'] === null) {
                $errors['media_id'] = $err ?? 'ກະລຸນາເລືອກ ຫຼື ອັບຮູບ.';
            }
        } else {
            $data['media_id'] = $this->pickedVideo($currentMedia, $errors);
            [$data['poster_id'], $err] = $this->pickedMedia('poster_id', $existing === null ? null : ($existing['poster_id'] === null ? null : (int) $existing['poster_id']));
            if ($err !== null || $data['poster_id'] === null) {
                $errors['poster_id'] = $err ?? 'ວິດີໂອຕ້ອງມີຮູບ poster: ສະແດງກ່ອນວິດີໂອໂຫຼດ ແລະ ເມື່ອຜູ້ໃຊ້ປິດການເຄື່ອນໄຫວ.';
            }
        }

        $page = Request::post('button_page');
        $allowed = [];
        foreach ($this->pageOptions((string) ($existing['button_page'] ?? '')) as $pages) {
            $allowed += $pages;
        }
        $data['button_page'] = $page === '' ? null : $page;
        if ($data['button_lo'] !== null && $page === '') {
            $errors['button_page'] = 'ກະລຸນາເລືອກໜ້າທີ່ປຸ່ມພາໄປ.';
        } elseif ($page !== '' && !isset($allowed[$page])) {
            $errors['button_page'] = 'ກະລຸນາເລືອກຈາກລາຍການ.';
        }
        $data['is_visible'] = Request::post('is_visible') === '1' ? 1 : 0;
        return [$data, $errors, $kind];
    }

    /** New MP4 upload, else the chosen library video, else $current. Adds to $errors when there is none. */
    private function pickedVideo(?int $current, array &$errors): ?int
    {
        $file = Request::file('video_upload');
        if ($file !== null) {
            try {
                return MediaController::service()->storeVideo($file, Auth::id());
            } catch (\InvalidArgumentException $e) {
                $errors['video_id'] = $e->getMessage();
                return $current;
            }
        }
        $id = (int) Request::post('video_id');
        $m = $id > 0 ? (new MediaRepository(App::db()))->find($id) : null;
        if ($m !== null && $m['kind'] === 'video') {
            return $id;
        }
        if ($current === null) {
            $errors['video_id'] = 'ກະລຸນາອັບວິດີໂອ MP4 (ສູງສຸດ ' . (MediaService::VIDEO_MAX_BYTES >> 20) . ' MB) ຫຼື ເລືອກຈາກຄັງສື່.';
        }
        return $current;
    }
}
