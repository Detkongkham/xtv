<?php
/** @var array $rows all suppliers in order @var int $withoutSupplier visible products with no supplier @var array $filters show ('' | featured | visible | hidden), q */
use App\Core\Countries;
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\SupplierController as SC;
use App\Repository\SupplierRepository;

// lucide icon paths, drawn by $i()
$icons = [
    'factory' => 'M2 20a2 2 0 002 2h16a2 2 0 002-2V8l-7 5V8l-7 5V4a2 2 0 00-2-2H4a2 2 0 00-2 2zM17 18h1M12 18h1M7 18h1',
    'star' => 'M11.53 2.3a.53.53 0 01.95 0l2.31 4.68a2.12 2.12 0 001.6 1.16l5.17.76a.53.53 0 01.29.9l-3.74 3.64a2.12 2.12 0 00-.61 1.88l.88 5.14a.53.53 0 01-.77.56l-4.62-2.43a2.12 2.12 0 00-1.97 0L6.4 21.01a.53.53 0 01-.77-.56l.88-5.14a2.12 2.12 0 00-.61-1.88L2.16 9.79a.53.53 0 01.29-.9l5.17-.76a2.12 2.12 0 001.6-1.16z',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'globe' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a14.5 14.5 0 000 20 14.5 14.5 0 000-20',
    'handshake' => 'M11 17l2 2a1 1 0 103-3M14 14l2.5 2.5a1 1 0 103-3l-3.88-3.88a3 3 0 00-4.24 0l-.88.88a1 1 0 11-3-3l2.81-2.81a5.79 5.79 0 017.06-.87l.47.28a2 2 0 001.42.25L21 4M21 3l1 11h-2M3 3L2 14l6.5 6.5a1 1 0 103-3M3 4h8',
    'calendar' => 'M8 2v4M16 2v4M3 6a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2zM3 10h18',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'external' => 'M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'image' => 'M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zM9 11a2 2 0 100-4 2 2 0 000 4zM21 15l-3.09-3.09a2 2 0 00-2.82 0L6 21',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);

$show = $filters['show'];
$q = $filters['q'];
$link = static fn (array $extra): string => '/admin/suppliers' . (($s = http_build_query(array_filter(array_merge(['show' => $show, 'q' => $q], $extra), static fn ($v) => $v !== null && $v !== ''))) !== '' ? '?' . $s : '');

$featured = count(array_filter($rows, static fn (array $r): bool => (int) $r['is_featured'] === 1));
$visible = count(array_filter($rows, static fn (array $r): bool => (int) $r['is_visible'] === 1));
$hidden = count($rows) - $visible;
$linked = array_sum(array_column($rows, 'product_count'));
$noProducts = count(array_filter($rows, static fn (array $r): bool => (int) $r['product_count'] === 0));
$featuredFull = $featured >= SupplierRepository::MAX_FEATURED;

// the order buttons move a row among all suppliers, so they are only shown on the unfiltered list
$filtered = $show !== '' || $q !== '';
$last = count($rows) - 1;
$list = [];
foreach ($rows as $pos => $r) {
    $on = (int) $r['is_visible'] === 1;
    if (($show === 'featured' && !(int) $r['is_featured']) || ($show === 'visible' && !$on) || ($show === 'hidden' && $on)) {
        continue;
    }
    if ($q !== '' && mb_stripos($r['name'] . ' ' . $r['slug'] . ' ' . Countries::name($r['country_code'], 'lo'), $q) === false) {
        continue;
    }
    $list[] = $r + ['pos' => $pos];
}
$tabs = ['' => ['ທັງໝົດ', count($rows)], 'featured' => ['ຫຼັກ', $featured], 'visible' => ['ສະແດງ', $visible], 'hidden' => ['ເຊື່ອງໄວ້', $hidden]];

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
<div class="dash ibx dv sp">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <h1>ຜູ້ສະໜອງ</h1>
        <p class="dash-head__meta">ບໍລິສັດທີ່ XTK ເປັນຕົວແທນຈຳໜ່າຍ — ຈັດລຳດັບ, ເລືອກຜູ້ສະໜອງຫຼັກ, ເປີດ/ປິດການສະແດງ ແລະ ເຊື່ອມສິນຄ້າ</p>
      </div>
      <div class="dash-head__tools">
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
        <a class="btn btn--sm" href="/admin/suppliers/new"><?= $i('plus') ?>ເພີ່ມຜູ້ສະໜອງ</a>
      </div>
    </div>
  </header>

  <div class="ibx-stats">
    <?= $stat('primary', 'factory', 'ຜູ້ສະໜອງທັງໝົດ', $n(count($rows)), 'ລຽງຕາມລຳດັບໃນໜ້າເວັບ', $filtered ? '/admin/suppliers' : null) ?>
    <?= $stat('accent', 'star', 'ຜູ້ສະໜອງຫຼັກ', $n($featured) . ' / ' . SupplierRepository::MAX_FEATURED, $featuredFull ? 'ເຕັມແລ້ວ — ປິດອັນອື່ນກ່ອນ' : 'ໂລໂກ້ຢູ່ໃນເມນູ', $link(['show' => $show === 'featured' ? null : 'featured']), $show === 'featured') ?>
    <?= $stat('success', 'eye', 'ສະແດງໃນເວັບ', $n($visible), 'ຢູ່ໃນໜ້າຜູ້ສະໜອງ', $link(['show' => $show === 'visible' ? null : 'visible']), $show === 'visible') ?>
    <?= $stat('warning', 'eye-off', 'ເຊື່ອງໄວ້', $n($hidden), 'ບໍ່ມີໜ້າ, ບໍ່ສະແດງໃນເວັບ', $link(['show' => $show === 'hidden' ? null : 'hidden']), $show === 'hidden') ?>
    <?= $withoutSupplier > 0
        ? $stat('danger', 'alert', 'ສິນຄ້າບໍ່ມີຜູ້ສະໜອງ', $n($withoutSupplier), 'ສິນຄ້າທີ່ສະແດງ ແຕ່ຍັງບໍ່ລະບຸ', '/admin/products?show=nosupplier')
        : $stat('info', 'box', 'ສິນຄ້າທີ່ເຊື່ອມແລ້ວ', $n($linked), 'ທຸກສິນຄ້າມີຜູ້ສະໜອງ', '/admin/products') ?>
  </div>

  <?php if ($withoutSupplier > 0): ?>
    <p class="ibx-alert" role="status"><?= $i('alert') ?><span>ມີ <a href="/admin/products?show=nosupplier"><b class="num"><?= e($n($withoutSupplier)) ?></b> ສິນຄ້າ</a> ທີ່ຍັງບໍ່ລະບຸຜູ້ສະໜອງ — ເປີດຜູ້ສະໜອງ ແລ້ວກົດ "ເພີ່ມສິນຄ້າ", ຫຼື ເລືອກຜູ້ສະໜອງໃນຟອມສິນຄ້າ.</span></p>
  <?php endif; ?>

  <section class="ibx-box" aria-labelledby="sp-heading">
    <h2 id="sp-heading" class="sr">ລາຍການຜູ້ສະໜອງ</h2>
    <form class="ibx-tools" method="get" action="/admin/suppliers" role="search">
      <?php if ($show !== ''): ?><input type="hidden" name="show" value="<?= e($show) ?>"><?php endif; ?>
      <div class="ibx-tools__row">
        <nav class="ibx-tabs" aria-label="ການສະແດງ">
          <?php foreach ($tabs as $k => [$label, $count]): ?>
            <a href="<?= e($link(['show' => $k === '' ? null : $k])) ?>"<?= $show === (string) $k ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="num"><?= e($n($count)) ?></span></a>
          <?php endforeach; ?>
        </nav>
        <div class="ibx-search">
          <?= $i('search') ?>
          <input type="search" name="q" value="<?= e($q) ?>" placeholder="ຄົ້ນຫາຊື່, ໜ້າເວັບ ຫຼື ປະເທດ…" aria-label="ຄົ້ນຫາຜູ້ສະໜອງ" aria-keyshortcuts="/" data-slash-focus>
          <kbd aria-hidden="true">/</kbd>
        </div>
      </div>
    </form>

    <div class="ibx-bulk">
      <span class="ibx-bulk__info">
        ສະແດງ <?= e($n(count($list))) ?> ຈາກ <?= e($n(count($rows))) ?>
        <?php if ($filtered): ?> · ລ້າງຕົວກອງເພື່ອຈັດລຳດັບ · <a href="/admin/suppliers">ລ້າງຕົວກອງ</a><?php else: ?> · ລຳດັບນີ້ຄືລຳດັບໃນໜ້າເວັບ<?php endif; ?>
        <?php if ($noProducts > 0): ?> · <span class="tag tag--warn"><?= e($n($noProducts)) ?> ຜູ້ສະໜອງຍັງບໍ່ມີສິນຄ້າ</span><?php endif; ?>
      </span>
    </div>

    <?php if ($list === []): ?>
      <div class="ibx-empty">
        <span class="ibx-empty__ico"><?= $i('factory') ?></span>
        <?php if ($rows === []): ?>
          <p><b>ຍັງບໍ່ມີຜູ້ສະໜອງ</b><br><span class="muted">ເມນູ "ຜູ້ສະໜອງ" ໃນໜ້າເວັບຈະປາກົດເມື່ອມີຜູ້ສະໜອງທີ່ສະແດງຢ່າງໜ້ອຍ 1 ລາຍ ແລະ ເຜີຍແຜ່ແລ້ວ.<br>ໃຊ້ສະເພາະຊື່ ແລະ ໂລໂກ້ທີ່ລູກຄ້າຢືນຢັນ ແລະ ອະນຸຍາດໃຫ້ໃຊ້ແລ້ວ.</span></p>
          <a class="btn btn--sm" href="/admin/suppliers/new"><?= $i('plus') ?>ເພີ່ມຜູ້ສະໜອງ</a>
        <?php else: ?>
          <p><b>ບໍ່ພົບຜູ້ສະໜອງ</b><br><span class="muted">ລອງປ່ຽນຕົວກອງ ຫຼື ຄຳຄົ້ນຫາ.</span></p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <ul class="ibx-list">
        <?php foreach ($list as $r):
            $id = (int) $r['id'];
            $on = (int) $r['is_visible'] === 1;
            $star = (int) $r['is_featured'] === 1;
            $count = (int) $r['product_count'];
            $host = $r['website_url'] ? preg_replace('/^www\./', '', (string) parse_url($r['website_url'], PHP_URL_HOST)) : '';
        ?>
          <li class="ibx-row dv-row sp-row ibx-row--<?= $on ? ($star ? 'accent' : 'success') : 'muted' ?><?= $on ? '' : ' is-hidden' ?>" id="s<?= e($id) ?>">
            <div class="dv-order">
              <span class="dv-order__pos num" aria-hidden="true"><?= e(str_pad((string) ($r['pos'] + 1), 2, '0', STR_PAD_LEFT)) ?></span>
              <?php if (!$filtered): ?><?= View::render('admin/partials/order-buttons', ['action' => '/admin/suppliers/' . $id . '/move', 'label' => $r['name'], 'first' => $r['pos'] === 0, 'last' => $r['pos'] === $last]) ?><?php endif; ?>
            </div>
            <a class="ibx-row__main" href="/admin/suppliers/<?= e($id) ?>">
              <span class="logo-box sp-logo<?= (int) $r['logo_on_dark'] ? ' logo-box--dark' : '' ?>">
                <?php if ($r['logo_path']): ?><img src="/<?= e($r['logo_path']) ?>" alt="" loading="lazy"><?php else: ?><?= $i('image') ?><?php endif; ?>
              </span>
              <span class="ibx-row__text">
                <span class="ibx-row__top">
                  <b title="<?= e($r['name']) ?>"><?= e($r['name']) ?></b>
                  <?php if ($star): ?><span class="sp-star"><?= $i('star') ?>ຫຼັກ</span><?php endif; ?>
                </span>
                <span class="ibx-row__meta">
                  <span class="ibx-status"><i aria-hidden="true"></i><?= $on ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                  <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('globe') ?><?= e(Countries::name($r['country_code'], 'lo')) ?></span>
                  <?php if ($r['partnership']): ?><span aria-hidden="true">·</span><span class="dv-meta"><?= $i('handshake') ?><?= e(SC::PARTNERSHIP_NAMES[$r['partnership']] ?? $r['partnership']) ?></span><?php endif; ?>
                  <?php if ($r['since_year']): ?><span aria-hidden="true">·</span><span class="dv-meta"><?= $i('calendar') ?>ແຕ່ <?= e($r['since_year']) ?></span><?php endif; ?>
                  <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('box') ?><?= e($n($count)) ?> ສິນຄ້າ</span>
                  <?php if ($count === 0): ?><span class="tag tag--error">ຍັງບໍ່ມີສິນຄ້າ</span><?php endif; ?>
                  <?php if (!$r['logo_path']): ?><span class="tag tag--warn">ບໍ່ມີໂລໂກ້</span><?php endif; ?>
                </span>
              </span>
            </a>
            <div class="dv-acts sp-acts">
              <?php foreach (['is_featured' => ['ຫຼັກ', 'ຜູ້ສະໜອງຫຼັກ', $star], 'is_visible' => ['ສະແດງ', 'ສະແດງໃນໜ້າເວັບ', $on]] as $field => [$short, $label, $checked]):
                  $blocked = $field === 'is_featured' && !$checked && $featuredFull; ?>
                <form class="sp-switch" method="post" action="/admin/suppliers/<?= e($id) ?>/toggle"><?= Csrf::field() ?><input type="hidden" name="field" value="<?= $field ?>">
                  <button class="switch" type="submit" role="switch" aria-checked="<?= $checked ? 'true' : 'false' ?>" aria-label="<?= e($label . ': ' . $r['name']) ?>" title="<?= e($blocked ? 'ຜູ້ສະໜອງຫຼັກມີໄດ້ສູງສຸດ ' . SupplierRepository::MAX_FEATURED . ' ລາຍ' : $label) ?>"><span></span></button>
                  <span aria-hidden="true"><?= e($short) ?></span>
                </form>
              <?php endforeach; ?>
              <?php if ($r['website_url']): ?><a class="ibx-icon-btn" href="<?= e($r['website_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e('ເວັບໄຊ ' . $r['name'] . ' (ເປີດແຖບໃໝ່)') ?>" title="<?= e($host) ?>"><?= $i('external') ?></a><?php endif; ?>
              <a class="ibx-icon-btn" href="/admin/suppliers/<?= e($id) ?>#products" aria-label="<?= e('ສິນຄ້າຂອງ ' . $r['name']) ?>" title="ສິນຄ້າ"><?= $i('box') ?></a>
              <a class="ibx-icon-btn" href="/admin/suppliers/<?= e($id) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $r['name']) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <p class="dv-foot">ຜູ້ສະໜອງຫຼັກ (ສູງສຸດ <?= SupplierRepository::MAX_FEATURED ?> ລາຍ) ສະແດງໂລໂກ້ໃນເມນູ ແລະ ເປັນກາດໃຫຍ່ໃນໜ້າຜູ້ສະໜອງ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
  </section>
</div>
