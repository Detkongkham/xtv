<?php
// Local only. Router for PHP's built-in server, doing what public/.htaccess does on Apache:
//   php -S 127.0.0.1:8000 -t public app/bin/dev-router.php
declare(strict_types=1);

$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (preg_match('#^/admin(/|$)#', $path) && !str_starts_with($path, '/admin/assets/')) {
    require __DIR__ . '/../../public/admin/index.php';
    return true;
}
// missing files get the site 404 (ErrorDocument in .htaccess); the built-in server would serve /index.html instead
$file = __DIR__ . '/../../public' . $path;
if (!is_file($file) && !is_file(rtrim($file, '/') . '/index.html') && !is_file(rtrim($file, '/') . '/index.php')) {
    http_response_code(404);
    readfile(__DIR__ . '/../../public/lo/404.html');
    return true;
}
// everything else (static pages, assets, /api/contact.php) is served as-is
return false;
