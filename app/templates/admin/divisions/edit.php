<?php
/**
 * @var array $d @var array $errors @var ?array $image @var array $images @var bool $published
 * @var ?array $saved the division as saved, with counts and its place in the menu (pos of `of`) @var array $groups its groups, in order
 */
use App\Core\App;
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\DivisionController as DC;
use App\Publish\PagePreview;

// lucide icon paths, drawn by $i()
$icons = [
    'arrow-left' => 'M19 12H5M12 19l-7-7 7-7',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'folder' => 'M20 20a2 2 0 002-2V8a2 2 0 00-2-2h-7.9a2 2 0 01-1.69-.9L9.6 3.9A2 2 0 007.93 3H4a2 2 0 00-2 2v13a2 2 0 002 2z',
    'languages' => 'M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10M14 18h6',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'external' => 'M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'file' => 'M15 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7zM14 2v4a2 2 0 002 2h4',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'trash' => 'M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$siteIcons = require App::path('templates/site/partials/icons.php');
// a site icon (the one shown on the public pages), falling back to the box
$siteIcon = static fn (?string $key): string => '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="' . e($siteIcons[$key] ?? $icons['box']) . '"/></svg>';
$n = static fn (int $v): string => number_format($v);

$id = (int) $d['id'];
$isNew = $id === 0;
$on = (int) $d['is_visible'] === 1;
$groupCount = (int) ($saved['group_count'] ?? 0);
$productCount = (int) ($saved['product_count'] ?? 0);
$langs = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
$missing = array_values(array_map(static fn (string $l): string => $langs[$l], array_filter(array_keys($langs), static fn (string $l): bool => trim((string) ($saved['name_' . $l] ?? '')) === '')));

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
<div class="dash ibx gpe de">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="gpe-head">
        <span class="gpe-head__ico"><?= $siteIcon($saved['icon'] ?? $d['icon'] ?? null) ?></span>
        <div class="dash-head__text">
          <a class="gpe-back" href="/admin/divisions<?= $isNew ? '' : '#d' . e($id) ?>"><?= $i('arrow-left') ?>ຂະແໜງທຸລະກິດ</a>
          <h1><?= $isNew ? 'ເພີ່ມຂະແໜງ' : e($saved['name_lo']) ?></h1>
          <p class="dash-head__meta">
            <?php if ($isNew): ?>
              ຂະແໜງມີໜ້າເວັບຂອງຕົນເອງ — ໃສ່ຊື່ທັງ 4 ພາສາ, ເລືອກໄອຄອນ ແລະ ຮູບ, ແລ້ວຈຶ່ງເພີ່ມກຸ່ມສິນຄ້າ
            <?php else: ?>
              <span><?= e($saved['name_en']) ?></span>
              <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('file') ?><code><?= e($saved['slug']) ?>.html</code></span>
            <?php endif; ?>
          </p>
        </div>
      </div>
      <div class="dash-head__tools">
        <?php if (!$isNew): ?>
          <a class="btn-soft" href="/admin/groups?division=<?= e($id) ?>"><?= $i('folder') ?><span>ກຸ່ມສິນຄ້າ</span></a>
          <a class="btn-soft" href="<?= PagePreview::url($saved['slug']) ?>" target="_blank" rel="noopener"><?= $i('external') ?><span>ເບິ່ງຕົວຢ່າງ</span><span class="sr"> (ແທັບໃໝ່)</span></a>
        <?php endif; ?>
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
      </div>
    </div>
  </header>

  <?php if (!$isNew): ?>
    <div class="ibx-stats">
      <?= $stat($groupCount > 0 ? 'primary' : 'danger', $groupCount > 0 ? 'folder' : 'alert', 'ກຸ່ມສິນຄ້າ', $n($groupCount), $groupCount > 0 ? 'ໃນຂະແໜງນີ້' : 'ໜ້າຂະແໜງຍັງວ່າງຢູ່', '/admin/groups?division=' . $id) ?>
      <?= $stat('info', 'box', 'ສິນຄ້າ', $n($productCount), 'ໃນທຸກກຸ່ມຂອງຂະແໜງ', '/admin/products?division=' . $id) ?>
      <?= (int) $saved['is_visible'] === 1
          ? $stat('success', 'eye', 'ການສະແດງ', 'ສະແດງ', $published ? 'ຢູ່ໃນເມນູ ແລະ ມີໜ້າເວັບແລ້ວ' : 'ຍັງບໍ່ໄດ້ເຜີຍແຜ່')
          : $stat('warning', 'eye-off', 'ການສະແດງ', 'ເຊື່ອງໄວ້', 'ບໍ່ມີໜ້າ, ບໍ່ຢູ່ໃນເມນູ') ?>
      <?= $stat($missing === [] ? 'accent' : 'warning', 'languages', 'ພາສາ', (4 - count($missing)) . ' / 4', $missing === [] ? 'ຄົບທຸກພາສາ' : 'ຍັງຂາດ: ' . implode(', ', $missing)) ?>
    </div>
  <?php endif; ?>

  <?php if ($errors !== []): ?>
    <p class="ibx-alert" role="alert"><?= $i('alert') ?>ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
  <?php endif; ?>

  <form class="ibx-cols gpe-cols" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/divisions/new' : '/admin/divisions/' . e($id) ?>">
    <?= Csrf::field() ?>
    <div class="gpe-main">
      <section class="ibx-box" aria-labelledby="de-text">
        <div class="gpe-box__head">
          <h2 id="de-text">ຊື່ ແລະ ຄຳອະທິບາຍ</h2>
          <span class="gpe-box__note">ລາວ ແລະ ອັງກິດ ຕ້ອງໃສ່ · ໄທ ແລະ ຈີນ ຫວ່າງໄດ້</span>
        </div>
        <div class="gpe-box__body">
          <?= View::render('admin/partials/lang-fields', ['fields' => DC::FIELDS, 'row' => $d, 'errors' => $errors]) ?>
        </div>
      </section>

      <section class="ibx-box" aria-labelledby="de-image">
        <div class="gpe-box__head">
          <h2 id="de-image">ຮູບຂະແໜງ</h2>
          <span class="gpe-box__note">ຫົວໜ້າຂອງໜ້າຂະແໜງ ແລະ ກາດໃນໜ້າຫຼັກ</span>
        </div>
        <div class="gpe-box__body de-media">
          <?= View::render('admin/partials/media-picker', ['name' => 'image_id', 'label' => 'ຮູບຂະແໜງ', 'current' => $image, 'images' => $images, 'error' => $errors['image_id'] ?? null]) ?>
        </div>
      </section>

      <section class="ibx-box" aria-labelledby="de-icon">
        <div class="gpe-box__head">
          <h2 id="de-icon">ໄອຄອນ</h2>
          <span class="gpe-box__note">ສະແດງໃນເມນູ ແລະ ກາດຂະແໜງ</span>
        </div>
        <div class="gpe-box__body gpe-icons">
          <?= View::render('admin/partials/icon-picker', ['name' => 'icon', 'current' => $d['icon'], 'error' => $errors['icon'] ?? null]) ?>
        </div>
      </section>

      <?php if (!$isNew): ?>
        <section class="ibx-box" aria-labelledby="de-groups">
          <div class="gpe-box__head">
            <h2 id="de-groups">ກຸ່ມສິນຄ້າໃນຂະແໜງນີ້ <span class="gpe-box__count num"><?= e($n($groupCount)) ?></span></h2>
            <a class="gp-div__add" href="/admin/groups/new?division=<?= e($id) ?>"><?= $i('plus') ?><span>ເພີ່ມກຸ່ມ</span></a>
          </div>
          <?php if ($groups === []): ?>
            <div class="ibx-empty">
              <span class="ibx-empty__ico"><?= $i('folder') ?></span>
              <p><b>ຍັງບໍ່ມີກຸ່ມສິນຄ້າ</b><br><span class="muted">ໜ້າຂະແໜງຈະບໍ່ມີສິນຄ້າ ຈົນກວ່າຈະເພີ່ມກຸ່ມ.</span></p>
            </div>
          <?php else: ?>
            <ul class="ibx-list" aria-label="ກຸ່ມສິນຄ້າໃນຂະແໜງນີ້">
              <?php foreach ($groups as $g): $gOn = (int) $g['is_visible'] === 1; $gCount = (int) $g['product_count']; ?>
                <li class="ibx-row dv-row ibx-row--<?= $gOn ? 'success' : 'muted' ?><?= $gOn ? '' : ' is-hidden' ?>">
                  <a class="ibx-row__main" href="/admin/groups/<?= e($g['id']) ?>">
                    <span class="dv-thumb"><span class="dv-thumb__ico"><?= $siteIcon($g['icon']) ?></span></span>
                    <span class="ibx-row__text">
                      <span class="ibx-row__top"><b title="<?= e($g['name_lo']) ?>"><?= e($g['name_lo']) ?></b></span>
                      <span class="ibx-row__meta">
                        <span class="ibx-status"><i aria-hidden="true"></i><?= $gOn ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                        <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('box') ?><?= e($n($gCount)) ?> ສິນຄ້າ</span>
                        <?php if ($gCount === 0): ?><span class="tag tag--warn">ຍັງບໍ່ມີສິນຄ້າ</span><?php endif; ?>
                      </span>
                    </span>
                  </a>
                  <div class="dv-acts">
                    <a class="ibx-icon-btn" href="/admin/products?group=<?= e($g['id']) ?>" aria-label="<?= e('ສິນຄ້າໃນ ' . $g['name_lo']) ?>" title="ສິນຄ້າໃນກຸ່ມ"><?= $i('box') ?></a>
                    <a class="ibx-icon-btn" href="/admin/groups/<?= e($g['id']) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $g['name_lo']) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
            <p class="dv-foot">ລຳດັບກຸ່ມຄືລຳດັບຫົວຂໍ້ໃນໜ້າຂະແໜງ — ປ່ຽນໄດ້ໃນ <a href="/admin/groups?division=<?= e($id) ?>">ກຸ່ມສິນຄ້າ</a>.</p>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </div>

    <aside class="ibx-panel gpe-panel" aria-labelledby="de-settings">
      <div class="ibx-panel__head">
        <h2 id="de-settings" class="gpe-panel__title">ການຕັ້ງຄ່າ</h2>
      </div>
      <div class="gpe-panel__fields">
        <div class="field">
          <label for="slug">ຊື່ໜ້າເວັບ</label>
          <div class="de-slug">
            <input id="slug" name="slug" maxlength="64" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($d['slug']) ?>" aria-describedby="slug-hint<?= isset($errors['slug']) ? ' slug-err' : '' ?>"<?= isset($errors['slug']) ? ' aria-invalid="true"' : '' ?>>
            <span aria-hidden="true">.html</span>
          </div>
          <?php if (isset($errors['slug'])): ?><span class="field__err" id="slug-err"><?= e($errors['slug']) ?></span><?php endif; ?>
          <span class="hint" id="slug-hint">ປ່ອຍຫວ່າງ = ສ້າງຈາກຊື່ອັງກິດ.<?= $published ? ' ຖ້າປ່ຽນ, URL ເກົ່າຈະພາໄປ URL ໃໝ່ເອງ.' : '' ?></span>
        </div>
        <label class="gpe-toggle">
          <span class="gpe-toggle__text"><b>ສະແດງໃນໜ້າເວັບ</b><span>ປິດໄວ້ = ບໍ່ມີໜ້າ ແລະ ບໍ່ຢູ່ໃນເມນູ</span></span>
          <input type="checkbox" name="is_visible" value="1" role="switch"<?= $on ? ' checked' : '' ?>>
        </label>
      </div>
      <?php if (!$isNew): ?>
        <dl class="ibx-panel__meta">
          <dt>ໜ້າເວັບ</dt><dd><?= $published ? 'ເຜີຍແຜ່ແລ້ວ' : 'ຍັງບໍ່ໄດ້ເຜີຍແຜ່' ?></dd>
          <dt>ກຸ່ມ</dt><dd><?= e($n($groupCount)) ?> ກຸ່ມ · <?= e($n($productCount)) ?> ສິນຄ້າ</dd>
          <?php if ($saved !== null): ?><dt>ລຳດັບ</dt><dd><?= e($saved['pos']) ?> ຈາກ <?= e($saved['of']) ?> ໃນເມນູ</dd><?php endif; ?>
        </dl>
        <p class="gpe-panel__hint">ລຳດັບໃນເມນູປ່ຽນໄດ້ໃນ <a href="/admin/divisions#d<?= e($id) ?>">ລາຍການຂະແໜງ</a>. ບັນທຶກແລ້ວ ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.</p>
      <?php endif; ?>
      <div class="gpe-panel__acts">
        <button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມຂະແໜງ' : 'ບັນທຶກ' ?></button>
        <a class="btn btn--ghost" href="/admin/divisions">ຍົກເລີກ</a>
      </div>
    </aside>
  </form>

  <?php if (!$isNew): ?>
    <form class="gpe-danger" method="post" action="/admin/divisions/<?= e($id) ?>/delete" data-confirm="ລຶບຂະແໜງ <?= e($saved['name_lo']) ?>?">
      <?= Csrf::field() ?>
      <span class="gpe-danger__ico"><?= $i('trash') ?></span>
      <div class="gpe-danger__text">
        <h2>ລຶບຂະແໜງ</h2>
        <p><?= $groupCount > 0
            ? 'ລຶບໄດ້ສະເພາະຂະແໜງທີ່ບໍ່ມີກຸ່ມສິນຄ້າ — ຕອນນີ້ມີ ' . e($n($groupCount)) . ' ກຸ່ມ. ຍ້າຍ ຫຼື ລຶບກຸ່ມກ່ອນ, ຫຼື ປິດ "ສະແດງໃນໜ້າເວັບ" ແທນ.'
            : 'ຂະແໜງນີ້ບໍ່ມີກຸ່ມສິນຄ້າ, ລຶບໄດ້. ລຶບແລ້ວກູ້ຄືນບໍ່ໄດ້.' . ($published ? ' ໜ້າເກົ່າຈະພາໄປໜ້າລວມຜະລິດຕະພັນ.' : '') ?></p>
      </div>
      <?php if ($groupCount === 0): ?><button class="btn btn--sm btn--danger" type="submit">ລຶບ</button><?php endif; ?>
    </form>
  <?php endif; ?>
</div>
