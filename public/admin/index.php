<?php
// Admin front controller. Apache rewrites /admin/* here (public/.htaccess); locally app/bin/dev-router.php does.
declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Http\Admin\AuthController;
use App\Http\Admin\DashboardController;
use App\Http\Admin\MessageController;
use App\Http\Admin\PublishController;
use App\Http\Admin\UserController;

require __DIR__ . '/../../app/bootstrap.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

Session::start();

$auth = new AuthController();
$dash = new DashboardController();
$msg = new MessageController();
$users = new UserController();
$pub = new PublishController();

$r = new Router();
$r->get('/admin', [$dash, 'index']);
$r->get('/admin/login', [$auth, 'showLogin']);
$r->post('/admin/login', [$auth, 'login']);
$r->post('/admin/logout', [$auth, 'logout']);
$r->get('/admin/messages', [$msg, 'index']);
$r->get('/admin/messages/export', [$msg, 'export']);
$r->get('/admin/messages/{id}', [$msg, 'show']);
$r->post('/admin/messages/{id}', [$msg, 'update']);
$r->post('/admin/messages/{id}/notes', [$msg, 'addNote']);
$r->get('/admin/publish', [$pub, 'index']);
$r->post('/admin/publish', [$pub, 'publish']);
$r->post('/admin/publish/rollback', [$pub, 'rollback']);
$r->get('/admin/users', [$users, 'index']);
$r->get('/admin/users/new', [$users, 'create']);
$r->post('/admin/users/new', [$users, 'store']);
$r->get('/admin/users/{id}', [$users, 'edit']);
$r->post('/admin/users/{id}', [$users, 'update']);
$r->get('/admin/account', [$users, 'account']);
$r->post('/admin/account', [$users, 'updateAccount']);

if (!$r->dispatch(Request::method(), Request::path())) {
    Response::html(View::render('admin/errors/standalone', ['code' => 404, 'text' => 'ບໍ່ພົບໜ້ານີ້.']), 404);
}
