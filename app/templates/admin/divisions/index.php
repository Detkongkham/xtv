<?php
/** @var array $rows all divisions in order @var array $filters show ('' | visible | hidden), q */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;

// lucide icon paths, drawn by $i()
$icons = [
    'layers' => 'M12.83 2.18a2 2 0 00-1.66 0L2.6 6.08a1 1 0 000 1.83l8.58 3.91a2 2 0 001.66 0l8.58-3.9a1 1 0 000-1.83zM2 12a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 12M2 17a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 17',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'folder' => 'M20 20a2 2 0 002-2V8a2 2 0 00-2-2h-7.9a2 2 0 01-1.69-.9L9.6 3.9A2 2 0 007.93 3H4a2 2 0 00-2 2v13a2 2 0 002 2z',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'file' => 'M15 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7zM14 2v4a2 2 0 002 2h4',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$siteIcons = require App\Core\App::path('templates/site/partials/icons.php');
$n = static fn (int $v): string => number_format($v);

$show = $filters['show'];
$q = $filters['q'];
$link = static fn (array $extra): string => '/admin/divisions' . (($s = http_build_query(array_filter(array_merge(['show' => $show, 'q' => $q], $extra), static fn ($v) => $v !== null && $v !== ''))) !== '' ? '?' . $s : '');

$visible = count(array_filter($rows, static fn (array $r): bool => (int) $r['is_visible'] === 1));
$hidden = count($rows) - $visible;
$groups = array_sum(array_column($rows, 'group_count'));
$products = array_sum(array_column($rows, 'product_count'));
$todo = count(array_filter($rows, static fn (array $r): bool => !$r['image_path'] || (int) $r['group_count'] === 0));

// the order buttons move a row among all divisions, so they are only shown on the unfiltered list
$filtered = $show !== '' || $q !== '';
$last = count($rows) - 1;
$list = [];
foreach ($rows as $pos => $r) {
    $on = (int) $r['is_visible'] === 1;
    if (($show === 'visible' && !$on) || ($show === 'hidden' && $on)) {
        continue;
    }
    if ($q !== '' && mb_stripos($r['name_lo'] . ' ' . $r['name_en'] . ' ' . $r['slug'], $q) === false) {
        continue;
    }
    $list[] = $r + ['pos' => $pos];
}
$tabs = ['' => ['ທັງໝົດ', count($rows)], 'visible' => ['ສະແດງ', $visible], 'hidden' => ['ເຊື່ອງໄວ້', $hidden]];

$stat = static function (string $toneName, string $icon, string $label, string $value, string $note, ?string $href = null, bool $on = false) use ($i): string {
    $inner = '<span class="ibx-stat__bar" aria-hidden="true"></span><span class="ibx-stat__ico">' . $i($icon) . '</span>'
        . '<span class="ibx-stat__text"><span class="ibx-stat__label">' . e($label) . '</span><b class="ibx-stat__value">' . e($value) . '</b>'
        . '<span class="ibx-stat__note">' . e($note) . '</span></span>';
    $cls = 'ibx-stat ibx-stat--' . $toneName;
    return $href === null
        ? '<div class="' . $cls . '">' . $inner . '</div>'
        : '<a class="' . $cls . '" href="' . e($href) . '"' . ($on ? ' aria-current="true"' : '') . '>' . $inner . $i('sliders', 'ibx-stat__go') . '</a>';
};
?>
<div class="dash ibx dv">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <h1>ຂະແໜງທຸລະກິດ</h1>
        <p class="dash-head__meta">ຂະແໜງແຕ່ລະອັນມີໜ້າເວັບຂອງຕົນເອງ ແລະ ລວມກຸ່ມສິນຄ້າໄວ້ — ຈັດລຳດັບ, ເປີດ/ປິດການສະແດງ ແລະ ແກ້ໄຂ</p>
      </div>
      <div class="dash-head__tools">
        <a class="btn-soft" href="/admin/groups"><?= $i('folder') ?><span>ກຸ່ມສິນຄ້າ</span></a>
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
        <a class="btn btn--sm" href="/admin/divisions/new"><?= $i('plus') ?>ເພີ່ມຂະແໜງ</a>
      </div>
    </div>
  </header>

  <div class="ibx-stats">
    <?= $stat('primary', 'layers', 'ຂະແໜງທັງໝົດ', $n(count($rows)), 'ລຽງຕາມລຳດັບໃນເມນູ', $filtered ? '/admin/divisions' : null) ?>
    <?= $stat('success', 'eye', 'ສະແດງໃນເວັບ', $n($visible), 'ຢູ່ໃນເມນູ ແລະ ໜ້າຫຼັກ', $link(['show' => $show === 'visible' ? null : 'visible']), $show === 'visible') ?>
    <?= $stat('warning', 'eye-off', 'ເຊື່ອງໄວ້', $n($hidden), 'ບໍ່ມີໜ້າ, ບໍ່ຢູ່ໃນເມນູ', $link(['show' => $show === 'hidden' ? null : 'hidden']), $show === 'hidden') ?>
    <?= $stat('info', 'folder', 'ກຸ່ມສິນຄ້າ', $n($groups), 'ໃນທຸກຂະແໜງ', '/admin/groups') ?>
    <?= $stat($todo > 0 ? 'danger' : 'accent', $todo > 0 ? 'alert' : 'box', $todo > 0 ? 'ຕ້ອງກວດ' : 'ສິນຄ້າ', $todo > 0 ? $n($todo) : $n($products), $todo > 0 ? 'ຂາດຮູບ ຫຼື ຍັງບໍ່ມີກຸ່ມ' : 'ໃນທຸກກຸ່ມ', $todo > 0 ? null : '/admin/products') ?>
  </div>

  <section class="ibx-box" aria-labelledby="dv-heading">
    <h2 id="dv-heading" class="sr">ລາຍການຂະແໜງ</h2>
    <form class="ibx-tools" method="get" action="/admin/divisions" role="search">
      <?php if ($show !== ''): ?><input type="hidden" name="show" value="<?= e($show) ?>"><?php endif; ?>
      <div class="ibx-tools__row">
        <nav class="ibx-tabs" aria-label="ການສະແດງ">
          <?php foreach ($tabs as $k => [$label, $count]): ?>
            <a href="<?= e($link(['show' => $k === '' ? null : $k])) ?>"<?= $show === (string) $k ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="num"><?= e($n($count)) ?></span></a>
          <?php endforeach; ?>
        </nav>
        <div class="ibx-search">
          <?= $i('search') ?>
          <input type="search" name="q" value="<?= e($q) ?>" placeholder="ຄົ້ນຫາຊື່ ຫຼື ໜ້າເວັບ…" aria-label="ຄົ້ນຫາຂະແໜງ" aria-keyshortcuts="/" data-slash-focus>
          <kbd aria-hidden="true">/</kbd>
        </div>
      </div>
    </form>

    <div class="ibx-bulk">
      <span class="ibx-bulk__info">
        ສະແດງ <?= e($n(count($list))) ?> ຈາກ <?= e($n(count($rows))) ?>
        <?php if ($filtered): ?> · ລ້າງຕົວກອງເພື່ອຈັດລຳດັບ · <a href="/admin/divisions">ລ້າງຕົວກອງ</a><?php else: ?> · ລຳດັບນີ້ຄືລຳດັບໃນເມນູ ແລະ ໜ້າເວັບ<?php endif; ?>
      </span>
    </div>

    <?php if ($list === []): ?>
      <div class="ibx-empty">
        <span class="ibx-empty__ico"><?= $i('layers') ?></span>
        <?php if ($rows === []): ?>
          <p><b>ຍັງບໍ່ມີຂະແໜງ</b><br><span class="muted">ເພີ່ມຂະແໜງທຳອິດ ແລ້ວຈຶ່ງເພີ່ມກຸ່ມສິນຄ້າໃສ່.</span></p>
          <a class="btn btn--sm" href="/admin/divisions/new"><?= $i('plus') ?>ເພີ່ມຂະແໜງ</a>
        <?php else: ?>
          <p><b>ບໍ່ພົບຂະແໜງ</b><br><span class="muted">ລອງປ່ຽນຕົວກອງ ຫຼື ຄຳຄົ້ນຫາ.</span></p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <ul class="ibx-list">
        <?php foreach ($list as $r):
            $id = (int) $r['id'];
            $on = (int) $r['is_visible'] === 1;
            $groupCount = (int) $r['group_count'];
        ?>
          <li class="ibx-row dv-row ibx-row--<?= $on ? 'success' : 'muted' ?><?= $on ? '' : ' is-hidden' ?>" id="d<?= e($id) ?>">
            <div class="dv-order">
              <span class="dv-order__pos num" aria-hidden="true"><?= e(str_pad((string) ($r['pos'] + 1), 2, '0', STR_PAD_LEFT)) ?></span>
              <?php if (!$filtered): ?><?= View::render('admin/partials/order-buttons', ['action' => '/admin/divisions/' . $id . '/move', 'label' => $r['name_lo'], 'first' => $r['pos'] === 0, 'last' => $r['pos'] === $last]) ?><?php endif; ?>
            </div>
            <a class="ibx-row__main" href="/admin/divisions/<?= e($id) ?>">
              <span class="dv-thumb">
                <?php if ($r['image_path']): ?>
                  <img src="<?= e(MC::thumb(['path' => $r['image_path'], 'variants' => $r['image_variants']])) ?>" alt="" loading="lazy" width="80" height="60">
                <?php endif; ?>
                <span class="dv-thumb__ico"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($siteIcons[$r['icon']] ?? $icons['box']) ?>"/></svg></span>
              </span>
              <span class="ibx-row__text">
                <span class="ibx-row__top"><b title="<?= e($r['name_lo']) ?>"><?= e($r['name_lo']) ?></b></span>
                <span class="ibx-row__body"><?= e($r['name_en']) ?></span>
                <span class="ibx-row__meta">
                  <span class="ibx-status"><i aria-hidden="true"></i><?= $on ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                  <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('file') ?><code><?= e($r['slug']) ?>.html</code></span>
                  <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('folder') ?><?= e($n($groupCount)) ?> ກຸ່ມ</span>
                  <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('box') ?><?= e($n((int) $r['product_count'])) ?> ສິນຄ້າ</span>
                  <?php if (!$r['image_path']): ?><span class="tag tag--warn">ບໍ່ມີຮູບ</span><?php endif; ?>
                  <?php if ($groupCount === 0): ?><span class="tag tag--warn">ຍັງບໍ່ມີກຸ່ມ</span><?php endif; ?>
                </span>
              </span>
            </a>
            <div class="dv-acts">
              <form method="post" action="/admin/divisions/<?= e($id) ?>/toggle"><?= Csrf::field() ?>
                <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $r['name_lo']) ?>" title="<?= $on ? 'ກົດເພື່ອເຊື່ອງ' : 'ກົດເພື່ອສະແດງ' ?>"><span></span></button>
              </form>
              <a class="ibx-icon-btn" href="/admin/groups#div<?= e($id) ?>" aria-label="<?= e('ກຸ່ມສິນຄ້າຂອງ ' . $r['name_lo']) ?>" title="ກຸ່ມສິນຄ້າ"><?= $i('folder') ?></a>
              <a class="ibx-icon-btn" href="/admin/divisions/<?= e($id) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $r['name_lo']) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <p class="dv-foot">ຂະແໜງທີ່ເຊື່ອງໄວ້ ບໍ່ມີໜ້າຂອງຕົນເອງ ແລະ ບໍ່ປາກົດໃນເມນູ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
  </section>
</div>
