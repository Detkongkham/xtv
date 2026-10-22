<?php
/**
 * @var array $p @var array $specs @var list<int> $supplierIds @var array $errors @var array $groups @var array $supplierOptions
 * @var ?array $image @var array $images @var bool $published
 */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\ProductController as PC;
use App\Repository\ProductRepository;

$isNew = (int) $p['id'] === 0;
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
?>
<p class="crumb"><a href="/admin/products<?= $p['group_id'] ? '?group=' . e($p['group_id']) : '' ?>">← ສິນຄ້າ</a><?php if ($published): ?> · <a href="/lo/product-<?= e($p['slug']) ?>.html" target="_blank" rel="noopener">ເບິ່ງໜ້າເວັບ ↗</a><?php endif; ?></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>

<form class="grid2" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/products/new' : '/admin/products/' . e($p['id']) ?>">
  <?= Csrf::field() ?>
  <div class="stack">
    <div class="card stack">
      <div class="field">
        <label for="name">ຊື່ສິນຄ້າ <em>*</em></label>
        <input id="name" name="name" required maxlength="160" value="<?= e($p['name']) ?>"<?= $inv('name') ?>><?= $err('name') ?>
        <span class="hint">ຊື່ດຽວທຸກພາສາ (ຕາມຊື່ທີ່ຜູ້ຜະລິດໃຊ້).</span>
      </div>
      <div class="field">
        <label for="slug">ຊື່ໜ້າເວັບ</label>
        <input id="slug" name="slug" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($p['slug']) ?>"<?= $inv('slug') ?>><?= $err('slug') ?>
        <span class="hint">ໜ້າເວັບ: product-<b><?= e($p['slug'] ?: '…') ?></b>.html. ປ່ອຍຫວ່າງ = ສ້າງຈາກຊື່.<?= $published ? ' ຖ້າປ່ຽນ, URL ເກົ່າຈະພາໄປ URL ໃໝ່ເອງ.' : '' ?></span>
      </div>
      <div class="field">
        <label for="group_id">ກຸ່ມ <em>*</em></label>
        <select id="group_id" name="group_id" required<?= $inv('group_id') ?>>
          <option value="">— ເລືອກ —</option>
          <?php foreach ($groups as $id => $label): ?><option value="<?= e($id) ?>"<?= (int) $p['group_id'] === (int) $id ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select><?= $err('group_id') ?>
      </div>
      <div class="field">
        <label for="variants_note">ລຸ້ນ / ລາຍການຍ່ອຍ</label>
        <input id="variants_note" name="variants_note" maxlength="400" placeholder="T3 · T4 · TSH · HbA1c" value="<?= e($p['variants_note'] ?? '') ?>"<?= $inv('variants_note') ?>><?= $err('variants_note') ?>
        <span class="hint">ແຖວນ້ອຍໃຕ້ຄຳອະທິບາຍ, ຄືກັນທຸກພາສາ.</span>
      </div>
    </div>

    <div class="card stack">
      <h2>ຄຳອະທິບາຍ</h2>
      <?= View::render('admin/partials/lang-fields', ['fields' => PC::FIELDS, 'row' => $p, 'errors' => $errors]) ?>
    </div>

    <div class="card stack" id="specs">
      <h2>ຂໍ້ມູນຈຳເພາະ</h2>
      <p class="hint">ແຖວ ປ້າຍ / ຄ່າ ໃນຕາຕະລາງຂອງໜ້າສິນຄ້າ (ຂະແໜງ, ກຸ່ມ ແລະ ຜູ້ສະໜອງ ໃສ່ໃຫ້ເອງ). ບໍ່ມີແຖວ = ສະແດງແຖວທົ່ວໄປ (ລຸ້ນ, ມາດຕະຖານ, ການຮັບປະກັນ, ການຕິດຕັ້ງ). ລາວ ແລະ ອັງກິດ ບັງຄັບ.</p>
      <?php if (isset($errors['specs'])): ?><p class="field__err" role="alert"><?= e($errors['specs']) ?></p><?php endif; ?>
      <div class="spec-rows" data-spec-rows>
        <?php foreach ($specs as $n => $s): ?><?= $specRow((string) $n, $s) ?><?php endforeach; ?>
        <?= $specRow('new', []) ?>
      </div>
      <template data-spec-template><?= $specRow('__N__', []) ?></template>
      <div class="btns"><button class="btn btn--ghost btn--sm" type="button" data-spec-add hidden>+ ເພີ່ມແຖວ</button></div>
    </div>

    <details class="card stack">
      <summary><h2 style="display:inline">SEO (ບໍ່ບັງຄັບ)</h2></summary>
      <?= View::render('admin/partials/lang-fields', ['fields' => PC::SEO_FIELDS, 'row' => $p, 'errors' => $errors]) ?>
    </details>
  </div>

  <div class="stack">
    <div class="card">
      <?= View::render('admin/partials/media-picker', ['name' => 'image_id', 'label' => 'ຮູບສິນຄ້າ', 'current' => $image, 'images' => $images, 'error' => $errors['image_id'] ?? null]) ?>
      <p class="hint" style="margin-top:12px">ມີຮູບ = ມີໜ້າລາຍລະອຽດຂອງຕົນເອງ. ບໍ່ມີຮູບ = ກາດ "ສອບຖາມລາຄາ" ທີ່ລິ້ງໄປໜ້າຕິດຕໍ່.</p>
    </div>
    <div class="card stack">
      <h2>ການສະແດງ</h2>
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $p['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
      <div class="field">
        <label class="check"><input type="checkbox" name="is_featured" value="1"<?= (int) $p['is_featured'] ? ' checked' : '' ?><?= $inv('is_featured') ?>><span>ສິນຄ້າແນະນຳໃນໜ້າຫຼັກ (ສູງສຸດ <?= ProductRepository::MAX_FEATURED ?>, ຕ້ອງມີຮູບ)</span></label><?= $err('is_featured') ?>
      </div>
    </div>
    <fieldset class="card stack">
      <legend class="sr">ຜູ້ສະໜອງ</legend>
      <h2>ຜູ້ສະໜອງ</h2>
      <?php if ($supplierOptions === []): ?>
        <p class="empty">ຍັງບໍ່ມີຜູ້ສະໜອງ. <a href="/admin/suppliers/new">ເພີ່ມຜູ້ສະໜອງ</a></p>
      <?php else: ?>
        <p class="hint">ໝາຍຜູ້ສະໜອງທີ່ XTK ຊື້ສິນຄ້ານີ້ມາ. "ຫຼັກ" = ສະແດງກ່ອນ.</p>
        <table class="table sup-pick">
          <thead><tr><th scope="col">ຜູ້ສະໜອງ</th><th scope="col">ຫຼັກ</th></tr></thead>
          <tbody>
            <?php foreach ($supplierOptions as $sid => $name): $on = in_array((int) $sid, $supplierIds, true) ?>
              <tr>
                <td><label class="check"><input type="checkbox" name="supplier_ids[]" value="<?= e($sid) ?>"<?= $on ? ' checked' : '' ?>><span><?= e($name) ?></span></label></td>
                <td><input type="radio" name="primary_supplier" value="<?= e($sid) ?>" aria-label="<?= e($name) ?> ເປັນຫຼັກ"<?= ($supplierIds[0] ?? 0) === (int) $sid ? ' checked' : '' ?>></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </fieldset>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມສິນຄ້າ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/products">ຍົກເລີກ</a></div>
  </div>
</form>

<?php if (!$isNew): ?>
  <div class="grid2">
    <form class="card stack" method="post" action="/admin/products/<?= e($p['id']) ?>/copy">
      <?= Csrf::field() ?>
      <h2>ສຳເນົາສິນຄ້າ</h2>
      <p>ສ້າງສິນຄ້າໃໝ່ທີ່ມີຂໍ້ມູນຄືກັນ (ຮູບ, ຄຳອະທິບາຍ, ຂໍ້ມູນຈຳເພາະ, ຜູ້ສະໜອງ). ສະບັບສຳເນົາຈະຖືກເຊື່ອງໄວ້ ຈົນກວ່າຈະເປີດ.</p>
      <div class="btns"><button class="btn btn--ghost" type="submit">ສຳເນົາ</button></div>
    </form>
    <form class="card danger" method="post" action="/admin/products/<?= e($p['id']) ?>/delete" data-confirm="ລຶບສິນຄ້າ <?= e($p['name']) ?>?">
      <?= Csrf::field() ?>
      <h2>ລຶບສິນຄ້າ</h2>
      <p>ຂໍ້ມູນຈຳເພາະ ແລະ ການເຊື່ອມກັບຜູ້ສະໜອງຈະຖືກລຶບນຳ. ຮູບຍັງຢູ່ໃນຄັງສື່.<?= $published ? ' ໜ້າເກົ່າຈະພາໄປໜ້າຂະແໜງ.' : '' ?> ຖ້າຢາກເອົາອອກຊົ່ວຄາວ ໃຫ້ປິດ "ສະແດງ" ແທນ.</p>
      <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
    </form>
  </div>
<?php endif; ?>
