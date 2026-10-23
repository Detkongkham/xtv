<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Repository\SettingRepository;
use App\Service\ThemeService;

/**
 * /admin/theme: every color of the site and of the admin, each set with RGB sliders or left to follow its base color.
 * Settings: theme {site, admin} (changed colors only), theme_saved (named themes), theme_history (the last versions).
 * The admin colors apply at once (/admin/theme.css); the site colors are written into the pages on the next publish.
 */
final class ThemeController extends Controller
{
    public const HISTORY = 10;
    public const SAVED_MAX = 20;

    private function repo(): SettingRepository
    {
        return new SettingRepository(App::db());
    }

    /** @return array{site: array<string, string>, admin: array<string, string>} */
    public static function current(?SettingRepository $repo = null): array
    {
        return ThemeService::cleanTheme(($repo ?? new SettingRepository(App::db()))->get('theme', []));
    }

    public function index(): never
    {
        $this->requireAdmin();
        $repo = $this->repo();
        $theme = self::current($repo);
        $this->page('theme/index', 'ຕີມສີ', [
            'theme' => $theme,
            'saved' => (array) $repo->get('theme_saved', []),
            'history' => (array) $repo->get('theme_history', []),
            'warnings' => ['site' => ThemeService::warnings('site', $theme['site']), 'admin' => ThemeService::warnings('admin', $theme['admin'])],
        ]);
    }

    public function update(): never
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $repo = $this->repo();
        $old = self::current($repo);
        $new = ThemeService::cleanTheme(['site' => (array) ($_POST['site'] ?? []), 'admin' => (array) ($_POST['admin'] ?? [])]);

        $name = mb_substr(Validator::clean(Request::post('save_as')), 0, 60);
        if ($name !== '') {
            $saved = array_values(array_filter((array) $repo->get('theme_saved', []), static fn ($s) => ($s['name'] ?? '') !== $name));
            array_unshift($saved, $new + ['name' => $name, 'at' => date('Y-m-d H:i')]);
            $repo->set('theme_saved', array_slice($saved, 0, self::SAVED_MAX));
        }
        if ($new === $old) {
            $this->back('/admin/theme', 'ok', $name !== '' ? 'ບັນທຶກຕີມ "' . $name . '" ແລ້ວ. ສີປັດຈຸບັນບໍ່ມີຫຍັງປ່ຽນ.' : 'ບໍ່ມີຫຍັງປ່ຽນ.');
        }

        $history = (array) $repo->get('theme_history', []);
        array_unshift($history, $old + ['at' => date('Y-m-d H:i'), 'by' => (string) (Auth::user()['name'] ?? '')]);
        $repo->set('theme_history', array_slice($history, 0, self::HISTORY));
        $repo->set('theme', $new);

        $changed = array_keys(array_filter(['site' => $new['site'] !== $old['site'], 'admin' => $new['admin'] !== $old['admin']]));
        App::logger()->info('theme: updated', ['parts' => $changed, 'by' => Auth::id()]);
        $this->activity('update', 'theme', null, ['parts' => $changed]);

        $hard = count(ThemeService::warnings('site', $new['site'])) + count(ThemeService::warnings('admin', $new['admin']));
        $note = $hard > 0 ? ' ມີ ' . $hard . ' ຄູ່ສີທີ່ອ່ານຍາກ, ເບິ່ງລາຍການເຕືອນ.' : '';
        if ($new['site'] === $old['site']) {
            $this->back('/admin/theme', 'ok', 'ບັນທຶກແລ້ວ.' . $note);
        }
        $this->markUnpublished();
        $this->back('/admin/theme', 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ສີໃໝ່ຂຶ້ນໜ້າເວັບ.' . $note);
    }

    public function deleteSaved(): never
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $repo = $this->repo();
        $name = Request::post('name');
        $saved = (array) $repo->get('theme_saved', []);
        $left = array_values(array_filter($saved, static fn ($s) => ($s['name'] ?? '') !== $name));
        if (count($left) === count($saved)) {
            $this->back('/admin/theme', 'error', 'ບໍ່ພົບຕີມນີ້.');
        }
        $repo->set('theme_saved', $left);
        $this->back('/admin/theme', 'ok', 'ລຶບຕີມ "' . $name . '" ແລ້ວ.');
    }

    /** The admin colors as a stylesheet: a <style> in the page would be blocked by the admin CSP (style-src 'self'). */
    public function css(): never
    {
        header('Content-Type: text/css; charset=utf-8');
        header('Cache-Control: private, max-age=31536000, immutable');
        echo ThemeService::css('admin', self::current()['admin']);
        exit;
    }

    /** ?v= for /admin/theme.css, null when the admin keeps its default colors. */
    public static function adminVersion(array $theme): ?string
    {
        return $theme['admin'] === [] ? null : substr(md5(ThemeService::css('admin', $theme['admin'])), 0, 8);
    }
}
