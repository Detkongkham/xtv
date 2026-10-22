<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repository\MediaRepository;
use App\Repository\MessageRepository;
use App\Repository\PublishLogRepository;
use App\Repository\SettingRepository;

/** Shared helpers for admin controllers. */
abstract class Controller
{
    /** Render a page inside the admin layout. */
    protected function page(string $template, string $title, array $vars = [], int $status = 200): never
    {
        $user = Auth::user();
        $content = View::render('admin/' . $template, $vars + ['user' => $user]);
        Response::html(View::render('admin/layout', [
            'title' => $title,
            'content' => $content,
            'user' => $user,
            'flash' => Session::takeFlash(),
            'active' => explode('/', $template)[0],
            'newCount' => $user === null ? 0 : (new MessageRepository(App::db()))->countByStatus()['new'],
            'unpublished' => $user !== null && self::publishState()['reason'] !== null,
        ]), $status);
    }

    /** @return array{live: ?array, changedAt: ?string, reason: ?string} see PublishLogRepository::pendingReason() */
    protected static function publishState(): array
    {
        $log = new PublishLogRepository(App::db());
        $live = $log->lastLive();
        $changedAt = (new SettingRepository(App::db()))->get('content_changed_at');
        return ['live' => $live, 'changedAt' => $changedAt, 'reason' => PublishLogRepository::pendingReason($live, $changedAt)];
    }

    protected function requireLogin(): array
    {
        $user = Auth::user();
        if ($user === null) {
            Response::redirect('/admin/login');
        }
        return $user;
    }

    protected function requireAdmin(): array
    {
        $user = $this->requireLogin();
        if ($user['role'] !== 'admin') {
            $this->page('errors/forbidden', 'ບໍ່ມີສິດເຂົ້າ', [], 403);
        }
        return $user;
    }

    /** Every admin POST must carry the session CSRF token. */
    protected function checkCsrf(): void
    {
        if (!Csrf::valid(Request::post('_csrf'))) {
            Session::flash('error', 'ໝົດເວລາຂອງຟອມ. ກະລຸນາລອງໃໝ່.');
            Response::redirect(Request::path(), 303);
        }
    }

    /** Remember that site content changed, so the dashboard can say it is not published yet. */
    protected function contentChanged(): void
    {
        (new SettingRepository(App::db()))->set('content_changed_at', date('Y-m-d\TH:i:s'));
    }

    /**
     * Reads translated fields (see partials/lang-fields.php). Empty values become NULL.
     * Required fields need Lao and English; an optional field, once filled in any language, needs both too.
     * @param array $fields list of [column prefix, label, type, max length, hint?, optional?]
     * @return array{0: array, 1: array<string, string>}
     */
    protected function translatedInput(array $fields): array
    {
        $data = [];
        $errors = [];
        foreach ($fields as $f) {
            [$prefix, $label, , $max] = $f;
            $optional = !empty($f[5]);
            $any = false;
            foreach (['lo', 'en', 'th', 'zh'] as $l) {
                $any = $any || trim(Request::post($prefix . '_' . $l)) !== '';
            }
            foreach (['lo', 'en', 'th', 'zh'] as $l) {
                $key = $prefix . '_' . $l;
                $value = trim(str_replace("\r\n", "\n", Request::post($key)));
                if ($value === '' && ($l === 'lo' || $l === 'en') && (!$optional || $any)) {
                    $errors[$key] = 'ກະລຸນາໃສ່' . $label . ($l === 'lo' ? 'ພາສາລາວ' : 'ພາສາອັງກິດ') . '.';
                } elseif (mb_strlen($value) > $max) {
                    $errors[$key] = 'ຍາວເກີນ ' . $max . ' ຕົວອັກສອນ.';
                }
                $data[$key] = $value === '' ? null : $value;
            }
        }
        return [$data, $errors];
    }

    /** One place up or down in a list, from order-buttons.php. */
    protected static function direction(): int
    {
        return Request::post('dir') === 'up' ? -1 : 1;
    }

    /**
     * Value of a media-picker field: a new upload wins, then the chosen library picture, else $current.
     * @return array{0: ?int, 1: ?string} media id (null = none), error message
     */
    protected function pickedMedia(string $name, ?int $current): array
    {
        $file = Request::file($name . '_upload');
        if ($file !== null) {
            try {
                return [MediaController::service()->store($file, Auth::id(), true), null];
            } catch (\InvalidArgumentException $e) {
                return [$current, $e->getMessage()];
            }
        }
        if (!array_key_exists($name, $_POST)) {
            return [$current, null];
        }
        $id = (int) Request::post($name);
        if ($id === 0) {
            return [null, null];
        }
        $m = (new MediaRepository(App::db()))->find($id);
        return $m !== null && $m['kind'] === 'image' ? [$id, null] : [$current, 'ບໍ່ພົບຮູບທີ່ເລືອກ.'];
    }

    /** Whether a page (name without .html) is on the live site now. */
    protected static function isPublished(string $page): bool
    {
        return is_file(dirname(App::path()) . '/public/lo/' . $page . '.html');
    }

    protected function back(string $url, string $type, string $text): never
    {
        Session::flash($type, $text);
        Response::redirect($url, 303);
    }
}
