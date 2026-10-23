<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Lang;
use App\Publish\PageList;
use App\Repository\SeoPageRepository;

/**
 * /admin/seo (docs/plan/03-backend.md §2.11): title and description in search results for the fixed pages
 * (PageList::SEO). Empty = the page's own title and description. Products have theirs in the product form.
 * Editors and admins.
 */
final class SeoController extends Controller
{
    public const PAGES = [
        'index' => 'ໜ້າຫຼັກ', 'about' => 'ຂໍ້ມູນບໍລິສັດ', 'vision' => 'ວິໄສທັດ ແລະ ພາລະກິດ', 'products' => 'ຜະລິດຕະພັນ',
        'solutions' => 'ໂຊລູຊັນ', 'services' => 'ບໍລິການຫຼັງການຂາຍ', 'news' => 'ຂ່າວສານ', 'suppliers' => 'ຜູ້ສະໜອງ',
        'contact' => 'ຕິດຕໍ່ພົວພັນ', 'downloads' => 'ດາວໂຫຼດເອກະສານ', 'faq' => 'ຄຳຖາມທີ່ພົບເລື້ອຍ',
    ];

    private function repo(): SeoPageRepository
    {
        return new SeoPageRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $this->page('seo/index', 'SEO ໜ້າຄົງທີ່', ['rows' => $this->repo()->all(), 'defaults' => self::defaults()]);
    }

    public function edit(string $page): never
    {
        $this->requireLogin();
        $this->found($page);
        $this->form($page, $this->repo()->find($page) ?? [], []);
    }

    public function update(string $page): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($page);
        [$data, $errors] = $this->translatedInput(self::fields($page));
        if ($errors !== []) {
            $this->form($page, $data, $errors, 422);
        }
        $this->repo()->save($page, $data);
        $this->contentChanged();
        App::logger()->info('seo: updated', ['page' => $page, 'by' => Auth::id()]);
        $this->back('/admin/seo#seo-' . $page, 'ok', 'ບັນທຶກ "' . self::PAGES[$page] . '" ແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    /** Lang-fields of one page: the home page title is used whole, the others get " | XTK" added. */
    public static function fields(string $page): array
    {
        return [
            ['title', 'ຫົວຂໍ້', 'input', 160, $page === 'index'
                ? 'ໃຊ້ທັງຫົວຂໍ້ (ບໍ່ຕໍ່ທ້າຍ). ຄວນບໍ່ເກີນ 60 ຕົວອັກສອນ. ຫວ່າງ = ຫົວຂໍ້ປົກກະຕິ (ສີເທົາ).'
                : 'ລະບົບຕໍ່ທ້າຍ " | XTK" ໃຫ້. ຄວນບໍ່ເກີນ 50 ຕົວອັກສອນ. ຫວ່າງ = ຫົວຂໍ້ປົກກະຕິ (ສີເທົາ).', true],
            ['desc', 'ຄຳອະທິບາຍ', 'textarea', 300, 'ສະແດງໃຕ້ຫົວຂໍ້ໃນ Google. ຄວນ 70–160 ຕົວອັກສອນ. ຫວ່າງ = ຄຳອະທິບາຍປົກກະຕິ (ສີເທົາ).', true],
        ];
    }

    /**
     * What each page shows when nothing is entered, per language, for the placeholders.
     * @return array<string, array<string, string>> page => title_<lang> / desc_<lang> => text
     */
    public static function defaults(): array
    {
        $out = [];
        foreach (Lang::CODES as $code) {
            $l = new Lang($code);
            foreach (PageList::SEO as $page => [$title, $desc]) {
                $out[$page]['title_' . $code] = ($page === 'index' ? 'XTK — ' : '') . $l->t($title);
                $out[$page]['desc_' . $code] = $l->t($desc);
            }
        }
        return $out;
    }

    private function found(string $page): void
    {
        if (!isset(self::PAGES[$page])) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
    }

    private function form(string $page, array $row, array $errors, int $status = 200): never
    {
        $this->page('seo/edit', 'SEO: ' . self::PAGES[$page], [
            'page' => $page, 'row' => $row, 'errors' => $errors, 'defaults' => self::defaults()[$page],
        ], $status);
    }
}
