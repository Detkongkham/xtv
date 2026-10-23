<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Publish\Publisher;
use App\Publish\PublishBusyException;
use App\Repository\PublishLogRepository;

/** /admin/publish: publish the site, see the history, put a previous version back. Editors may do all of it. */
final class PublishController extends Controller
{
    public const STATUS_NAMES = ['running' => 'ກຳລັງເຮັດ', 'success' => 'ສຳເລັດ', 'failed' => 'ບໍ່ສຳເລັດ', 'rolled_back' => 'ກັບຄືນຊຸດເກົ່າ'];

    public function index(): never
    {
        $this->requireLogin();
        $publisher = self::publisher();
        $this->page('publish/index', 'ເຜີຍແຜ່', [
            'history' => (new PublishLogRepository(App::db()))->latest(15),
            'builds' => $publisher->builds(),
            'busy' => $publisher->isBusy(),
        ]);
    }

    public function publish(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        try {
            $r = self::publisher()->publish(Auth::id());
        } catch (PublishBusyException $e) {
            $this->back('/admin/publish', 'error', $e->getMessage());
        }
        $this->activity('publish', 'publish', null, ['status' => $r['status'], 'pages' => $r['pages'] ?? null]);
        if ($r['status'] === 'failed') {
            $this->back('/admin/publish', 'error', 'ເຜີຍແຜ່ບໍ່ສຳເລັດ. ເວັບທີ່ອອນລາຍຢູ່ບໍ່ມີການປ່ຽນແປງ. ເບິ່ງສາເຫດໃນປະຫວັດຂ້າງລຸ່ມ.');
        }
        $text = sprintf('ເຜີຍແຜ່ສຳເລັດ: %d ໜ້າ ໃນ %.1f ວິນາທີ.', $r['pages'], $r['ms'] / 1000);
        $this->back('/admin/publish', 'ok', $r['warnings'] ? $text . ' ມີຄຳເຕືອນ, ເບິ່ງຂ້າງລຸ່ມ.' : $text);
    }

    public function rollback(): never
    {
        $this->requireLogin();
        $this->checkCsrf();
        try {
            self::publisher()->rollback(Auth::id(), Request::post('build'));
        } catch (\RuntimeException $e) {
            $this->back('/admin/publish', 'error', $e->getMessage());
        }
        $this->activity('rollback', 'publish', null, ['build' => self::buildLabel(Request::post('build'))]);
        $this->back('/admin/publish', 'ok', 'ກັບຄືນໄປຊຸດ ' . self::buildLabel(Request::post('build')) . ' ແລ້ວ.');
    }

    /** "20261022-220608-7755" -> "22/10/2026 22:06:08" */
    public static function buildLabel(?string $id): string
    {
        $t = $id ? \DateTime::createFromFormat('Ymd-His', substr($id, 0, 15)) : false;
        return $t ? $t->format('d/m/Y H:i:s') : (string) $id;
    }

    private static function publisher(): Publisher
    {
        return new Publisher(App::db(), App::path('storage'), dirname(App::path()) . '/public', App::config('base_url'));
    }
}
