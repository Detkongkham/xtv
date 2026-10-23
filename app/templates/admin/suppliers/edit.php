<?php
/**
 * @var array $s @var array $errors @var array $countries @var array $products @var array $unlinked @var bool $published
 * @var ?array $saved the stored row (null when new) @var int $featuredOthers featured suppliers other than this one
 */
use App\Core\Countries;
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\SupplierController as SC;
use App\Repository\SupplierRepository;
use App\Service\MediaService;

$isNew = (int) $s['id'] === 0;
$id = (int) $s['id'];
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
$byDivision = [];
foreach ($unlinked as $p) {
    $byDivision[$p['division_name']][] = $p;
}

// lucide icon paths, drawn by $i()
$icons = [
    'arrow-left' => 'M19 12H5M12 19l-7-7 7-7',
    'factory' => 'M2 20a2 2 0 002 2h16a2 2 0 002-2V8l-7 5V8l-7 5V4a2 2 0 00-2-2H4a2 2 0 00-2 2zM17 18h1M12 18h1M7 18h1',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'star' => 'M11.53 2.3a.53.53 0 01.95 0l2.31 4.68a2.12 2.12 0 001.6 1.16l5.17.76a.53.53 0 01.29.9l-3.74 3.64a2.12 2.12 0 00-.61 1.88l.88 5.14a.53.53 0 01-.77.56l-4.62-2.43a2.12 2.12 0 00-1.97 0L6.4 21.01a.53.53 0 01-.77-.56l.88-5.14a2.12 2.12 0 00-.61-1.88L2.16 9.79a.53.53 0 01.29-.9l5.17-.76a2.12 2.12 0 001.6-1.16z',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'globe' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a14.5 14.5 0 000 20 14.5 14.5 0 000-20',
    'languages' => 'M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10M14 18h6',
    'image' => 'M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zM9 11a2 2 0 100-4 2 2 0 000 4zM21 15l-3.09-3.09a2 2 0 00-2.82 0L6 21',
    'external' => 'M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'x' => 'M18 6L6 18M6 6l12 12',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'trash' => 'M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);

$langs = SC::LANG_NAMES;
$aboutDone = array_filter(SupplierRepository::LANGS, static fn (string $l): bool => trim((string) ($saved['about_' . $l] ?? '')) !== '');
$aboutMissing = array_values(array_map(static fn (string $l): string => $langs[$l], array_diff(SupplierRepository::LANGS, $aboutDone)));
$featuredFull = $featuredOthers >= SupplierRepository::MAX_FEATURED;
$productCount = count($products);
$primaryCount = count(array_filter($products, static fn (array $p): bool => (int) $p['is_primary'] === 1));

$stat = static function (string $toneName, string $icon, string $label, string $value, string $note, ?string $href = null) use ($i): string {
    $inner = '<span class="ibx-stat__bar" aria-hidden="true"></span><span class="ibx-stat__ico">' . $i($icon) . '</span>'
        . '<span class="ibx-stat__text"><span class="ibx-stat__label">' . e($label) . '</span><b class="ibx-stat__value">' . e($value) . '</b>'
        . '<span class="ibx-stat__note">' . e($note) . '</span></span>';
    $cls = 'ibx-stat ibx-stat--' . $toneName;
    return $href === null
        ? '<div class="' . $cls . '">' . $inner . '</div>'
        : '<a class="' . $cls . '" href="' . e($href) . '">' . $inner . '</a>';
};
$toggle = static fn (string $name, string $title, string $note, bool $on, string $extra = ''): string => '<label class="gpe-toggle"><span class="gpe-toggle__text"><b>' . e($title) . '</b><span>' . e($note) . '</span></span>'
    . '<input type="checkbox" name="' . $name . '" value="1" role="switch"' . ($on ? ' checked' : '') . $extra . '></label>';
?>
<div class="dash ibx gpe sp spe">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="gpe-head">
        <?php if ($saved !== null && $saved['logo_path']): ?>
          <span class="logo-box spe-head__logo<?= (int) $saved['logo_on_dark'] ? ' logo-box--dark' : '' ?>"><img src="/<?= e($saved['logo_path']) ?>" alt=""></span>
        <?php else: ?>
          <span class="gpe-head__ico"><?= $i('factory') ?></span>
        <?php endif; ?>
        <div class="dash-head__text">
          <a class="gpe-back" href="/admin/suppliers<?= $isNew ? '' : '#s' . e($id) ?>"><?= $i('arrow-left') ?>ຜູ້ສະໜອງ</a>
          <h1><?= $isNew ? 'ເພີ່ມຜູ້ສະໜອງ' : e($saved['name']) ?><?php if (!$isNew && (int) $saved['is_featured']): ?> <span class="sp-star"><?= $i('star') ?>ຫຼັກ</span><?php endif; ?></h1>
          <p class="dash-head__meta">
            <?php if ($isNew): ?>
              ໃສ່ຊື່, ປະເທດ ແລະ ໂລໂກ້ — ບັນທຶກແລ້ວຈຶ່ງເລືອກສິນຄ້າທີ່ XTK ຂາຍຈາກຜູ້ສະໜອງນີ້ໄດ້
            <?php else: ?>
              <span><?= $i('globe') ?><?= e(Countries::name($saved['country_code'], 'lo')) ?></span>
              <span aria-hidden="true">·</span><span>supplier-<?= e($saved['slug']) ?>.html</span>
              <?php if ($saved['partnership']): ?><span aria-hidden="true">·</span><span><?= e(SC::PARTNERSHIP_NAMES[$saved['partnership']] ?? '') ?></span><?php endif; ?>
            <?php endif; ?>
          </p>
        </div>
      </div>
      <div class="dash-head__tools">
        <?php if (!$isNew && $saved['website_url']): ?>
          <a class="btn-soft" href="<?= e($saved['website_url']) ?>" target="_blank" rel="noopener"><?= $i('external') ?><span>ເວັບໄຊ</span></a>
        <?php endif; ?>
        <?php if (!$isNew): ?>
          <a class="btn-soft" href="#products"><?= $i('box') ?><span>ສິນຄ້າ</span></a>
        <?php endif; ?>
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
      </div>
    </div>
  </header>

  <?php if (!$isNew): ?>
    <div class="ibx-stats">
      <?= $stat($productCount > 0 ? 'primary' : 'danger', $productCount > 0 ? 'box' : 'alert', 'ສິນຄ້າ', $n($productCount), $productCount > 0 ? 'ເປັນຜູ້ສະໜອງຫຼັກ ' . $primaryCount . ' ລາຍການ' : 'ຍັງບໍ່ໄດ້ເຊື່ອມສິນຄ້າ', '#products') ?>
      <?= (int) $saved['is_visible'] === 1
          ? $stat('success', 'eye', 'ການສະແດງ', 'ສະແດງ', 'ຢູ່ໃນໜ້າຜູ້ສະໜອງ')
          : $stat('warning', 'eye-off', 'ການສະແດງ', 'ເຊື່ອງໄວ້', 'ບໍ່ປາກົດໃນໜ້າເວັບ') ?>
      <?= (int) $saved['is_featured'] === 1
          ? $stat('accent', 'star', 'ຜູ້ສະໜອງຫຼັກ', 'ແມ່ນ', 'ຢູ່ໃນ mega menu · ' . ($featuredOthers + 1) . ' / ' . SupplierRepository::MAX_FEATURED)
          : $stat('info', 'star', 'ຜູ້ສະໜອງຫຼັກ', 'ບໍ່', $featuredFull ? 'ເຕັມແລ້ວ ' . SupplierRepository::MAX_FEATURED . ' / ' . SupplierRepository::MAX_FEATURED : 'ໃຊ້ໄປ ' . $featuredOthers . ' / ' . SupplierRepository::MAX_FEATURED) ?>
      <?= $stat($aboutMissing === [] ? 'accent' : 'warning', 'languages', 'ຄຳອະທິບາຍ', count($aboutDone) . ' / 4', $aboutMissing === [] ? 'ຄົບທຸກພາສາ' : 'ຍັງຂາດ: ' . implode(', ', $aboutMissing)) ?>
    </div>
  <?php endif; ?>

  <?php if ($errors !== []): ?>
    <p class="ibx-alert" role="alert"><?= $i('alert') ?>ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
  <?php endif; ?>

  <form class="ibx-cols gpe-cols" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/suppliers/new' : '/admin/suppliers/' . e($id) ?>">
    <?= Csrf::field() ?>
    <div class="gpe-main">
      <section class="ibx-box" aria-labelledby="spe-basic">
        <div class="gpe-box__head">
          <h2 id="spe-basic">ຂໍ້ມູນຜູ້ສະໜອງ</h2>
          <span class="gpe-box__note">ຊື່ຍີ່ຫໍ້ ຄືກັນທຸກພາສາ</span>
        </div>
        <div class="gpe-box__body spe-grid">
          <div class="field spe-grid__wide">
            <label for="name">ຊື່ຜູ້ສະໜອງ <em>*</em></label>
            <input id="name" name="name" required maxlength="160" value="<?= e($s['name']) ?>"<?= $inv('name') ?>><?= $err('name') ?>
          </div>
          <div class="field">
            <label for="country_code">ປະເທດ <em>*</em></label>
            <select id="country_code" name="country_code" required<?= $inv('country_code') ?>>
              <option value="">— ເລືອກ —</option>
              <?php foreach ($countries as $code => $label): ?>
                <option value="<?= e($code) ?>"<?= $s['country_code'] === $code ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select><?= $err('country_code') ?>
          </div>
          <div class="field">
            <label for="website_url">ເວັບໄຊຂອງຜູ້ສະໜອງ</label>
            <input id="website_url" name="website_url" type="url" maxlength="255" placeholder="https://" value="<?= e($s['website_url'] ?? '') ?>"<?= $inv('website_url') ?>><?= $err('website_url') ?>
          </div>
          <div class="field spe-grid__wide">
            <label for="slug">ຊື່ໃນ URL</label>
            <input id="slug" name="slug" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($s['slug']) ?>"<?= $inv('slug') ?>><?= $err('slug') ?>
            <span class="hint">ໜ້າເວັບ: supplier-<b><?= e($s['slug'] ?: '…') ?></b>.html. ປ່ອຍຫວ່າງ = ສ້າງຈາກຊື່.<?= $published ? ' ຖ້າປ່ຽນ, URL ເກົ່າຈະພາໄປ URL ໃໝ່ເອງ.' : '' ?></span>
          </div>
        </div>
      </section>

      <section class="ibx-box" aria-labelledby="spe-about">
        <div class="gpe-box__head">
          <h2 id="spe-about">ກ່ຽວກັບຜູ້ສະໜອງ</h2>
          <span class="gpe-box__note">ໃນກາດຜູ້ສະໜອງຫຼັກ ແລະ ໜ້າລາຍລະອຽດ · ໄທ ແລະ ຈີນ ຫວ່າງ = ໃຊ້ອັງກິດ</span>
        </div>
        <div class="gpe-box__body">
          <div class="lang-tabs" data-lang-tabs>
            <?php foreach (SupplierRepository::LANGS as $l): $key = 'about_' . $l; ?>
              <?php $state = isset($errors[$key]) ? 'error' : (in_array($l, ['th', 'zh'], true) && trim((string) ($s[$key] ?? '')) === '' && trim((string) ($s['about_en'] ?? '')) !== '' ? 'missing' : ''); ?>
              <section class="lang-panel" data-label="<?= e($langs[$l]) ?>" data-state="<?= $state ?>">
                <h3 class="lang-panel__title"><?= e($langs[$l]) ?></h3>
                <div class="field">
                  <label for="<?= $key ?>">ຄຳອະທິບາຍ (<?= e($langs[$l]) ?>)</label>
                  <textarea id="<?= $key ?>" name="<?= $key ?>" rows="5" maxlength="3000" lang="<?= $l ?>"<?= $inv($key) ?>><?= e($s[$key] ?? '') ?></textarea><?= $err($key) ?>
                </div>
              </section>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="ibx-box" aria-labelledby="spe-logo">
        <div class="gpe-box__head">
          <h2 id="spe-logo">ໂລໂກ້</h2>
          <span class="gpe-box__note">PNG ຫຼື WebP ພື້ນໂປ່ງໃສ, ກວ້າງ ≥ <?= MediaService::LOGO_MIN_WIDTH ?> px</span>
        </div>
        <div class="gpe-box__body spe-logo">
          <?php if ($s['logo_path']): ?>
            <div class="logo-preview">
              <figure><span class="logo-box logo-box--lg"><img src="/<?= e($s['logo_path']) ?>" alt="<?= e($s['name']) ?> (ພື້ນຂາວ)"></span><figcaption>ພື້ນຂາວ</figcaption></figure>
              <figure><span class="logo-box logo-box--lg logo-box--dark"><img src="/<?= e($s['logo_path']) ?>" alt="<?= e($s['name']) ?> (ພື້ນເຂັ້ມ)"></span><figcaption>ພື້ນເຂັ້ມ</figcaption></figure>
            </div>
          <?php else: ?>
            <div class="spe-logo__none"><?= $i('image') ?><span>ຍັງບໍ່ມີໂລໂກ້ — ໜ້າເວັບຈະສະແດງຊື່ແທນ</span></div>
          <?php endif; ?>
          <div class="field">
            <label for="logo"><?= $s['logo_path'] ? 'ປ່ຽນໂລໂກ້' : 'ອັບໂລໂກ້' ?></label>
            <input id="logo" name="logo" type="file" accept="image/png,image/webp"<?= $inv('logo') ?>><?= $err('logo') ?>
          </div>
          <?= $toggle('logo_on_dark', 'ໂລໂກ້ສີຂາວ', 'ສະແດງເທິງພື້ນເຂັ້ມ ເພື່ອໃຫ້ເບິ່ງເຫັນ', (int) $s['logo_on_dark'] === 1) ?>
          <?php if ($s['logo_path']): ?>
            <label class="check spe-logo__remove"><input type="checkbox" name="remove_logo" value="1"><span>ເອົາໂລໂກ້ອອກ</span></label>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <aside class="ibx-panel gpe-panel" aria-labelledby="spe-settings">
      <div class="ibx-panel__head">
        <h2 id="spe-settings" class="gpe-panel__title">ການຕັ້ງຄ່າ</h2>
      </div>
      <div class="gpe-panel__fields">
        <?= $toggle('is_visible', 'ສະແດງໃນໜ້າເວັບ', 'ປິດໄວ້ = ບໍ່ປາກົດໃນໜ້າຜູ້ສະໜອງ ແລະ ໜ້າສິນຄ້າ', (int) $s['is_visible'] === 1) ?>
        <div class="field">
          <?= $toggle('is_featured', 'ຜູ້ສະໜອງຫຼັກ', 'mega menu ແລະ ກາດໃຫຍ່ · ໃຊ້ໄປ ' . $featuredOthers . ' / ' . SupplierRepository::MAX_FEATURED . ' ລາຍອື່ນ', (int) $s['is_featured'] === 1, $inv('is_featured')) ?><?= $err('is_featured') ?>
          <?php if ($featuredFull && !(int) $s['is_featured']): ?><span class="hint">ເຕັມແລ້ວ — ປິດອັນອື່ນໃນລາຍການຜູ້ສະໜອງກ່ອນ.</span><?php endif; ?>
        </div>
        <p class="spe-panel__sub">ຂໍ້ມູນທາງເລືອກ <span>ໃສ່ສະເພາະເມື່ອລູກຄ້າຢືນຢັນແລ້ວ. ຫວ່າງ = ບໍ່ສະແດງ.</span></p>
        <div class="field">
          <label for="partnership">ປະເພດການເປັນຕົວແທນ</label>
          <select id="partnership" name="partnership"<?= $inv('partnership') ?>>
            <option value="">— ບໍ່ສະແດງ —</option>
            <?php foreach (SC::PARTNERSHIP_NAMES as $k => $label): ?>
              <option value="<?= e($k) ?>"<?= ($s['partnership'] ?? '') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select><?= $err('partnership') ?>
        </div>
        <div class="field">
          <label for="since_year">ຮ່ວມງານຕັ້ງແຕ່ປີ</label>
          <input id="since_year" name="since_year" type="number" min="1950" max="<?= date('Y') ?>" inputmode="numeric" placeholder="ເຊັ່ນ 2015" value="<?= e($s['since_year'] ?? '') ?>"<?= $inv('since_year') ?>><?= $err('since_year') ?>
        </div>
      </div>
      <?php if (!$isNew): ?>
        <dl class="ibx-panel__meta">
          <dt>ໜ້າເວັບ</dt><dd><code>supplier-<?= e($saved['slug']) ?>.html</code></dd>
          <dt>ສິນຄ້າ</dt><dd><?= e($n($productCount)) ?> ລາຍການ</dd>
          <dt>ເຜີຍແຜ່</dt><dd><?= $published ? 'ຂຶ້ນເວັບແລ້ວ' : 'ຍັງບໍ່ໄດ້ເຜີຍແຜ່' ?></dd>
        </dl>
      <?php endif; ?>
      <div class="gpe-panel__acts">
        <button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມຜູ້ສະໜອງ' : 'ບັນທຶກ' ?></button>
        <a class="btn btn--ghost" href="/admin/suppliers">ຍົກເລີກ</a>
        <?= $isNew ? '' : View::render('admin/partials/preview-link', ['page' => 'supplier-' . $saved['slug']]) ?>
      </div>
    </aside>
  </form>

  <?php if (!$isNew): ?>
    <section class="ibx-box" id="products" aria-labelledby="spe-products">
      <div class="gpe-box__head">
        <h2 id="spe-products">ສິນຄ້າທີ່ XTK ຂາຍຈາກ <?= e($saved['name']) ?> <span class="gpe-box__count num"><?= e($n($productCount)) ?></span></h2>
        <?php if ($products !== []): ?><span class="gpe-box__note">"ຫຼັກ" = ຜູ້ສະໜອງທີ່ສະແດງກ່ອນ ໃນໜ້າສິນຄ້າ ແລະ ກາດສິນຄ້າ</span><?php endif; ?>
      </div>
      <?php if ($products === []): ?>
        <div class="ibx-empty">
          <span class="ibx-empty__ico"><?= $i('box') ?></span>
          <p><b>ຍັງບໍ່ໄດ້ເຊື່ອມສິນຄ້າ</b><br><span class="muted">ເລືອກສິນຄ້າໃນກ່ອງຂ້າງລຸ່ມ ເພື່ອບອກລູກຄ້າວ່າ XTK ຂາຍຫຍັງຈາກຜູ້ສະໜອງນີ້.</span></p>
        </div>
      <?php else: ?>
        <ul class="ibx-list" aria-label="ສິນຄ້າທີ່ເຊື່ອມແລ້ວ">
          <?php foreach ($products as $p): $pOn = (int) $p['is_visible'] === 1; $primary = (int) $p['is_primary'] === 1; ?>
            <li class="ibx-row dv-row ibx-row--<?= $pOn ? ($primary ? 'accent' : 'success') : 'muted' ?><?= $pOn ? '' : ' is-hidden' ?>">
              <a class="ibx-row__main" href="/admin/products/<?= e($p['id']) ?>">
                <span class="dv-thumb spe-thumb"><span class="dv-thumb__ico"><?= $i('box') ?></span></span>
                <span class="ibx-row__text">
                  <span class="ibx-row__top"><b title="<?= e($p['name']) ?>"><?= e($p['name']) ?></b><?php if ($primary): ?><span class="sp-star"><?= $i('star') ?>ຫຼັກ</span><?php endif; ?></span>
                  <span class="ibx-row__meta">
                    <span class="ibx-status"><i aria-hidden="true"></i><?= $pOn ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                    <span aria-hidden="true">·</span><span><?= e($p['division_name'] . ' › ' . $p['group_name']) ?></span>
                    <?php if ((int) $p['supplier_count'] > 1): ?><span aria-hidden="true">·</span><span><?= e($p['supplier_count']) ?> ຜູ້ສະໜອງ</span><?php endif; ?>
                  </span>
                </span>
              </a>
              <div class="dv-acts">
                <?php if (!$primary): ?>
                  <form method="post" action="/admin/suppliers/<?= e($id) ?>/primary"><?= Csrf::field() ?><input type="hidden" name="product_id" value="<?= e($p['id']) ?>"><button class="gp-div__add" type="submit" title="ໃຫ້ຜູ້ສະໜອງນີ້ສະແດງກ່ອນໃນໜ້າສິນຄ້າ"><?= $i('star') ?><span>ຕັ້ງເປັນຫຼັກ</span></button></form>
                <?php endif; ?>
                <form method="post" action="/admin/suppliers/<?= e($id) ?>/products/remove" data-confirm="ເອົາ <?= e($p['name']) ?> ອອກຈາກຜູ້ສະໜອງນີ້?"><?= Csrf::field() ?><input type="hidden" name="product_id" value="<?= e($p['id']) ?>"><button class="ibx-icon-btn spe-remove" type="submit" aria-label="ເອົາ <?= e($p['name']) ?> ອອກ" title="ເອົາອອກ"><?= $i('x') ?></button></form>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <form class="ibx-box" method="post" action="/admin/suppliers/<?= e($id) ?>/products" aria-labelledby="spe-add">
      <?= Csrf::field() ?>
      <div class="gpe-box__head">
        <h2 id="spe-add">ເພີ່ມສິນຄ້າ</h2>
        <?php if ($unlinked !== []): ?>
          <div class="ibx-search spe-search">
            <?= $i('search') ?>
            <input id="pick-q" type="search" data-filter-for="pick" placeholder="ພິມຊື່ສິນຄ້າ ຫຼື ກຸ່ມ" aria-label="ຄົ້ນຫາສິນຄ້າ">
          </div>
        <?php endif; ?>
      </div>
      <?php if ($unlinked === []): ?>
        <p class="gp-none">ເຊື່ອມສິນຄ້າຄົບທຸກລາຍການແລ້ວ.</p>
      <?php else: ?>
        <div class="gpe-box__body">
          <div class="picker spe-picker" id="pick">
            <?php foreach ($byDivision as $division => $items): ?>
              <fieldset>
                <legend><?= e($division) ?> <span class="muted"><?= e(count($items)) ?></span></legend>
                <?php foreach ($items as $p): ?>
                  <label class="check" data-text="<?= e(mb_strtolower($p['name'] . ' ' . $p['group_name'])) ?>"><input type="checkbox" name="product_ids[]" value="<?= e($p['id']) ?>"><span><?= e($p['name']) ?> <small class="muted"><?= e($p['group_name']) ?><?= (int) $p['is_visible'] ? '' : ' · ເຊື່ອງຢູ່' ?></small></span></label>
                <?php endforeach; ?>
              </fieldset>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="spe-add__foot"><button class="btn" type="submit">ເພີ່ມສິນຄ້າທີ່ເລືອກ</button></div>
      <?php endif; ?>
    </form>

    <form class="gpe-danger" method="post" action="/admin/suppliers/<?= e($id) ?>/delete" data-confirm="ລຶບຜູ້ສະໜອງ <?= e($saved['name']) ?>? ສິນຄ້າຈະບໍ່ຖືກລຶບ.">
      <?= Csrf::field() ?>
      <span class="gpe-danger__ico"><?= $i('trash') ?></span>
      <div class="gpe-danger__text">
        <h2>ລຶບຜູ້ສະໜອງ</h2>
        <p>ການເຊື່ອມກັບສິນຄ້າຈະຖືກລຶບ, ສິນຄ້າບໍ່ຖືກລຶບ.<?= $published ? ' ໜ້າເກົ່າຂອງຜູ້ສະໜອງນີ້ຈະພາໄປໜ້າລວມຜູ້ສະໜອງ.' : '' ?></p>
      </div>
      <button class="btn btn--sm btn--danger" type="submit">ລຶບ</button>
    </form>
  <?php endif; ?>
</div>
