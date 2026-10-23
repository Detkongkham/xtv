<?php
/**
 * @var array $p @var array $specs @var list<int> $supplierIds @var array $errors @var array $groups @var array $supplierOptions
 * @var ?array $image @var array $images @var bool $published @var int $featuredOthers featured products other than this one
 */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;
use App\Http\Admin\ProductController as PC;
use App\Publish\PagePreview;
use App\Repository\ProductRepository;

$isNew = (int) $p['id'] === 0;
$id = (int) $p['id'];
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
$specRow = static function (string $i, array $s): string {
    $in = static fn (string $k, string $ph, int $max) => '<input name="specs[' . $i . '][' . $k . ']" maxlength="' . $max . '" placeholder="' . e($ph) . '" aria-label="' . e($ph) . '" value="' . e($s[$k] ?? '') . '">';
    return '<fieldset class="spec-row" data-spec-row><div class="spec-row__main">'
        . $in('label_lo', 'ປ້າຍ (ລາວ)', 120) . $in('value_lo', 'ຄ່າ (ລາວ)', 400) . $in('label_en', 'ປ້າຍ (ອັງກິດ)', 120) . $in('value_en', 'ຄ່າ (ອັງກິດ)', 400)
        . '</div><details><summary>ໄທ / ຈີນ</summary><div class="spec-row__main">'
        . $in('label_th', 'ປ້າຍ (ໄທ)', 120) . $in('value_th', 'ຄ່າ (ໄທ)', 400) . $in('label_zh', 'ປ້າຍ (ຈີນ)', 120) . $in('value_zh', 'ຄ່າ (ຈີນ)', 400)
        . '</div></details><div class="spec-row__tools" data-spec-tools hidden>'
        . '<button class="icon-btn" type="button" data-spec="up" aria-label="ຍ້າຍຂຶ້ນ">↑</button><button class="icon-btn" type="button" data-spec="down" aria-label="ຍ້າຍລົງ">↓</button>'
        . '<button class="icon-btn" type="button" data-spec="remove" aria-label="ລຶບແຖວ">✕</button></div></fieldset>';
};

// lucide icon paths, drawn by $i()
$icons = [
    'arrow-left' => 'M19 12H5M12 19l-7-7 7-7',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'external' => 'M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6',
    'copy' => 'M10 8h10a2 2 0 012 2v10a2 2 0 01-2 2H10a2 2 0 01-2-2V10a2 2 0 012-2zM4 16a2 2 0 01-2-2V4a2 2 0 012-2h10a2 2 0 012 2',
    'image' => 'M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zM9 11a2 2 0 100-4 2 2 0 000 4zM21 15l-3.09-3.09a2 2 0 00-2.82 0L6 21',
    'truck' => 'M14 18V6a2 2 0 00-2-2H4a2 2 0 00-2 2v11a1 1 0 001 1h2M15 18H9M19 18h2a1 1 0 001-1v-3.65a1 1 0 00-.22-.62l-3.48-4.35A1 1 0 0017.52 8H14M17 20a2 2 0 100-4 2 2 0 000 4zM7 20a2 2 0 100-4 2 2 0 000 4z',
    'list' => 'M3 6h.01M3 12h.01M3 18h.01M8 6h13M8 12h13M8 18h13',
    'languages' => 'M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10M14 18h6',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'plus' => 'M5 12h14M12 5v14',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'tag' => 'M12.59 2.59A2 2 0 0011.17 2H4a2 2 0 00-2 2v7.17a2 2 0 00.59 1.42l8.7 8.7a2.43 2.43 0 003.42 0l6.58-6.58a2.43 2.43 0 000-3.42zM7.5 8a.5.5 0 100-1 .5.5 0 000 1z',
    'star' => 'M11.53 2.3a.53.53 0 01.94 0l2.31 4.68a2.12 2.12 0 001.6 1.16l5.16.76a.53.53 0 01.3.9l-3.74 3.64a2.12 2.12 0 00-.61 1.88l.88 5.14a.53.53 0 01-.77.56l-4.62-2.43a2.12 2.12 0 00-1.97 0L6.4 21.01a.53.53 0 01-.77-.56l.88-5.14a2.12 2.12 0 00-.61-1.88L2.16 9.79a.53.53 0 01.3-.9l5.16-.76a2.12 2.12 0 001.6-1.16z',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'trash' => 'M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);

$name = trim((string) $p['name']);
$on = (int) $p['is_visible'] === 1;
$featured = (int) $p['is_featured'] === 1;
$featuredFull = !$featured && $featuredOthers >= ProductRepository::MAX_FEATURED;
$where = $groups[$p['group_id']] ?? null;
$back = '/admin/products' . ($p['group_id'] ? '?group=' . (int) $p['group_id'] : '') . ($isNew ? '' : '#p' . $id);
$specCount = count($specs);
$supCount = count($supplierIds);

// a language counts as done when the summary and the details both have text; empty ones show the group's text
$langs = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
$missing = array_values(array_filter($langs, static fn (string $l): bool => array_filter(PC::FIELDS, static fn (array $f): bool => trim((string) ($p[$f[0] . '_' . $l] ?? '')) === '') !== [], ARRAY_FILTER_USE_KEY));

$stat = static function (string $toneName, string $icon, string $label, string $value, string $note, ?string $href = null) use ($i): string {
    $inner = '<span class="ibx-stat__bar" aria-hidden="true"></span><span class="ibx-stat__ico">' . $i($icon) . '</span>'
        . '<span class="ibx-stat__text"><span class="ibx-stat__label">' . e($label) . '</span><b class="ibx-stat__value">' . e($value) . '</b>'
        . '<span class="ibx-stat__note">' . e($note) . '</span></span>';
    $cls = 'ibx-stat ibx-stat--' . $toneName;
    return $href === null
        ? '<div class="' . $cls . '">' . $inner . '</div>'
        : '<a class="' . $cls . '" href="' . e($href) . '">' . $inner . $i('sliders', 'ibx-stat__go') . '</a>';
};
?>
<div class="dash ibx gpe pe">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="gpe-head">
        <span class="pe-head__img">
          <?php if ($image): ?><img src="<?= e(MC::thumb($image)) ?>" alt="" width="72" height="54"><?php else: ?><?= $i($isNew ? 'box' : 'tag') ?><?php endif; ?>
        </span>
        <div class="dash-head__text">
          <a class="gpe-back" href="<?= e($back) ?>"><?= $i('arrow-left') ?>ສິນຄ້າ</a>
          <h1 class="pe-head__title"><?= $isNew ? 'ເພີ່ມສິນຄ້າ' : e($name !== '' ? $name : 'ສິນຄ້າ') ?>
            <?php if (!$isNew && $featured): ?><span class="sp-star"><?= $i('star') ?>ແນະນຳ</span><?php endif; ?>
            <?php if (!$isNew && !$on): ?><span class="tag tag--warn">ເຊື່ອງໄວ້</span><?php endif; ?>
          </h1>
          <p class="dash-head__meta">
            <?php if ($isNew): ?>
              ໃສ່ຊື່, ເລືອກກຸ່ມ ແລະ ຮູບ — ສິນຄ້າທີ່ມີຮູບມີໜ້າເວັບຂອງຕົນເອງ
            <?php else: ?>
              <?php if ($where): ?><span><?= e($where) ?></span><span aria-hidden="true">·</span><?php endif; ?>
              <span class="dv-meta"><?= $image ? '<code>' . e('product-' . $p['slug'] . '.html') . '</code>' : 'ກາດສອບຖາມລາຄາ' ?></span>
            <?php endif; ?>
          </p>
        </div>
      </div>
      <div class="dash-head__tools">
        <?php if ($published): ?><a class="btn-soft" href="/lo/product-<?= e($p['slug']) ?>.html" target="_blank" rel="noopener"><?= $i('external') ?><span>ໜ້າເວັບ</span><span class="sr"> (ແທັບໃໝ່)</span></a><?php endif; ?>
        <?php if (!$isNew): ?>
          <a class="btn-soft" href="<?= PagePreview::url('product-' . $p['slug']) ?>" target="_blank" rel="noopener"><?= $i('eye') ?><span>ຕົວຢ່າງ</span><span class="sr"> (ແທັບໃໝ່)</span></a>
          <button class="btn-soft" type="submit" form="pe-copy" title="ສ້າງສິນຄ້າໃໝ່ທີ່ມີຂໍ້ມູນຄືກັນ (ເຊື່ອງໄວ້)"><?= $i('copy') ?><span>ສຳເນົາ</span></button>
        <?php endif; ?>
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
      </div>
    </div>
  </header>

  <?php if (!$isNew): ?>
    <div class="ibx-stats">
      <?= $on
          ? $stat($featured ? 'accent' : 'success', $featured ? 'star' : 'eye', 'ການສະແດງ', $featured ? 'ແນະນຳ' : 'ສະແດງ', $featured ? 'ໃນໜ້າຫຼັກ ແລະ ໜ້າກຸ່ມ' : 'ໃນໜ້າກຸ່ມ', '#pe-show')
          : $stat('warning', 'eye-off', 'ການສະແດງ', 'ເຊື່ອງໄວ້', 'ບໍ່ປາກົດໃນໜ້າເວັບ', '#pe-show') ?>
      <?= $stat($image ? 'primary' : 'info', $image ? 'image' : 'tag', 'ຮູບສິນຄ້າ', $image ? 'ມີຮູບ' : 'ບໍ່ມີຮູບ', $image ? 'ມີໜ້າລາຍລະອຽດຂອງຕົນເອງ' : 'ເປັນກາດ “ສອບຖາມລາຄາ”', '#pe-image') ?>
      <?= $stat($supCount > 0 ? 'success' : 'danger', $supCount > 0 ? 'truck' : 'alert', 'ຜູ້ສະໜອງ', $n($supCount), $supCount > 0 ? 'ຫຼັກ: ' . ($supplierOptions[$supplierIds[0]] ?? '—') : 'ຕ້ອງເພີ່ມຜູ້ສະໜອງ', '#pe-suppliers') ?>
      <?= $stat($specCount > 0 ? 'primary' : 'info', 'list', 'ຂໍ້ມູນຈຳເພາະ', $n($specCount) . ' ແຖວ', $specCount > 0 ? 'ໃນຕາຕະລາງຂອງໜ້າສິນຄ້າ' : 'ໃຊ້ແຖວທົ່ວໄປ', '#specs') ?>
      <?= $stat($missing === [] ? 'accent' : 'warning', 'languages', 'ຄຳອະທິບາຍ', (4 - count($missing)) . ' / 4', $missing === [] ? 'ຄົບທຸກພາສາ' : 'ຫວ່າງ: ' . implode(', ', $missing), '#pe-text') ?>
    </div>
  <?php endif; ?>

  <?php if ($errors !== []): ?>
    <p class="ibx-alert" role="alert"><?= $i('alert') ?>ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
  <?php endif; ?>

  <form class="ibx-cols gpe-cols" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/products/new' : '/admin/products/' . e($id) ?>">
    <?= Csrf::field() ?>
    <div class="gpe-main">
      <section class="ibx-box" aria-labelledby="pe-basic">
        <div class="gpe-box__head">
          <h2 id="pe-basic">ຂໍ້ມູນຫຼັກ</h2>
          <span class="gpe-box__note">ຊື່ດຽວທຸກພາສາ</span>
        </div>
        <div class="gpe-box__body pe-grid">
          <div class="field pe-grid__wide">
            <label for="name">ຊື່ສິນຄ້າ <em>*</em></label>
            <input id="name" name="name" required maxlength="160" value="<?= e($p['name']) ?>"<?= $inv('name') ?>><?= $err('name') ?>
            <span class="hint">ຕາມຊື່ທີ່ຜູ້ຜະລິດໃຊ້.</span>
          </div>
          <div class="field">
            <label for="group_id">ກຸ່ມ <em>*</em></label>
            <select id="group_id" name="group_id" required<?= $inv('group_id') ?>>
              <option value="">— ເລືອກ —</option>
              <?php foreach ($groups as $gId => $label): ?><option value="<?= e($gId) ?>"<?= (int) $p['group_id'] === (int) $gId ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select><?= $err('group_id') ?>
          </div>
          <div class="field">
            <label for="slug">ຊື່ໜ້າເວັບ</label>
            <input id="slug" name="slug" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($p['slug']) ?>"<?= $inv('slug') ?>><?= $err('slug') ?>
            <span class="hint">product-<b><?= e($p['slug'] ?: '…') ?></b>.html · ຫວ່າງ = ສ້າງຈາກຊື່.<?= $published ? ' ຖ້າປ່ຽນ, URL ເກົ່າຈະພາໄປ URL ໃໝ່ເອງ.' : '' ?></span>
          </div>
          <div class="field pe-grid__wide">
            <label for="variants_note">ລຸ້ນ / ລາຍການຍ່ອຍ</label>
            <input id="variants_note" name="variants_note" maxlength="400" placeholder="T3 · T4 · TSH · HbA1c" value="<?= e($p['variants_note'] ?? '') ?>"<?= $inv('variants_note') ?>><?= $err('variants_note') ?>
            <span class="hint">ແຖວນ້ອຍໃຕ້ຄຳອະທິບາຍ, ຄືກັນທຸກພາສາ.</span>
          </div>
        </div>
      </section>

      <section class="ibx-box" id="pe-text-box" aria-labelledby="pe-text">
        <div class="gpe-box__head">
          <h2 id="pe-text">ຄຳອະທິບາຍ</h2>
          <span class="gpe-box__note">ຫວ່າງ = ໃຊ້ຂໍ້ຄວາມຂອງກຸ່ມ · ໄທ ແລະ ຈີນ ຫວ່າງ = ໃຊ້ອັງກິດ</span>
        </div>
        <div class="gpe-box__body">
          <?= View::render('admin/partials/lang-fields', ['fields' => PC::FIELDS, 'row' => $p, 'errors' => $errors]) ?>
        </div>
      </section>

      <section class="ibx-box" id="specs" aria-labelledby="pe-specs">
        <div class="gpe-box__head">
          <h2 id="pe-specs">ຂໍ້ມູນຈຳເພາະ <span class="gpe-box__count num"><?= e($n($specCount)) ?></span></h2>
          <button class="gp-div__add" type="button" data-spec-add hidden><?= $i('plus') ?><span>ເພີ່ມແຖວ</span></button>
        </div>
        <div class="gpe-box__body pe-specs">
          <p class="hint">ແຖວ ປ້າຍ / ຄ່າ ໃນຕາຕະລາງຂອງໜ້າສິນຄ້າ (ຂະແໜງ, ກຸ່ມ ແລະ ຜູ້ສະໜອງ ໃສ່ໃຫ້ເອງ). ບໍ່ມີແຖວ = ສະແດງແຖວທົ່ວໄປ (ລຸ້ນ, ມາດຕະຖານ, ການຮັບປະກັນ, ການຕິດຕັ້ງ). ລາວ ແລະ ອັງກິດ ບັງຄັບ.</p>
          <?php if (isset($errors['specs'])): ?><p class="field__err" role="alert"><?= e($errors['specs']) ?></p><?php endif; ?>
          <div class="spec-rows" data-spec-rows>
            <?php foreach ($specs as $k => $s): ?><?= $specRow((string) $k, $s) ?><?php endforeach; ?>
            <?= $specRow('new', []) ?>
          </div>
          <template data-spec-template><?= $specRow('__N__', []) ?></template>
        </div>
      </section>

      <details class="ibx-box pe-seo"<?= array_filter(array_keys($errors), static fn ($k) => str_starts_with($k, 'seo_')) !== [] ? ' open' : '' ?>>
        <summary class="gpe-box__head">
          <h2><?= $i('search') ?>SEO</h2>
          <span class="gpe-box__note">ບໍ່ບັງຄັບ · ຫວ່າງ = ໃຊ້ຊື່ ແລະ ຂະແໜງ</span>
        </summary>
        <div class="gpe-box__body">
          <?= View::render('admin/partials/lang-fields', ['fields' => PC::SEO_FIELDS, 'row' => $p, 'errors' => $errors]) ?>
        </div>
      </details>
    </div>

    <aside class="ibx-panel gpe-panel pe-panel" aria-labelledby="pe-settings">
      <div class="ibx-panel__head">
        <h2 id="pe-settings" class="gpe-panel__title">ການຕັ້ງຄ່າ</h2>
      </div>

      <div class="gpe-panel__fields" id="pe-show">
        <label class="gpe-toggle">
          <span class="gpe-toggle__text"><b>ສະແດງໃນໜ້າເວັບ</b><span>ປິດໄວ້ = ເຊື່ອງຊົ່ວຄາວ, ຂໍ້ມູນຍັງຢູ່</span></span>
          <input type="checkbox" name="is_visible" value="1" role="switch"<?= $on ? ' checked' : '' ?>>
        </label>
        <div class="field">
          <label class="gpe-toggle<?= $featuredFull ? ' is-off' : '' ?>">
            <span class="gpe-toggle__text"><b>ສິນຄ້າແນະນຳ</b><span><?= $featuredFull ? 'ເຕັມແລ້ວ (' . ProductRepository::MAX_FEATURED . '/' . ProductRepository::MAX_FEATURED . ') — ປິດອັນອື່ນກ່ອນ' : 'ໃນໜ້າຫຼັກ · ຕ້ອງມີຮູບ · ໃຊ້ແລ້ວ ' . ($featuredOthers + (int) $featured) . '/' . ProductRepository::MAX_FEATURED ?></span></span>
            <input type="checkbox" name="is_featured" value="1" role="switch"<?= $featured ? ' checked' : '' ?><?= $inv('is_featured') ?>>
          </label><?= $err('is_featured') ?>
        </div>
      </div>

      <div class="pe-panel__sec" id="pe-image">
        <?= View::render('admin/partials/media-picker', ['name' => 'image_id', 'label' => 'ຮູບສິນຄ້າ', 'current' => $image, 'images' => $images, 'error' => $errors['image_id'] ?? null]) ?>
        <p class="gpe-panel__hint">ມີຮູບ = ມີໜ້າລາຍລະອຽດຂອງຕົນເອງ. ບໍ່ມີຮູບ = ກາດ “ສອບຖາມລາຄາ” ທີ່ລິ້ງໄປໜ້າຕິດຕໍ່.</p>
      </div>

      <fieldset class="pe-panel__sec pe-sup" id="pe-suppliers">
        <legend>ຜູ້ສະໜອງ<?php if ($supplierOptions !== []): ?> <span class="gpe-box__count num"><?= e($n($supCount)) ?></span><?php endif; ?></legend>
        <?php if ($supplierOptions === []): ?>
          <p class="gpe-panel__hint">ຍັງບໍ່ມີຜູ້ສະໜອງ. <a href="/admin/suppliers/new">ເພີ່ມຜູ້ສະໜອງ</a></p>
        <?php else: ?>
          <p class="gpe-panel__hint">ໝາຍຜູ້ສະໜອງທີ່ XTK ຊື້ສິນຄ້ານີ້ມາ. “ຫຼັກ” = ສະແດງກ່ອນ.</p>
          <ul class="pe-sup__list">
            <?php foreach ($supplierOptions as $sid => $sName): $picked = in_array((int) $sid, $supplierIds, true) ?>
              <li class="pe-sup__row">
                <label class="pe-sup__name"><input type="checkbox" class="ibx-check" name="supplier_ids[]" value="<?= e($sid) ?>"<?= $picked ? ' checked' : '' ?>><span><?= e($sName) ?></span></label>
                <label class="pe-sup__main"><input type="radio" name="primary_supplier" value="<?= e($sid) ?>"<?= ($supplierIds[0] ?? 0) === (int) $sid ? ' checked' : '' ?>><span>ຫຼັກ</span><span class="sr"> — <?= e($sName) ?></span></label>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </fieldset>

      <div class="gpe-panel__acts">
        <button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມສິນຄ້າ' : 'ບັນທຶກ' ?></button>
        <a class="btn btn--ghost" href="<?= e($back) ?>">ຍົກເລີກ</a>
      </div>
    </aside>
  </form>

  <?php if (!$isNew): ?>
    <form id="pe-copy" method="post" action="/admin/products/<?= e($id) ?>/copy" hidden><?= Csrf::field() ?></form>
    <form class="gpe-danger" method="post" action="/admin/products/<?= e($id) ?>/delete" data-confirm="ລຶບສິນຄ້າ <?= e($p['name']) ?>?">
      <?= Csrf::field() ?>
      <span class="gpe-danger__ico"><?= $i('trash') ?></span>
      <div class="gpe-danger__text">
        <h2>ລຶບສິນຄ້າ</h2>
        <p>ຂໍ້ມູນຈຳເພາະ ແລະ ການເຊື່ອມກັບຜູ້ສະໜອງຈະຖືກລຶບນຳ. ຮູບຍັງຢູ່ໃນຄັງສື່.<?= $published ? ' ໜ້າເກົ່າຈະພາໄປໜ້າຂະແໜງ.' : '' ?> ຖ້າຢາກເອົາອອກຊົ່ວຄາວ ໃຫ້ປິດ “ສະແດງ” ແທນ.</p>
      </div>
      <button class="btn btn--sm btn--danger" type="submit">ລຶບ</button>
    </form>
  <?php endif; ?>
</div>
