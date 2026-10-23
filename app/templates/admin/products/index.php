<?php
/**
 * @var array $rows @var int $total @var int $page @var int $pages @var array $filters q, division, group, show
 * @var array $groups see ProductRepository::groupCounts() @var array $divisions id => name @var array $counts see ProductRepository::counts()
 */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;
use App\Repository\ProductRepository;

// lucide icon paths, drawn by $i()
$icons = [
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'star' => 'M11.53 2.3a.53.53 0 01.94 0l2.31 4.68a2.12 2.12 0 001.6 1.16l5.16.76a.53.53 0 01.3.9l-3.74 3.64a2.12 2.12 0 00-.61 1.88l.88 5.14a.53.53 0 01-.77.56l-4.62-2.43a2.12 2.12 0 00-1.97 0L6.4 21.01a.53.53 0 01-.77-.56l.88-5.14a2.12 2.12 0 00-.61-1.88L2.16 9.79a.53.53 0 01.3-.9l5.16-.76a2.12 2.12 0 001.6-1.16z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'tag' => 'M12.59 2.59A2 2 0 0011.17 2H4a2 2 0 00-2 2v7.17a2 2 0 00.59 1.42l8.7 8.7a2.43 2.43 0 003.42 0l6.58-6.58a2.43 2.43 0 000-3.42zM7.5 8a.5.5 0 100-1 .5.5 0 000 1z',
    'truck' => 'M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2M15 18H9M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.62l-3.48-4.35A1 1 0 0017.52 8H14M17 20a2 2 0 100-4 2 2 0 000 4zM7 20a2 2 0 100-4 2 2 0 000 4z',
    'folder' => 'M20 20a2 2 0 002-2V8a2 2 0 00-2-2h-7.9a2 2 0 01-1.69-.9L9.6 3.9A2 2 0 007.93 3H4a2 2 0 00-2 2v13a2 2 0 002 2z',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'chevron' => 'M6 9l6 6 6-6',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'file' => 'M15 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7zM14 2v4a2 2 0 002 2h4',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);

$show = $filters['show'];
// a chosen group also picks its division, so the chips and the group list stay in step
$groupDivision = array_column($groups, 'division_id', 'id');
$division = $filters['group'] > 0 ? (int) ($groupDivision[$filters['group']] ?? 0) : $filters['division'];
$query = array_filter(['q' => $filters['q'], 'division' => $division, 'group' => $filters['group'], 'show' => $show], static fn ($v) => $v !== '' && $v !== 0);
$link = static fn (array $extra): string => '/admin/products' . (($s = http_build_query(array_filter(array_merge($query, $extra), static fn ($v) => $v !== null))) !== '' ? '?' . $s : '');
$here = $link($page > 1 ? ['page' => $page] : []);
$featuredFull = $counts['featured'] >= ProductRepository::MAX_FEATURED;
$canOrder = $filters['group'] > 0 && $filters['q'] === '' && $show === '';
$last = count($rows) - 1;

$perDivision = [];
foreach ($groups as $g) {
    $perDivision[(int) $g['division_id']] = ($perDivision[(int) $g['division_id']] ?? 0) + (int) $g['n'];
}
$tabs = ['' => ['ທັງໝົດ', $counts['all']], 'featured' => ['ແນະນຳ', $counts['featured']], 'hidden' => ['ເຊື່ອງໄວ້', $counts['hidden']], 'noimage' => ['ບໍ່ມີຮູບ', $counts['noimage']], 'nosupplier' => ['ບໍ່ມີຜູ້ສະໜອງ', $counts['nosupplier']]];
$toggle = static fn (string $k): string => $link(['show' => $show === $k ? null : $k, 'page' => null]);

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
<div class="dash ibx dv pd">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <h1>ສິນຄ້າ</h1>
        <p class="dash-head__meta">ສິນຄ້າທີ່ມີຮູບມີໜ້າເວັບຂອງຕົນເອງ, ບໍ່ມີຮູບເປັນກາດ “ສອບຖາມລາຄາ” — ຄົ້ນຫາ, ຈັດລຳດັບ, ເລືອກສິນຄ້າແນະນຳ ແລະ ແກ້ໄຂ</p>
      </div>
      <div class="dash-head__tools">
        <a class="btn-soft" href="/admin/groups"><?= $i('folder') ?><span>ກຸ່ມສິນຄ້າ</span></a>
        <a class="btn-soft" href="/admin/suppliers"><?= $i('truck') ?><span>ຜູ້ສະໜອງ</span></a>
        <a class="btn btn--sm" href="/admin/products/new<?= $filters['group'] ? '?group=' . e($filters['group']) : '' ?>"><?= $i('plus') ?>ເພີ່ມສິນຄ້າ</a>
      </div>
    </div>
  </header>

  <div class="ibx-stats">
    <?= $stat('primary', 'box', 'ສິນຄ້າທັງໝົດ', $n($counts['all']), 'ໃນ ' . $n(count($groups)) . ' ກຸ່ມ', $query !== [] ? '/admin/products' : null) ?>
    <?= $stat('accent', 'star', 'ສິນຄ້າແນະນຳ', $n($counts['featured']) . ' / ' . ProductRepository::MAX_FEATURED, $featuredFull ? 'ເຕັມແລ້ວ — ປິດອັນອື່ນກ່ອນ' : 'ສະແດງໃນໜ້າຫຼັກ', $toggle('featured'), $show === 'featured') ?>
    <?= $stat('warning', 'eye-off', 'ເຊື່ອງໄວ້', $n($counts['hidden']), 'ບໍ່ສະແດງໃນໜ້າເວັບ', $toggle('hidden'), $show === 'hidden') ?>
    <?= $stat('info', 'tag', 'ສອບຖາມລາຄາ', $n($counts['noimage']), 'ບໍ່ມີຮູບ, ບໍ່ມີໜ້າຂອງຕົນເອງ', $toggle('noimage'), $show === 'noimage') ?>
    <?= $stat($counts['nosupplier'] > 0 ? 'danger' : 'success', 'truck', 'ບໍ່ມີຜູ້ສະໜອງ', $n($counts['nosupplier']), $counts['nosupplier'] > 0 ? 'ຕ້ອງເພີ່ມຜູ້ສະໜອງ' : 'ທຸກສິນຄ້າມີແລ້ວ', $counts['nosupplier'] > 0 ? $toggle('nosupplier') : null, $show === 'nosupplier') ?>
  </div>

  <section class="ibx-box" aria-labelledby="pd-heading">
    <h2 id="pd-heading" class="sr">ລາຍການສິນຄ້າ</h2>
    <form class="ibx-tools" method="get" action="/admin/products" role="search">
      <?php if ($show !== ''): ?><input type="hidden" name="show" value="<?= e($show) ?>"><?php endif; ?>
      <?php if ($division > 0): ?><input type="hidden" name="division" value="<?= e($division) ?>"><?php endif; ?>
      <div class="ibx-tools__row">
        <nav class="ibx-tabs" aria-label="ກອງສິນຄ້າ">
          <?php foreach ($tabs as $k => [$label, $count]): ?>
            <a href="<?= e($link(['show' => $k === '' ? null : $k, 'page' => null])) ?>"<?= $show === (string) $k ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="num"><?= e($n($count)) ?></span></a>
          <?php endforeach; ?>
        </nav>
        <div class="ibx-search">
          <?= $i('search') ?>
          <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="ຄົ້ນຫາຊື່ສິນຄ້າ…" aria-label="ຄົ້ນຫາສິນຄ້າ" aria-keyshortcuts="/" data-slash-focus>
          <kbd aria-hidden="true">/</kbd>
        </div>
      </div>
      <div class="ibx-tools__row">
        <?php $c = 0; foreach ($divisions as $id => $name): $on = $division === (int) $id; ?>
          <a class="ibx-chip c-<?= e($c++ % 5 + 1) ?>" href="<?= e($link(['division' => $on ? null : $id, 'group' => null, 'page' => null])) ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>" role="button">
            <i aria-hidden="true"></i><?= e($name) ?><span class="num"><?= e($n($perDivision[(int) $id] ?? 0)) ?></span>
          </a>
        <?php endforeach; ?>
        <div class="ibx-tools__end">
          <div class="ibx-select">
            <select name="group" aria-label="ກຸ່ມສິນຄ້າ" data-autosubmit>
              <option value="">ທຸກກຸ່ມ</option>
              <?php foreach ($divisions as $id => $name): if ($division > 0 && $division !== (int) $id) continue; ?>
                <optgroup label="<?= e($name) ?>">
                  <?php foreach ($groups as $g): if ((int) $g['division_id'] !== (int) $id) continue; ?>
                    <option value="<?= e($g['id']) ?>"<?= $filters['group'] === (int) $g['id'] ? ' selected' : '' ?>><?= e($g['name_lo'] . ' (' . $g['n'] . ')') ?></option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endforeach; ?>
            </select>
            <?= $i('chevron') ?>
          </div>
          <button class="sr" type="submit">ຄົ້ນຫາ</button>
        </div>
      </div>
    </form>

    <div class="ibx-bulk">
      <span class="ibx-bulk__info">
        ສະແດງ <?= e($n(count($rows))) ?> ຈາກ <?= e($n($total)) ?>
        · <?= $canOrder ? 'ລຳດັບນີ້ຄືລຳດັບໃນໜ້າກຸ່ມ' : ($filters['group'] > 0 ? 'ລ້າງການຄົ້ນຫາ ແລະ ຕົວກອງ ເພື່ອຈັດລຳດັບ' : 'ເລືອກກຸ່ມ ເພື່ອຈັດລຳດັບ') ?>
        <?php if ($query !== []): ?> · <a href="/admin/products">ລ້າງຕົວກອງ</a><?php endif; ?>
      </span>
    </div>

    <?php if ($rows === []): ?>
      <div class="ibx-empty">
        <span class="ibx-empty__ico"><?= $i('box') ?></span>
        <?php if ($counts['all'] === 0): ?>
          <p><b>ຍັງບໍ່ມີສິນຄ້າ</b><br><span class="muted">ເພີ່ມສິນຄ້າທຳອິດ ແລ້ວເລືອກກຸ່ມໃຫ້ມັນ.</span></p>
          <a class="btn btn--sm" href="/admin/products/new"><?= $i('plus') ?>ເພີ່ມສິນຄ້າ</a>
        <?php else: ?>
          <p><b>ບໍ່ພົບສິນຄ້າ</b><br><span class="muted">ລອງປ່ຽນຕົວກອງ ຫຼື ຄຳຄົ້ນຫາ.</span></p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <ul class="ibx-list">
        <?php foreach ($rows as $k => $r):
            $id = (int) $r['id'];
            $on = (int) $r['is_visible'] === 1;
            $featured = (int) $r['is_featured'] === 1;
        ?>
          <li class="ibx-row dv-row ibx-row--<?= $on ? ($featured ? 'accent' : 'success') : 'muted' ?><?= $on ? '' : ' is-hidden' ?>" id="p<?= e($id) ?>">
            <?php if ($canOrder): ?>
              <div class="dv-order">
                <span class="dv-order__pos num" aria-hidden="true"><?= e(str_pad((string) (($page - 1) * ProductRepository::PER_PAGE + $k + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                <?= View::render('admin/partials/order-buttons', ['action' => '/admin/products/' . $id . '/move', 'label' => $r['name'], 'first' => $k === 0, 'last' => $k === $last]) ?>
              </div>
            <?php endif; ?>
            <a class="ibx-row__main" href="/admin/products/<?= e($id) ?>">
              <span class="dv-thumb">
                <?php if ($r['image_path']): ?>
                  <img src="<?= e(MC::thumb(['path' => $r['image_path'], 'variants' => $r['image_variants']])) ?>" alt="" loading="lazy" width="80" height="60">
                <?php else: ?>
                  <span class="dv-thumb__ico" title="ສອບຖາມລາຄາ"><?= $i('tag') ?></span>
                <?php endif; ?>
              </span>
              <span class="ibx-row__text">
                <span class="ibx-row__top">
                  <b title="<?= e($r['name']) ?>"><?= e($r['name']) ?></b>
                  <?php if ($featured): ?><span class="sp-star"><?= $i('star') ?>ແນະນຳ</span><?php endif; ?>
                </span>
                <span class="ibx-row__body"><?= e($r['division_name']) ?> › <?= e($r['group_name']) ?></span>
                <span class="ibx-row__meta">
                  <span class="ibx-status"><i aria-hidden="true"></i><?= $on ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                  <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('file') ?><?= $r['image_path'] ? '<code>' . e('product-' . $r['slug'] . '.html') . '</code>' : 'ກາດສອບຖາມລາຄາ' ?></span>
                  <?php if ($r['supplier_names']): ?>
                    <span aria-hidden="true">·</span><span class="dv-meta pd-suppliers" title="<?= e($r['supplier_names']) ?>"><?= $i('truck') ?><?= e($r['supplier_names']) ?></span>
                  <?php else: ?>
                    <span class="tag tag--error">ບໍ່ມີຜູ້ສະໜອງ</span>
                  <?php endif; ?>
                </span>
              </span>
            </a>
            <div class="dv-acts sp-acts">
              <?php foreach (['is_featured' => ['ແນະນຳ', 'ສິນຄ້າແນະນຳ', $featured], 'is_visible' => ['ສະແດງ', 'ສະແດງໃນໜ້າເວັບ', $on]] as $field => [$short, $label, $checked]):
                  $blocked = $field === 'is_featured' && !$checked ? (!$r['image_path'] ? 'ສິນຄ້າແນະນຳຕ້ອງມີຮູບ' : ($featuredFull ? 'ສິນຄ້າແນະນຳມີໄດ້ສູງສຸດ ' . ProductRepository::MAX_FEATURED . ' ລາຍການ' : null)) : null; ?>
                <form class="sp-switch" method="post" action="/admin/products/<?= e($id) ?>/toggle"><?= Csrf::field() ?><input type="hidden" name="field" value="<?= $field ?>"><input type="hidden" name="back" value="<?= e($here) ?>">
                  <button class="switch" type="submit" role="switch" aria-checked="<?= $checked ? 'true' : 'false' ?>" aria-label="<?= e($label . ': ' . $r['name']) ?>" title="<?= e($blocked ?? $label) ?>"<?= $blocked !== null && !$r['image_path'] ? ' disabled' : '' ?>><span></span></button>
                  <span aria-hidden="true"><?= e($short) ?></span>
                </form>
              <?php endforeach; ?>
              <a class="ibx-icon-btn" href="/admin/products/<?= e($id) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $r['name']) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
      <nav class="ibx-pager" aria-label="ແບ່ງໜ້າ">
        <?php if ($page > 1): ?><a href="<?= e($link(['page' => $page - 1])) ?>" rel="prev">← ກ່ອນໜ້າ</a><?php endif; ?>
        <span>ໜ້າ <?= e($page) ?> / <?= e($pages) ?></span>
        <?php if ($page < $pages): ?><a href="<?= e($link(['page' => $page + 1])) ?>" rel="next">ຕໍ່ໄປ →</a><?php endif; ?>
      </nav>
    <?php endif; ?>

    <p class="dv-foot">ສິນຄ້າແນະນຳມີໄດ້ສູງສຸດ <?= ProductRepository::MAX_FEATURED ?> ລາຍການ ແລະ ຕ້ອງມີຮູບ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
  </section>
</div>
