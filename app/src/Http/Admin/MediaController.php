<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Repository\MediaRepository;
use App\Service\MediaService;

/** /admin/media: upload pictures and PDFs, see where each is used, delete unused ones. */
final class MediaController extends Controller
{
    public const KIND_NAMES = ['' => 'ທັງໝົດ', 'image' => 'ຮູບ', 'pdf' => 'PDF', 'video' => 'ວິດີໂອ'];

    private function repo(): MediaRepository
    {
        return new MediaRepository(App::db());
    }

    public static function service(): MediaService
    {
        return new MediaService(App::db(), dirname(App::path()) . '/public');
    }

    public function index(): never
    {
        $this->requireLogin();
        $kind = Request::query('kind');
        $q = trim(Request::query('q'));
        $page = max(1, (int) Request::query('page', '1'));
        $result = $this->repo()->search($kind, $q, $page);
        $this->page('media/index', 'ຄັງສື່', [
            'rows' => $result['rows'], 'total' => $result['total'], 'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / MediaRepository::PER_PAGE)),
            'filters' => ['kind' => $kind, 'q' => $q],
        ]);
    }

    public function upload(): never
    {
        $this->requireLogin();
        if (Request::tooLarge()) {
            $this->back('/admin/media', 'error', 'ໄຟລ໌ທີ່ເລືອກລວມກັນໃຫຍ່ເກີນໄປ. ກະລຸນາອັບເທື່ອລະໜ້ອຍ.');
        }
        $this->checkCsrf();
        $files = Request::files('files');
        if ($files === []) {
            $this->back('/admin/media', 'error', 'ກະລຸນາເລືອກໄຟລ໌.');
        }
        $ok = 0;
        $errors = [];
        foreach ($files as $file) {
            try {
                self::service()->store($file, Auth::id());
                $ok++;
            } catch (\InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }
        if ($errors) {
            $this->back('/admin/media', 'error', ($ok ? "ອັບແລ້ວ {$ok} ໄຟລ໌. " : '') . 'ບໍ່ສຳເລັດ: ' . implode(' · ', $errors));
        }
        $this->back('/admin/media', 'ok', "ອັບແລ້ວ {$ok} ໄຟລ໌.");
    }

    public function edit(int $id): never
    {
        $this->requireLogin();
        $m = $this->found($id);
        $this->page('media/edit', $m['original_name'], ['m' => $m, 'usage' => $this->repo()->usage($id)]);
    }

    public function update(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $this->found($id);
        $alt = [];
        foreach (MediaRepository::LANGS as $l) {
            $alt[$l] = trim(Request::post('alt_' . $l));
        }
        $this->repo()->updateAlt($id, $alt);
        $this->contentChanged();
        $this->back('/admin/media/' . $id, 'ok', 'ບັນທຶກແລ້ວ.');
    }

    public function delete(int $id): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        $m = $this->found($id);
        if ($this->repo()->usage($id) !== []) {
            $this->back('/admin/media/' . $id, 'error', 'ລຶບບໍ່ໄດ້: ໄຟລ໌ນີ້ຍັງຖືກໃຊ້ຢູ່. ປ່ຽນຮູບໃນບ່ອນທີ່ໃຊ້ກ່ອນ.');
        }
        $this->repo()->delete($id);
        self::service()->deleteFiles($m);
        App::logger()->info('media: deleted', ['id' => $id, 'path' => $m['path'], 'by' => Auth::id()]);
        $this->back('/admin/media', 'ok', 'ລຶບ "' . $m['original_name'] . '" ແລ້ວ.');
    }

    /** Smallest picture of a media row, for admin thumbnails. */
    public static function thumb(array $m): string
    {
        $variants = json_decode((string) ($m['variants'] ?? 'null'), true);
        return '/' . ($variants ? $variants[0]['webp'] : $m['path']);
    }

    private function found(int $id): array
    {
        $m = $this->repo()->find($id);
        if ($m === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        return $m;
    }
}
