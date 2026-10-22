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
use App\Http\Admin\DivisionController;
use App\Http\Admin\GroupController;
use App\Http\Admin\MediaController;
use App\Http\Admin\MessageController;
use App\Http\Admin\NewsController;
use App\Http\Admin\ProductController;
use App\Http\Admin\PublishController;
use App\Http\Admin\SupplierController;
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
$sup = new SupplierController();
$media = new MediaController();
$divs = new DivisionController();
$groups = new GroupController();
$prods = new ProductController();
$news = new NewsController();

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
$r->get('/admin/divisions', [$divs, 'index']);
$r->get('/admin/divisions/new', [$divs, 'create']);
$r->post('/admin/divisions/new', [$divs, 'store']);
$r->get('/admin/divisions/{id}', [$divs, 'edit']);
$r->post('/admin/divisions/{id}', [$divs, 'update']);
$r->post('/admin/divisions/{id}/delete', [$divs, 'delete']);
$r->post('/admin/divisions/{id}/toggle', [$divs, 'toggle']);
$r->post('/admin/divisions/{id}/move', [$divs, 'move']);
$r->get('/admin/groups', [$groups, 'index']);
$r->get('/admin/groups/new', [$groups, 'create']);
$r->post('/admin/groups/new', [$groups, 'store']);
$r->get('/admin/groups/{id}', [$groups, 'edit']);
$r->post('/admin/groups/{id}', [$groups, 'update']);
$r->post('/admin/groups/{id}/delete', [$groups, 'delete']);
$r->post('/admin/groups/{id}/toggle', [$groups, 'toggle']);
$r->post('/admin/groups/{id}/move', [$groups, 'move']);
$r->get('/admin/products', [$prods, 'index']);
$r->get('/admin/products/new', [$prods, 'create']);
$r->post('/admin/products/new', [$prods, 'store']);
$r->get('/admin/products/{id}', [$prods, 'edit']);
$r->post('/admin/products/{id}', [$prods, 'update']);
$r->post('/admin/products/{id}/copy', [$prods, 'copy']);
$r->post('/admin/products/{id}/delete', [$prods, 'delete']);
$r->post('/admin/products/{id}/toggle', [$prods, 'toggle']);
$r->post('/admin/products/{id}/move', [$prods, 'move']);
$r->get('/admin/news', [$news, 'index']);
$r->get('/admin/news/new', [$news, 'create']);
$r->post('/admin/news/new', [$news, 'store']);
$r->get('/admin/news/{id}', [$news, 'edit']);
$r->post('/admin/news/{id}', [$news, 'update']);
$r->post('/admin/news/{id}/delete', [$news, 'delete']);
$r->get('/admin/media', [$media, 'index']);
$r->post('/admin/media', [$media, 'upload']);
$r->get('/admin/media/{id}', [$media, 'edit']);
$r->post('/admin/media/{id}', [$media, 'update']);
$r->post('/admin/media/{id}/delete', [$media, 'delete']);
$r->get('/admin/suppliers', [$sup, 'index']);
$r->get('/admin/suppliers/new', [$sup, 'create']);
$r->post('/admin/suppliers/new', [$sup, 'store']);
$r->get('/admin/suppliers/{id}', [$sup, 'edit']);
$r->post('/admin/suppliers/{id}', [$sup, 'update']);
$r->post('/admin/suppliers/{id}/delete', [$sup, 'delete']);
$r->post('/admin/suppliers/{id}/toggle', [$sup, 'toggle']);
$r->post('/admin/suppliers/{id}/move', [$sup, 'move']);
$r->post('/admin/suppliers/{id}/products', [$sup, 'addProducts']);
$r->post('/admin/suppliers/{id}/products/remove', [$sup, 'removeProduct']);
$r->post('/admin/suppliers/{id}/primary', [$sup, 'primary']);
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
