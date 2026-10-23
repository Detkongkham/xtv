<?php
/** @var array $byDivision division id => groups in order @var array $divisions all divisions in order @var array $filters show ('' | visible | hidden | empty), division, q */
use App\Core\Csrf;
use App\Core\View;

// lucide icon paths, drawn by $i()
$icons = [
    'folder' => 'M20 20a2 2 0 002-2V8a2 2 0 00-2-2h-7.9a2 2 0 01-1.69-.9L9.6 3.9A2 2 0 007.93 3H4a2 2 0 00-2 2v13a2 2 0 002 2z',
    'layers' => 'M12.83 2.18a2 2 0 00-1.66 0L2.6 6.08a1 1 0 000 1.83l8.58 3.91a2 2 0 001.66 0l8.58-3.9a1 1 0 000-1.83zM2 12a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 12M2 17a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 17',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'hash' => 'M4 9h16M4 15h16M10 3L8 21M16 3l-2 18',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$siteIcons = require App\Core\App::path('templates/site/partials/icons.php');
$n = static fn (int $v): string => number_format($v);

$show = $filters['show'];
$q = $filters['q'];
$divFilter = in_array($filters['division'], array_map('intval', array_column($divisions, 'id')), true) ? $filters['division'] : 0;
$link = static fn (array $extra): string => '/admin/groups' . (($s = http_build_query(array_filter(array_merge(['show' => $show, 'division' => $divFilter ?: null, 'q' => $q], $extra), static fn ($v) => $v !== null && $v !== ''))) !== '' ? '?' . $s : '');

$all = array_merge([], ...array_values($byDivision));
$visible = count(array_filter($all, static fn (array $g): bool => (int) $g['is_visible'] === 1));
$hidden = count($all) - $visible;
$empty = count(array_filter($all, static fn (array $g): bool => (int) $g['product_count'] === 0));
$products = array_sum(array_column($all, 'product_count'));

// the order buttons move a group within its division, so they are hidden while show or q leave rows out
$filtered = $show !== '' || $q !== '';
$sections = [];
$shown = 0;
foreach ($divisions as $d) {
    $divId = (int) $d['id'];
    if ($divFilter !== 0 && $divId !== $divFilter) {
        continue;
    }
    $groups = $byDivision[$divId] ?? [];
    $list = [];
    foreach ($groups as $pos => $g) {
        $on = (int) $g['is_visible'] === 1;
        if (($show === 'visible' && !$on) || ($show === 'hidden' && $on) || ($show === 'empty' && (int) $g['product_count'] > 0)) {
            continue;
        }
        if ($q !== '' && mb_stripos($g['name_lo'] . ' ' . $g['name_en'] . ' ' . $g['code'], $q) === false) {
            continue;
        }
        $list[] = $g + ['pos' => $pos];
    }
    // while filtering, divisions with no match are left out; unfiltered, an empty division still shows its add button
    if ($filtered && $list === []) {
        continue;
    }
    $sections[] = ['d' => $d, 'list' => $list, 'last' => count($groups) - 1];
    $shown += count($list);
}
$tabs = ['' => ['ທັງໝົດ', count($all)], 'visible' => ['ສະແດງ', $visible], 'hidden' => ['ເຊື່ອງໄວ້', $hidden], 'empty' => ['ບໍ່ມີສິນຄ້າ', $empty]];
$colors = ['c-1', 'c-2', 'c-3', 'c-4', 'c-5'];

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
<div class="dash ibx dv gp">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <h1>ກຸ່ມສິນຄ້າ</h1>
        <p class="dash-head__meta">ກຸ່ມສິນຄ້າແຍກຕາມຂະແໜງ ແລະ ເປັນຫົວຂໍ້ໃນໜ້າຂະແໜງ — ຈັດລຳດັບ, ເປີດ/ປິດການສະແດງ ແລະ ແກ້ໄຂ</p>
      </div>
      <div class="dash-head__tools">
        <a class="btn-soft" href="/admin/divisions"><?= $i('layers') ?><span>ຂະແໜງທຸລະກິດ</span></a>
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
        <a class="btn btn--sm" href="/admin/groups/new<?= $divFilter ? '?division=' . e($divFilter) : '' ?>"><?= $i('plus') ?>ເພີ່ມກຸ່ມ</a>
      </div>
    </div>
  </header>

  <div class="ibx-stats">
    <?= $stat('primary', 'folder', 'ກຸ່ມທັງໝົດ', $n(count($all)), 'ໃນ ' . $n(count($divisions)) . ' ຂະແໜງ', $filtered || $divFilter ? '/admin/groups' : null) ?>
    <?= $stat('success', 'eye', 'ສະແດງໃນເວັບ', $n($visible), 'ເປັນຫົວຂໍ້ໃນໜ້າຂະແໜງ', $link(['show' => $show === 'visible' ? null : 'visible']), $show === 'visible') ?>
    <?= $stat('warning', 'eye-off', 'ເຊື່ອງໄວ້', $n($hidden), 'ສິນຄ້າໃນກຸ່ມບໍ່ປາກົດ', $link(['show' => $show === 'hidden' ? null : 'hidden']), $show === 'hidden') ?>
    <?= $stat('info', 'box', 'ສິນຄ້າ', $n($products), 'ໃນທຸກກຸ່ມ', '/admin/products') ?>
    <?= $empty > 0
        ? $stat('danger', 'alert', 'ບໍ່ມີສິນຄ້າ', $n($empty), 'ກຸ່ມທີ່ຍັງວ່າງຢູ່', $link(['show' => $show === 'empty' ? null : 'empty']), $show === 'empty')
        : $stat('accent', 'layers', 'ຂະແໜງ', $n(count($divisions)), 'ທຸກກຸ່ມມີສິນຄ້າແລ້ວ', '/admin/divisions') ?>
  </div>

  <section class="ibx-box" aria-labelledby="gp-heading">
    <h2 id="gp-heading" class="sr">ລາຍການກຸ່ມສິນຄ້າ</h2>
    <form class="ibx-tools" method="get" action="/admin/groups" role="search">
      <?php if ($show !== ''): ?><input type="hidden" name="show" value="<?= e($show) ?>"><?php endif; ?>
      <?php if ($divFilter): ?><input type="hidden" name="division" value="<?= e($divFilter) ?>"><?php endif; ?>
      <div class="ibx-tools__row">
        <nav class="ibx-tabs" aria-label="ການສະແດງ">
          <?php foreach ($tabs as $k => [$label, $count]): ?>
            <a href="<?= e($link(['show' => $k === '' ? null : $k])) ?>"<?= $show === (string) $k ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="num"><?= e($n($count)) ?></span></a>
          <?php endforeach; ?>
        </nav>
        <div class="ibx-search">
          <?= $i('search') ?>
          <input type="search" name="q" value="<?= e($q) ?>" placeholder="ຄົ້ນຫາຊື່ ຫຼື anchor…" aria-label="ຄົ້ນຫາກຸ່ມ" aria-keyshortcuts="/" data-slash-focus>
          <kbd aria-hidden="true">/</kbd>
        </div>
      </div>
      <?php if (count($divisions) > 1): ?>
        <div class="ibx-tools__row">
          <?php foreach (array_values($divisions) as $k => $d): $id = (int) $d['id']; $on = $divFilter === $id; ?>
            <a class="ibx-chip <?= e($colors[$k % count($colors)]) ?>" href="<?= e($link(['division' => $on ? null : $id])) ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>" role="button">
              <i aria-hidden="true"></i><?= e($d['name_lo']) ?><span class="num"><?= e($n(count($byDivision[$id] ?? []))) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </form>

    <div class="ibx-bulk">
      <span class="ibx-bulk__info">
        ສະແດງ <?= e($n($shown)) ?> ຈາກ <?= e($n(count($all))) ?>
        <?php if ($filtered): ?> · ລ້າງຕົວກອງເພື່ອຈັດລຳດັບ · <a href="<?= e($divFilter ? '/admin/groups?division=' . $divFilter : '/admin/groups') ?>">ລ້າງຕົວກອງ</a><?php else: ?> · ລຳດັບນີ້ຄືລຳດັບໃນໜ້າຂະແໜງ<?php endif; ?>
      </span>
    </div>

    <?php if ($sections === []): ?>
      <div class="ibx-empty">
        <span class="ibx-empty__ico"><?= $i('folder') ?></span>
        <?php if ($divisions === []): ?>
          <p><b>ຍັງບໍ່ມີຂະແໜງ</b><br><span class="muted">ກຸ່ມສິນຄ້າຢູ່ໃນຂະແໜງ, ສະນັ້ນເພີ່ມຂະແໜງກ່ອນ.</span></p>
          <a class="btn btn--sm" href="/admin/divisions/new"><?= $i('plus') ?>ເພີ່ມຂະແໜງ</a>
        <?php else: ?>
          <p><b>ບໍ່ພົບກຸ່ມ</b><br><span class="muted">ລອງປ່ຽນຕົວກອງ ຫຼື ຄຳຄົ້ນຫາ.</span></p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php foreach ($sections as ['d' => $d, 'list' => $list, 'last' => $last]):
          $divId = (int) $d['id'];
          $divOn = (int) $d['is_visible'] === 1;
      ?>
        <div class="gp-div" id="div<?= e($divId) ?>">
          <span class="gp-div__ico"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($siteIcons[$d['icon']] ?? $icons['layers']) ?>"/></svg></span>
          <h3><a href="/admin/divisions/<?= e($divId) ?>"><?= e($d['name_lo']) ?></a></h3>
          <span class="gp-div__count num"><?= e($n(count($byDivision[$divId] ?? []))) ?> ກຸ່ມ</span>
          <?php if (!$divOn): ?><span class="tag tag--warn" title="ກຸ່ມໃນຂະແໜງນີ້ຈະບໍ່ປາກົດ ຈົນກວ່າຈະເປີດຂະແໜງ">ຂະແໜງເຊື່ອງໄວ້</span><?php endif; ?>
          <a class="gp-div__add" href="/admin/groups/new?division=<?= e($divId) ?>" aria-label="<?= e('ເພີ່ມກຸ່ມໃນ ' . $d['name_lo']) ?>"><?= $i('plus') ?><span>ເພີ່ມກຸ່ມ</span></a>
        </div>
        <?php if ($list === []): ?>
          <p class="gp-none">ຍັງບໍ່ມີກຸ່ມໃນຂະແໜງນີ້.</p>
        <?php else: ?>
          <ul class="ibx-list" aria-label="<?= e('ກຸ່ມໃນ ' . $d['name_lo']) ?>">
            <?php foreach ($list as $g):
                $id = (int) $g['id'];
                $on = (int) $g['is_visible'] === 1;
                $count = (int) $g['product_count'];
            ?>
              <li class="ibx-row dv-row ibx-row--<?= $on ? 'success' : 'muted' ?><?= $on ? '' : ' is-hidden' ?>" id="g<?= e($id) ?>">
                <div class="dv-order">
                  <span class="dv-order__pos num" aria-hidden="true"><?= e(str_pad((string) ($g['pos'] + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                  <?php if (!$filtered): ?><?= View::render('admin/partials/order-buttons', ['action' => '/admin/groups/' . $id . '/move', 'label' => $g['name_lo'], 'first' => $g['pos'] === 0, 'last' => $g['pos'] === $last]) ?><?php endif; ?>
                </div>
                <a class="ibx-row__main" href="/admin/groups/<?= e($id) ?>">
                  <span class="dv-thumb"><span class="dv-thumb__ico"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($siteIcons[$g['icon']] ?? $icons['box']) ?>"/></svg></span></span>
                  <span class="ibx-row__text">
                    <span class="ibx-row__top"><b title="<?= e($g['name_lo']) ?>"><?= e($g['name_lo']) ?></b></span>
                    <span class="ibx-row__body"><?= e($g['name_en']) ?></span>
                    <span class="ibx-row__meta">
                      <span class="ibx-status"><i aria-hidden="true"></i><?= $on ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                      <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('hash') ?><code><?= e($g['code']) ?></code></span>
                      <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('box') ?><?= e($n($count)) ?> ສິນຄ້າ</span>
                      <?php if ($count === 0): ?><span class="tag tag--warn">ບໍ່ມີສິນຄ້າ</span><?php endif; ?>
                    </span>
                  </span>
                </a>
                <div class="dv-acts">
                  <form method="post" action="/admin/groups/<?= e($id) ?>/toggle"><?= Csrf::field() ?>
                    <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $g['name_lo']) ?>" title="<?= $on ? 'ກົດເພື່ອເຊື່ອງ' : 'ກົດເພື່ອສະແດງ' ?>"><span></span></button>
                  </form>
                  <a class="ibx-icon-btn" href="/admin/products?group=<?= e($id) ?>" aria-label="<?= e('ສິນຄ້າໃນ ' . $g['name_lo']) ?>" title="ສິນຄ້າ"><?= $i('box') ?></a>
                  <a class="ibx-icon-btn" href="/admin/groups/<?= e($id) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $g['name_lo']) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <p class="dv-foot">ສິນຄ້າໃນກຸ່ມທີ່ເຊື່ອງໄວ້ ຈະບໍ່ປາກົດໃນໜ້າເວັບ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
  </section>
</div>
