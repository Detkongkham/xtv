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
use App\Repository\MessageRepository;

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
        ]), $status);
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

    protected function back(string $url, string $type, string $text): never
    {
        Session::flash($type, $text);
        Response::redirect($url, 303);
    }
}
