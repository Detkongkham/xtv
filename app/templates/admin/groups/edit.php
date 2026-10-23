<?php
/**
 * @var array $g @var array $errors @var array $divisions id => name @var int $productCount
 * @var array $products first page of ProductRepository::search() for this group @var ?array $saved the stored row (null when new)
 * @var array $siblings groups in the saved row's division, in order
 */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\GroupController as GC;
use App\Http\Admin\MediaController as MC;

$isNew = (int) $g['id'] === 0;
$id = (int) $g['id'];

// lucide icon paths, drawn by $i()
$icons = [
    'arrow-left' => 'M19 12H5M12 19l-7-7 7-7',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'eye-off' => 'M10.73 5.08A10.43 10.43 0 0112 5c7 0 10 7 10 7a13.16 13.16 0 01-1.67 2.68M6.61 6.61A13.53 13.53 0 002 12s3 7 10 7a9.74 9.74 0 005.39-1.61M14.12 14.12a3 3 0 11-4.24-4.24M2 2l20 20',
    'layers' => 'M12.83 2.18a2 2 0 00-1.66 0L2.6 6.08a1 1 0 000 1.83l8.58 3.91a2 2 0 001.66 0l8.58-3.9a1 1 0 000-1.83zM2 12a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 12M2 17a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 17',
    'languages' => 'M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10M14 18h6',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'hash' => 'M4 9h16M4 15h16M10 3L8 21M16 3l-2 18',
    'alert' => 'M21.73 18l-8-14a2 2 0 00-3.48 0l-8 14A2 2 0 004 21h16a2 2 0 001.73-3M12 9v4M12 17h.01',
    'tag' => 'M12.59 2.59A2 2 0 0011.17 2H4a2 2 0 00-2 2v7.17a2 2 0 00.59 1.42l8.7 8.7a2.43 2.43 0 003.42 0l6.58-6.58a2.43 2.43 0 000-3.42zM7.5 8a.5.5 0 100-1 .5.5 0 000 1z',
    'star' => 'M11.53 2.3a.53.53 0 01.94 0l2.31 4.68a2.12 2.12 0 001.6 1.16l5.16.76a.53.53 0 01.3.9l-3.74 3.64a2.12 2.12 0 00-.61 1.88l.88 5.14a.53.53 0 01-.77.56l-4.62-2.43a2.12 2.12 0 00-1.97 0L6.4 21.01a.53.53 0 01-.77-.56l.88-5.14a2.12 2.12 0 00-.61-1.88L2.16 9.79a.53.53 0 01.3-.9l5.16-.76a2.12 2.12 0 001.6-1.16z',
    'trash' => 'M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$siteIcons = require App\Core\App::path('templates/site/partials/icons.php');
$n = static fn (int $v): string => number_format($v);

$on = (int) ($g['is_visible'] ?? 0) === 1;
$divId = (int) ($saved['division_id'] ?? $g['division_id'] ?? 0);
$divName = $divisions[$divId] ?? '—';
$pos = array_search($id, array_map('intval', array_column($siblings, 'id')), true);
$divHidden = $siblings !== [] && (int) $siblings[0]['division_visible'] === 0;

// a language counts as done when every translated field has text
$langs = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
$missing = array_values(array_filter($langs, static fn (string $l): bool => array_filter(GC::FIELDS, static fn (array $f): bool => trim((string) ($g[$f[0] . '_' . $l] ?? '')) === '') !== [], ARRAY_FILTER_USE_KEY));
$shownProducts = array_slice($products, 0, 8);

$stat = static function (string $toneName, string $icon, string $label, string $value, string $note, ?string $href = null) use ($i): string {
    $inner = '<span class="ibx-stat__bar" aria-hidden="true"></span><span class="ibx-stat__ico">' . $i($icon) . '</span>'
        . '<span class="ibx-stat__text"><span class="ibx-stat__label">' . e($label) . '</span><b class="ibx-stat__value">' . e($value) . '</b>'
        . '<span class="ibx-stat__note">' . e($note) . '</span></span>';
    $cls = 'ibx-stat ibx-stat--' . $toneName;
    return $href === null
        ? '<div class="' . $cls . '">' . $inner . '</div>'
        : '<a class="' . $cls . '" href="' . e($href) . '">' . $inner . $i('sliders', 'ibx-stat__go') . '</a>';
};
$tileIcon = static fn (?string $key, string $class): string => '<span class="' . $class . '"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="' . e($siteIcons[$key] ?? $icons['box']) . '"/></svg></span>';
?>
<div class="dash ibx gpe">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="gpe-head">
        <?= $tileIcon($saved['icon'] ?? $g['icon'] ?? null, 'gpe-head__ico') ?>
        <div class="dash-head__text">
          <a class="gpe-back" href="/admin/groups<?= $isNew ? '' : '#g' . e($id) ?>"><?= $i('arrow-left') ?>ກຸ່ມສິນຄ້າ</a>
          <h1><?= $isNew ? 'ເພີ່ມກຸ່ມສິນຄ້າ' : e($saved['name_lo']) ?></h1>
          <p class="dash-head__meta">
            <?php if ($isNew): ?>
              ກຸ່ມສິນຄ້າເປັນຫົວຂໍ້ໃນໜ້າຂະແໜງ — ໃສ່ຊື່ທັງ 4 ພາສາ, ເລືອກຂະແໜງ ແລະ ໄອຄອນ
            <?php else: ?>
              <span><?= e($saved['name_en']) ?></span>
              <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('hash') ?><code><?= e($saved['code']) ?></code></span>
            <?php endif; ?>
          </p>
        </div>
      </div>
      <div class="dash-head__tools">
        <?php if (!$isNew): ?>
          <a class="btn-soft" href="/admin/products?group=<?= e($id) ?>"><?= $i('box') ?><span>ສິນຄ້າໃນກຸ່ມ</span></a>
          <a class="btn-soft" href="/admin/divisions/<?= e($divId) ?>"><?= $i('layers') ?><span>ຂະແໜງ</span></a>
        <?php endif; ?>
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
      </div>
    </div>
  </header>

  <?php if (!$isNew): ?>
    <div class="ibx-stats">
      <?= $stat($productCount > 0 ? 'primary' : 'danger', $productCount > 0 ? 'box' : 'alert', 'ສິນຄ້າ', $n($productCount), $productCount > 0 ? 'ໃນກຸ່ມນີ້' : 'ກຸ່ມຍັງວ່າງຢູ່', '/admin/products?group=' . $id) ?>
      <?= (int) $saved['is_visible'] === 1
          ? $stat($divHidden ? 'warning' : 'success', 'eye', 'ການສະແດງ', 'ສະແດງ', $divHidden ? 'ແຕ່ຂະແໜງຍັງເຊື່ອງໄວ້' : 'ເປັນຫົວຂໍ້ໃນໜ້າຂະແໜງ')
          : $stat('warning', 'eye-off', 'ການສະແດງ', 'ເຊື່ອງໄວ້', 'ສິນຄ້າໃນກຸ່ມບໍ່ປາກົດ') ?>
      <?= $stat('info', 'layers', 'ຂະແໜງ', $divisions[(int) $saved['division_id']] ?? '—', $pos === false ? '' : 'ລຳດັບທີ ' . ($pos + 1) . ' ຈາກ ' . count($siblings) . ' ກຸ່ມ', '/admin/groups?division=' . (int) $saved['division_id'] . '#g' . $id) ?>
      <?= $stat($missing === [] ? 'accent' : 'warning', 'languages', 'ພາສາ', (4 - count($missing)) . ' / 4', $missing === [] ? 'ຄົບທຸກພາສາ' : 'ຍັງຂາດ: ' . implode(', ', $missing)) ?>
    </div>
  <?php endif; ?>

  <?php if ($errors !== []): ?>
    <p class="ibx-alert" role="alert"><?= $i('alert') ?>ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
  <?php endif; ?>

  <form class="ibx-cols gpe-cols" method="post" action="<?= $isNew ? '/admin/groups/new' : '/admin/groups/' . e($id) ?>">
    <?= Csrf::field() ?>
    <div class="gpe-main">
      <section class="ibx-box" aria-labelledby="gpe-text">
        <div class="gpe-box__head">
          <h2 id="gpe-text">ຊື່ ແລະ ຄຳອະທິບາຍ</h2>
          <span class="gpe-box__note">ລາວ ແລະ ອັງກິດ ຕ້ອງໃສ່ · ໄທ ແລະ ຈີນ ຫວ່າງໄດ້</span>
        </div>
        <div class="gpe-box__body">
          <?= View::render('admin/partials/lang-fields', ['fields' => GC::FIELDS, 'row' => $g, 'errors' => $errors]) ?>
        </div>
      </section>

      <section class="ibx-box" aria-labelledby="gpe-icon">
        <div class="gpe-box__head">
          <h2 id="gpe-icon">ໄອຄອນ</h2>
          <span class="gpe-box__note">ສະແດງຂ້າງຊື່ກຸ່ມໃນໜ້າຂະແໜງ</span>
        </div>
        <div class="gpe-box__body gpe-icons">
          <?= View::render('admin/partials/icon-picker', ['name' => 'icon', 'current' => $g['icon'], 'error' => $errors['icon'] ?? null]) ?>
        </div>
      </section>

      <?php if (!$isNew): ?>
        <section class="ibx-box" aria-labelledby="gpe-products">
          <div class="gpe-box__head">
            <h2 id="gpe-products">ສິນຄ້າໃນກຸ່ມນີ້ <span class="gpe-box__count num"><?= e($n($productCount)) ?></span></h2>
            <a class="gp-div__add" href="/admin/products/new?group=<?= e($id) ?>"><?= $i('plus') ?><span>ເພີ່ມສິນຄ້າ</span></a>
          </div>
          <?php if ($shownProducts === []): ?>
            <div class="ibx-empty">
              <span class="ibx-empty__ico"><?= $i('box') ?></span>
              <p><b>ຍັງບໍ່ມີສິນຄ້າ</b><br><span class="muted">ກຸ່ມທີ່ບໍ່ມີສິນຄ້າຈະເປັນຫົວຂໍ້ຫວ່າງໃນໜ້າຂະແໜງ.</span></p>
            </div>
          <?php else: ?>
            <ul class="ibx-list" aria-label="ສິນຄ້າໃນກຸ່ມນີ້">
              <?php foreach ($shownProducts as $p): $pOn = (int) $p['is_visible'] === 1; ?>
                <li class="ibx-row dv-row ibx-row--<?= $pOn ? 'success' : 'muted' ?><?= $pOn ? '' : ' is-hidden' ?>">
                  <a class="ibx-row__main" href="/admin/products/<?= e($p['id']) ?>">
                    <span class="dv-thumb">
                      <?php if ($p['image_path']): ?>
                        <img src="<?= e(MC::thumb(['path' => $p['image_path'], 'variants' => $p['image_variants']])) ?>" alt="" loading="lazy" width="80" height="60">
                      <?php else: ?>
                        <span class="dv-thumb__ico" title="ສອບຖາມລາຄາ"><?= $i('tag') ?></span>
                      <?php endif; ?>
                    </span>
                    <span class="ibx-row__text">
                      <span class="ibx-row__top"><b title="<?= e($p['name']) ?>"><?= e($p['name']) ?></b><?php if ((int) $p['is_featured']): ?><span class="sp-star"><?= $i('star') ?>ແນະນຳ</span><?php endif; ?></span>
                      <span class="ibx-row__meta">
                        <span class="ibx-status"><i aria-hidden="true"></i><?= $pOn ? 'ສະແດງ' : 'ເຊື່ອງໄວ້' ?></span>
                        <?php if ($p['supplier_names']): ?><span aria-hidden="true">·</span><span class="pd-suppliers"><?= e($p['supplier_names']) ?></span><?php endif; ?>
                      </span>
                    </span>
                  </a>
                  <div class="dv-acts">
                    <a class="ibx-icon-btn" href="/admin/products/<?= e($p['id']) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $p['name']) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php if ($productCount > count($shownProducts)): ?>
              <p class="dv-foot"><a href="/admin/products?group=<?= e($id) ?>">ເບິ່ງສິນຄ້າທັງໝົດ <?= e($n($productCount)) ?> ລາຍການ →</a></p>
            <?php endif; ?>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </div>

    <aside class="ibx-panel gpe-panel" aria-labelledby="gpe-settings">
      <div class="ibx-panel__head">
        <h2 id="gpe-settings" class="gpe-panel__title">ການຕັ້ງຄ່າ</h2>
      </div>
      <div class="gpe-panel__fields">
        <div class="field">
          <label for="division_id">ຂະແໜງ <em>*</em></label>
          <select id="division_id" name="division_id" required<?= isset($errors['division_id']) ? ' aria-invalid="true" aria-describedby="division_id-err"' : '' ?>>
            <option value="">— ເລືອກ —</option>
            <?php foreach ($divisions as $dId => $name): ?>
              <option value="<?= e($dId) ?>"<?= (int) $g['division_id'] === (int) $dId ? ' selected' : '' ?>><?= e($name) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['division_id'])): ?><span class="field__err" id="division_id-err"><?= e($errors['division_id']) ?></span><?php endif; ?>
          <?php if ($productCount > 0): ?><span class="hint">ຖ້າປ່ຽນຂະແໜງ, ສິນຄ້າ <?= e($productCount) ?> ລາຍການໃນກຸ່ມນີ້ຈະຍ້າຍໄປນຳ.</span><?php endif; ?>
        </div>
        <label class="gpe-toggle">
          <span class="gpe-toggle__text"><b>ສະແດງໃນໜ້າເວັບ</b><span>ປິດໄວ້ = ກຸ່ມ ແລະ ສິນຄ້າໃນກຸ່ມບໍ່ປາກົດ</span></span>
          <input type="checkbox" name="is_visible" value="1" role="switch"<?= $on ? ' checked' : '' ?>>
        </label>
      </div>
      <?php if (!$isNew): ?>
        <dl class="ibx-panel__meta">
          <dt>Anchor</dt><dd><code>#<?= e($g['code']) ?></code></dd>
          <dt>ສິນຄ້າ</dt><dd><?= e($n($productCount)) ?> ລາຍການ</dd>
          <?php if ($pos !== false): ?><dt>ລຳດັບ</dt><dd><?= e($pos + 1) ?> ຈາກ <?= e(count($siblings)) ?> ໃນ <?= e($divName) ?></dd><?php endif; ?>
        </dl>
        <p class="gpe-panel__hint">Anchor ຕັ້ງຕອນສ້າງ ແລະ ບໍ່ປ່ຽນ ເພື່ອໃຫ້ລິ້ງເກົ່າ ແລະ ໜ້າໂຊລູຊັນຍັງໃຊ້ໄດ້.</p>
      <?php endif; ?>
      <div class="gpe-panel__acts">
        <button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມກຸ່ມ' : 'ບັນທຶກ' ?></button>
        <a class="btn btn--ghost" href="/admin/groups">ຍົກເລີກ</a>
      </div>
    </aside>
  </form>

  <?php if (!$isNew): ?>
    <form class="gpe-danger" method="post" action="/admin/groups/<?= e($id) ?>/delete" data-confirm="ລຶບກຸ່ມ <?= e($saved['name_lo']) ?>?">
      <?= Csrf::field() ?>
      <span class="gpe-danger__ico"><?= $i('trash') ?></span>
      <div class="gpe-danger__text">
        <h2>ລຶບກຸ່ມ</h2>
        <p><?= $productCount > 0 ? 'ລຶບໄດ້ສະເພາະກຸ່ມທີ່ບໍ່ມີສິນຄ້າ — ຕອນນີ້ມີ ' . e($n($productCount)) . ' ລາຍການ. ຍ້າຍ ຫຼື ລຶບສິນຄ້າກ່ອນ.' : 'ກຸ່ມນີ້ບໍ່ມີສິນຄ້າ, ລຶບໄດ້. ລຶບແລ້ວກູ້ຄືນບໍ່ໄດ້.' ?></p>
      </div>
      <?php if ($productCount === 0): ?><button class="btn btn--sm btn--danger" type="submit">ລຶບ</button><?php endif; ?>
    </form>
  <?php endif; ?>
</div>
