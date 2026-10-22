<?php
/** @var string $title @var string $content @var ?array $user @var array $flash @var string $active @var int $newCount */
use App\Core\Csrf;
use App\Http\Admin\UserController;

$nav = [
    ['dashboard', '/admin', 'ໜ້າຫຼັກ', 'M3 12l9-8 9 8M5 10v10h5v-6h4v6h5V10'],
    ['messages', '/admin/messages', 'ກ່ອງຂໍ້ຄວາມ', 'M4 6h16v12H4zM4 7l8 6 8-6'],
    ['divisions', '/admin/divisions', 'ຂະແໜງທຸລະກິດ', 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
    ['groups', '/admin/groups', 'ກຸ່ມສິນຄ້າ', 'M4 6h16M4 12h16M4 18h10'],
    ['products', '/admin/products', 'ສິນຄ້າ', 'M4 8l8-4 8 4v8l-8 4-8-4V8zM4 8l8 4 8-4M12 12v8'],
    ['suppliers', '/admin/suppliers', 'ຜູ້ສະໜອງ', 'M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6M9 11h.01M15 11h.01'],
    ['news', '/admin/news', 'ຂ່າວສານ', 'M5 4h11v16H5zM16 8h3v10a2 2 0 01-2 2M8 8h5M8 12h5M8 16h3'],
    ['media', '/admin/media', 'ຄັງສື່', 'M4 5h16v14H4zM4 15l4-4 4 4 3-3 5 5M15 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z'],
    ['publish', '/admin/publish', 'ເຜີຍແຜ່', 'M12 16V4M7 9l5-5 5 5M5 20h14'],
];
if (($user['role'] ?? '') === 'admin') {
    $nav[] = ['users', '/admin/users', 'ຜູ້ໃຊ້', 'M16 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9.5 10a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM21 19v-1a4 4 0 00-3-3.9M16 3.1a3.5 3.5 0 010 6.8'];
}
$v = static fn (string $f): string => $f . '?v=' . substr((string) @filemtime(__DIR__ . '/../../../public' . $f), -6);
?><!doctype html>
<html lang="lo">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — XTK Admin</title>
<link rel="icon" href="/assets/img/favicon.png">
<link rel="stylesheet" href="/assets/fonts/fonts.css">
<link rel="stylesheet" href="<?= e($v('/admin/assets/admin.css')) ?>">
<script src="<?= e($v('/admin/assets/admin.js')) ?>" defer></script>
</head>
<body>
<a class="skip" href="#main">ຂ້າມໄປເນື້ອຫາ</a>
<div class="shell">
  <aside class="side" id="side">
    <a class="side__logo" href="/admin"><img src="/assets/img/logo.svg" alt="XTK" width="120" height="40"><span>Admin</span></a>
    <nav aria-label="ເມນູຫຼັກ">
      <ul>
        <?php foreach ($nav as [$key, $href, $label, $icon]): ?>
          <li><a href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($icon) ?>"/></svg>
            <span><?= e($label) ?></span>
            <?php if ($key === 'messages' && $newCount > 0): ?><b class="badge" aria-label="<?= e($newCount) ?> ຂໍ້ຄວາມໃໝ່"><?= e($newCount) ?></b><?php endif; ?>
          </a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <p class="side__site"><a href="/lo/index.html" target="_blank" rel="noopener">ເບິ່ງໜ້າເວັບ ↗</a></p>
  </aside>
  <div class="main">
    <header class="top">
      <button class="top__menu" type="button" aria-controls="side" aria-expanded="false" data-menu>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg><span class="sr">ເມນູ</span>
      </button>
      <h1 class="top__title"><?= e($title) ?></h1>
      <?php if ($user !== null): ?>
        <div class="top__user">
          <a href="/admin/account" title="ບັນຊີຂອງຂ້ອຍ"><b><?= e($user['name']) ?></b><small><?= e(UserController::ROLE_NAMES[$user['role']] ?? $user['role']) ?></small></a>
          <form method="post" action="/admin/logout"><?= Csrf::field() ?><button class="btn btn--ghost btn--sm" type="submit">ອອກຈາກລະບົບ</button></form>
        </div>
      <?php endif; ?>
    </header>
    <main id="main" tabindex="-1">
      <?php foreach ($flash as $f): ?>
        <p class="flash flash--<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['text']) ?></p>
      <?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
</div>
</body>
</html>
