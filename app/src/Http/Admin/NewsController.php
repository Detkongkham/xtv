<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\MediaRepository;
use App\Repository\NewsRepository;
use App\Repository\RedirectRepository;
use App\Service\HtmlSanitizer;
use App\Service\Slugger;

/**
 * /admin/news (docs/plan/03-backend.md §2.5). The body comes from a small editor and is cleaned by HtmlSanitizer
 * before it is stored, so what reaches the site is always allow-listed HTML.
 */
final class NewsController extends Controller
{
    public const FIELDS = [
        ['title', 'ຫົວຂໍ້', 'input', 200],
        ['summary', 'ສະຫຼຸບ', 'textarea', 400, 'ສະແດງໃນກາດຂ່າວ ແລະ ເປັນຫຍໍ້ໜ້າທຳອິດຂອງຂ່າວ.'],
        ['body', 'ເນື້ອຫາ', 'html', 60000, 'ຫວ່າງ = ບໍ່ມີໜ້າຂອງຕົນເອງ: ກາດຂ່າວລິ້ງໄປ "ລິ້ງປຸ່ມ" ແທນ.', true],
    ];
    public const CATEGORY_NAMES = ['product' => 'ສິນຄ້າ', 'company' => 'ບໍລິສັດ', 'event' => 'ກິດຈະກຳ'];
    public const STATUS_NAMES = ['draft' => 'ສະບັບຮ່າງ', 'published' => 'ເຜີຍແຜ່'];

    private function repo(): NewsRepository
    {
        return new NewsRepository(App::db());
    }

    public function index(): never
    {
        $this->requireLogin();
        $filters = [
            'status' => isset(self::STATUS_NAMES[Request::query('status')]) ? Request::query('status') : '',
            'category' => isset(self::CATEGORY_NAMES[Request::query('category')]) ? Request::query('category') : '',
            'show' => Request::query('show') === 'nocover' ? 'nocover' : '',
            'q' => mb_substr(trim(Request::query('q')), 0, 100),
        ];
        // live: the article's own page is on the site (published at least once)
        $rows = array_map(static fn (array $r): array => $r + ['live' => (int) $r['has_page'] === 1 && self::isPublished('news-' . $r['slug'])], $this->repo()->all());
        $this->page('news/index', 'ຂ່າວສານ', ['rows' => $rows, 'filters' => $filters]);
    }

    public function create(): never
    {
        $this->requireLogin();
        $this->form(['id' => 0, 'slug' => '', 'category' => 'company', 'cover_id' => null, 'link_url' => '', 'published_on' => date('Y-m-d'), 'status' => 'draft'], []);
    }

    public function store(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->form(['id' => 0] + $data, $errors, 422);
        }
        $id = $this->repo()->create($data + ['created_by' => Auth::id()]);
        $this->contentChanged(['name' => $data['title_lo'], 'status' => $data['status']], $id);
        $this->back('/admin/news/' . $id, 'ok', $data['status'] === 'published' ? 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.' : 'ບັນທຶກສະບັບຮ່າງແລ້ວ.');
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
        $n = $this->found($id);
        [$data, $errors] = $this->input($n);
        if ($errors !== []) {
            $this->form(['id' => $id] + $data, $errors, 422);
        }
        $old = 'news-' . $n['slug'];
        if (self::isPublished($old)) {
            $redirects = new RedirectRepository(App::db());
            $hasPage = $data['status'] === 'published' && trim((string) $data['body_en']) !== '';
            if (!$hasPage) {
                $redirects->add($old, 'news'); // taken offline: the old address shows the news list
            } elseif ($data['slug'] !== $n['slug']) {
                $redirects->add($old, 'news-' . $data['slug']);
            }
        }
        $this->repo()->update($id, $data);
        $this->contentChanged(['status' => $data['status']]);
        $this->back('/admin/news/' . $id, 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $n = $this->found($id);
        $this->repo()->delete($id);
        if (self::isPublished('news-' . $n['slug'])) {
            (new RedirectRepository(App::db()))->add('news-' . $n['slug'], 'news');
        }
        $this->contentChanged(['name' => $n['title_lo']]);
        App::logger()->info('news: deleted', ['id' => $id, 'slug' => $n['slug'], 'by' => Auth::id()]);
        $this->back('/admin/news', 'ok', 'ລຶບ "' . $n['title_lo'] . '" ແລ້ວ.');
    }

    private function found(int $id): array
    {
        $n = $this->repo()->find($id);
        if ($n === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $n;
    }

    private function form(array $n, array $errors, int $status = 200): never
    {
        $media = new MediaRepository(App::db());
        $images = $media->images();
        $this->page('news/edit', (int) $n['id'] === 0 ? 'ເພີ່ມຂ່າວ' : ($n['title_lo'] ?? 'ຂ່າວ'), [
            'n' => $n, 'errors' => $errors,
            'cover' => $n['cover_id'] ? $media->find((int) $n['cover_id']) : null, 'images' => $images,
            'published' => (int) $n['id'] !== 0 && self::isPublished('news-' . $n['slug']),
        ], $status);
    }

    /** @return array{0: array, 1: array<string, string>} */
    private function input(?array $existing): array
    {
        $id = (int) ($existing['id'] ?? 0);
        [$data, $errors] = $this->translatedInput(self::FIELDS);
        foreach (['lo', 'en', 'th', 'zh'] as $l) {
            $clean = HtmlSanitizer::clean((string) ($data['body_' . $l] ?? ''));
            // body_lo/body_en are NOT NULL: an article without a body is stored as ''
            $data['body_' . $l] = $clean === '' && ($l === 'th' || $l === 'zh') ? null : $clean;
        }
        unset($errors['body_lo'], $errors['body_en']);
        if (($data['body_lo'] === '') !== ($data['body_en'] === '')) {
            $errors[$data['body_lo'] === '' ? 'body_lo' : 'body_en'] = 'ໃສ່ເນື້ອຫາທັງພາສາລາວ ແລະ ອັງກິດ, ຫຼື ປ່ອຍຫວ່າງທັງສອງ.';
        }
        $data += [
            'slug' => strtolower(trim(Request::post('slug'))) ?: Slugger::slugify((string) $data['title_en']),
            'category' => Request::post('category'),
            'status' => Request::post('status') === 'published' ? 'published' : 'draft',
            'published_on' => Request::post('published_on'),
            'link_url' => trim(Request::post('link_url')) ?: null,
        ];
        [$data['cover_id'], $coverError] = $this->pickedMedia('cover_id', isset($existing['cover_id']) ? (int) $existing['cover_id'] : null);

        if (!Slugger::isValid($data['slug'])) {
            $errors['slug'] = 'ໃຊ້ໄດ້ສະເພາະ a-z, 0-9 ແລະ ຂີດ (-).';
        } elseif ($this->repo()->slugTaken($data['slug'], $id)) {
            $errors['slug'] = 'ມີຂ່າວອື່ນໃຊ້ຊື່ນີ້ແລ້ວ.';
        }
        if (!in_array($data['category'], NewsRepository::CATEGORIES, true)) {
            $errors['category'] = 'ກະລຸນາເລືອກໝວດ.';
        }
        $date = \DateTime::createFromFormat('!Y-m-d', $data['published_on']);
        if ($date === false || $date->format('Y-m-d') !== $data['published_on']) {
            $errors['published_on'] = 'ວັນທີບໍ່ຖືກຕ້ອງ.';
        }
        $link = $data['link_url'];
        if ($link !== null && !preg_match('#^(https?://[^\s"<>]+|[a-z0-9][a-z0-9_-]*\.html(\#[A-Za-z0-9_-]+)?)$#i', $link)) {
            $errors['link_url'] = 'ໃສ່ຊື່ໜ້າໃນເວັບ (ເຊັ່ນ healthcare.html) ຫຼື ລິ້ງເຕັມ https://…';
        }
        if ($data['body_en'] === '' && $link === null) {
            $errors['link_url'] = 'ຂ່າວທີ່ບໍ່ມີເນື້ອຫາ ຕ້ອງມີລິ້ງປຸ່ມ (ກາດຂ່າວຈະລິ້ງໄປທີ່ນີ້).';
        }
        if ($coverError !== null) {
            $errors['cover_id'] = $coverError;
        }
        return [$data, $errors];
    }
}
