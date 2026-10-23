<?php
/** @var string $title @var string $content @var ?array $user @var array $flash @var string $active @var bool $ownHeader @var int $newCount @var bool $unpublished */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\UserController;

$nav = [
    'ພາບລວມ' => [
        ['dashboard', '/admin', 'ໜ້າຫຼັກ', 'M3 12l9-8 9 8M5 10v10h5v-6h4v6h5V10'],
        ['messages', '/admin/messages', 'ກ່ອງຂໍ້ຄວາມ', 'M4 6h16v12H4zM4 7l8 6 8-6'],
    ],
    'ສິນຄ້າ' => [
        ['divisions', '/admin/divisions', 'ຂະແໜງທຸລະກິດ', 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
        ['groups', '/admin/groups', 'ກຸ່ມສິນຄ້າ', 'M4 6h16M4 12h16M4 18h10'],
        ['products', '/admin/products', 'ສິນຄ້າ', 'M4 8l8-4 8 4v8l-8 4-8-4V8zM4 8l8 4 8-4M12 12v8'],
        ['suppliers', '/admin/suppliers', 'ຜູ້ສະໜອງ', 'M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6M9 11h.01M15 11h.01'],
    ],
    'ເນື້ອຫາເວັບ' => [
        ['news', '/admin/news', 'ຂ່າວສານ', 'M5 4h11v16H5zM16 8h3v10a2 2 0 01-2 2M8 8h5M8 12h5M8 16h3'],
        ['downloads', '/admin/downloads', 'ໄຟລ໌ດາວໂຫຼດ', 'M12 4v11M7 11l5 5 5-5M5 20h14'],
        ['hero', '/admin/hero', 'Hero ໜ້າຫຼັກ', 'M3 5h18v14H3zM3 15l5-5 4 4 3-3 6 6'],
        ['faqs', '/admin/faqs', 'FAQ', 'M12 21a9 9 0 100-18 9 9 0 000 18zM9.5 9a2.5 2.5 0 114 2c-.9.6-1.5 1.2-1.5 2.5M12 17h.01'],
        ['seo', '/admin/seo', 'SEO', 'M11 18a7 7 0 100-14 7 7 0 000 14zM21 21l-5-5'],
        ['media', '/admin/media', 'ຄັງສື່', 'M4 5h16v14H4zM4 15l4-4 4 4 3-3 5 5M15 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z'],
    ],
    'ລະບົບ' => [
        ['publish', '/admin/publish', 'ເຜີຍແຜ່', 'M12 16V4M7 9l5-5 5 5M5 20h14'],
    ],
];
if (($user['role'] ?? '') === 'admin') {
    $nav['ລະບົບ'][] = ['users', '/admin/users', 'ຜູ້ໃຊ້', 'M16 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9.5 10a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM21 19v-1a4 4 0 00-3-3.9M16 3.1a3.5 3.5 0 010 6.8'];
    $nav['ລະບົບ'][] = ['activity', '/admin/activity', 'ປະຫວັດການແກ້ໄຂ', 'M12 8v4l3 2M3 12a9 9 0 109-9 9 9 0 00-7 3.3M3 4v4h4'];
    $nav['ລະບົບ'][] = ['theme', '/admin/theme', 'ຕີມສີ', 'M12 3a9 9 0 100 18c.8 0 1.5-.7 1.5-1.5 0-.4-.2-.8-.4-1.1-.3-.3-.4-.6-.4-1 0-.8.7-1.5 1.5-1.5H16a5 5 0 005-5c0-4.4-4-8-9-8zM7.5 12h.01M9.5 8h.01M14.5 8h.01'];
    $nav['ລະບົບ'][] = ['settings', '/admin/settings', 'ການຕັ້ງຄ່າ', 'M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0M14 4v4M8 10v4M16 16v4'];
}
$v = static fn (string $f): string => $f . '?v=' . substr((string) @filemtime(__DIR__ . '/../../../public' . $f), -6);
?><!doctype html>
<html lang="lo"<?= App\Http\Admin\ThemeController::modeAttr() ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — XTK Admin</title>
<link rel="icon" href="/assets/img/favicon.png">
<link rel="stylesheet" href="/assets/fonts/fonts.css">
<link rel="stylesheet" href="<?= e($v('/admin/assets/admin.css')) ?>">
<?= View::render('admin/partials/theme-css') ?><script src="<?= e($v('/admin/assets/admin.js')) ?>" defer></script>
</head>
<body>
<a class="skip" href="#main">ຂ້າມໄປເນື້ອຫາ</a>
<div class="shell">
  <aside class="side" id="side">
    <a class="side__logo" href="/admin"><img src="/assets/img/logo.svg" alt="XTK Admin" width="120" height="40"></a>
    <nav aria-label="ເມນູຫຼັກ">
      <?php foreach ($nav as $group => $items): ?>
        <div class="side__group">
          <h2 class="side__label"><?= e($group) ?></h2>
          <ul>
            <?php foreach ($items as [$key, $href, $label, $icon]): ?>
              <li><a href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($icon) ?>"/></svg>
                <span><?= e($label) ?></span>
                <?php if ($key === 'messages' && $newCount > 0): ?><b class="badge" aria-label="<?= e($newCount) ?> ຂໍ້ຄວາມໃໝ່"><?= e($newCount) ?></b><?php endif; ?>
                <?php if ($key === 'publish' && $unpublished): ?><b class="badge badge--dot" title="ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່"><span class="sr">ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່</span></b><?php endif; ?>
              </a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </nav>
    <p class="side__site"><a href="/lo/index.html" target="_blank" rel="noopener"><span>ເບິ່ງໜ້າເວັບ</span> ↗</a></p>
  </aside>
  <div class="main">
    <header class="top">
      <div class="top__left">
        <button class="top__icon top__menu" type="button" aria-label="ເມນູ" aria-controls="side" aria-expanded="false" data-menu>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
      <div class="top__mid">
        <button class="top__search" type="button" aria-label="ຄົ້ນຫາ" aria-keyshortcuts="Meta+K Control+K" data-search hidden>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3"/></svg>
          <span>ຄົ້ນຫາເມນູ ແລະ ໜ້າຕ່າງໆ...</span><kbd>⌘K</kbd>
        </button>
        <button class="top__icon top__search-sm" type="button" aria-label="ຄົ້ນຫາ" data-search hidden>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3"/></svg>
        </button>
      </div>
      <?php if ($user !== null): ?>
        <div class="top__right">
          <?php if ($unpublished): ?>
            <a class="pill pill--warn" href="/admin/publish" aria-label="ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່">
              <span class="pill__ping" aria-hidden="true"></span>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>ລໍຖ້າເຜີຍແຜ່
            </a>
          <?php endif; ?>
          <a class="pill pill--site" href="/lo/index.html" target="_blank" rel="noopener" aria-label="ເບິ່ງໜ້າເວັບ (ແຖບໃໝ່)">
            <span class="pill__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg><i aria-hidden="true"></i></span>
            <span>ເບິ່ງໜ້າເວັບ</span>
            <svg class="pill__ext" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>
          </a>
          <span class="top__sep" aria-hidden="true"></span>
          <a class="top__icon top__bell" href="/admin/messages" aria-label="ກ່ອງຂໍ້ຄວາມ<?= $newCount > 0 ? ', ຍັງບໍ່ໄດ້ອ່ານ ' . e($newCount) : '' ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.3 21a2 2 0 003.4 0M3.3 15.3A1 1 0 004 17h16a1 1 0 00.7-1.7C19.4 14 18 12.5 18 8A6 6 0 006 8c0 4.5-1.4 6-2.7 7.3"/></svg>
            <?php if ($newCount > 0): ?><b aria-hidden="true"><?= e($newCount > 99 ? '99+' : $newCount) ?></b><?php endif; ?>
          </a>
          <span class="top__sep" aria-hidden="true"></span>
          <details class="top__user">
            <summary aria-label="<?= e($user['name']) ?>">
              <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr(trim($user['name']), 0, 1))) ?></span>
              <span class="top__who"><b><?= e($user['name']) ?></b><small><?= e(UserController::ROLE_NAMES[$user['role']] ?? $user['role']) ?></small></span>
            </summary>
            <div class="top__menu-pop">
              <p class="top__menu-head"><b><?= e($user['name']) ?></b><small><?= e(UserController::ROLE_NAMES[$user['role']] ?? $user['role']) ?></small></p>
              <a href="/admin/account"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 21v-1a6 6 0 0112 0v1"/></svg>ບັນຊີຂອງຂ້ອຍ</a>
              <?php $mode = $_COOKIE['xtk_mode'] ?? ''; ?>
              <form class="top__mode" method="post" action="/admin/mode" data-mode-form>
                <?= Csrf::field() ?><input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/admin') ?>">
                <span class="top__mode-label" id="mode-label">ຮູບແບບສີ</span>
                <span class="top__mode-seg" role="group" aria-labelledby="mode-label">
                  <?php foreach (['light' => ['ສະຫວ່າງ', 'M12 17a5 5 0 100-10 5 5 0 000 10zM12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4'], 'dark' => ['ມືດ', 'M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z'], 'auto' => ['ຕາມລະບົບ', 'M3 5h18v11H3zM8 21h8M12 16v5']] as $m => [$label, $icon]): ?>
                    <button type="submit" name="mode" value="<?= $m ?>" title="<?= e($label) ?>" aria-pressed="<?= ($mode === $m || ($m === 'auto' && !in_array($mode, ['light', 'dark'], true))) ? 'true' : 'false' ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $icon ?>"/></svg><span><?= e($label) ?></span></button>
                  <?php endforeach; ?>
                </span>
              </form>
              <form method="post" action="/admin/logout"><?= Csrf::field() ?><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>ອອກຈາກລະບົບ</button></form>
            </div>
          </details>
        </div>
      <?php endif; ?>
    </header>
    <dialog class="cmd" id="cmd" aria-label="ຄົ້ນຫາ">
      <div class="cmd__bar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3"/></svg>
        <input type="search" placeholder="ພິມເພື່ອຄົ້ນຫາເມນູ..." aria-label="ຄົ້ນຫາເມນູ" autocomplete="off" data-cmd-input>
        <kbd>Esc</kbd>
      </div>
      <ul class="cmd__list" role="listbox" data-cmd-list></ul>
      <p class="cmd__empty" data-cmd-empty hidden>ບໍ່ພົບເມນູນີ້ — ລອງພິມ “ສິນຄ້າ” ຫຼື “ຂ່າວ”</p>
    </dialog>
    <div class="crumbs">
      <button class="crumbs__toggle" type="button" aria-label="ຫຍໍ້ແຖບຂ້າງ" aria-keyshortcuts="Meta+B Control+B" aria-controls="side" aria-pressed="false" data-collapse hidden>
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path class="crumbs__arrow" d="M16 15l-3-3 3-3"/></svg>
      </button>
      <nav aria-label="Breadcrumb">
        <a href="/admin" aria-label="ໜ້າຫຼັກ"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 21v-8a1 1 0 00-1-1h-4a1 1 0 00-1 1v8"/><path d="M3 10a2 2 0 01.7-1.5l7-6a2 2 0 012.6 0l7 6A2 2 0 0121 10v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></a>
        <svg class="crumbs__sep" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
        <span aria-current="page"><?= e($title) ?></span>
      </nav>
    </div>
    <main id="main" tabindex="-1">
      <?php if (empty($ownHeader)): // the dashboard, the inbox, divisions, groups and products draw their own header ?><h1 class="page-title"><?= e($title) ?></h1><?php endif; ?>
      <?php foreach ($flash as $f): ?>
        <p class="flash flash--<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['text']) ?></p>
      <?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
</div>
</body>
</html>
