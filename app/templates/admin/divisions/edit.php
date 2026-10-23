<?php
/** @var array $d @var array $errors @var ?array $image @var array $images @var bool $published */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\DivisionController as DC;

$isNew = (int) $d['id'] === 0;
?>
<p class="crumb"><a href="/admin/divisions">← ຂະແໜງທຸລະກິດ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/divisions/new' : '/admin/divisions/' . e($d['id']) ?>">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => DC::FIELDS, 'row' => $d, 'errors' => $errors]) ?>
  </div>
  <div class="stack">
    <div class="card stack">
      <div class="field">
        <label for="slug">ຊື່ໜ້າເວັບ</label>
        <input id="slug" name="slug" maxlength="64" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($d['slug']) ?>"<?= isset($errors['slug']) ? ' aria-invalid="true" aria-describedby="slug-err"' : '' ?>>
        <?php if (isset($errors['slug'])): ?><span class="field__err" id="slug-err"><?= e($errors['slug']) ?></span><?php endif; ?>
        <span class="hint">ໜ້າເວັບ: <b><?= e($d['slug'] ?: '…') ?></b>.html. ປ່ອຍຫວ່າງ = ສ້າງຈາກຊື່ອັງກິດ.<?= $published ? ' ຖ້າປ່ຽນ, URL ເກົ່າຈະພາໄປ URL ໃໝ່ເອງ.' : '' ?></span>
      </div>
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $d['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
    </div>
    <div class="card"><?= View::render('admin/partials/icon-picker', ['name' => 'icon', 'current' => $d['icon'], 'error' => $errors['icon'] ?? null]) ?></div>
    <div class="card"><?= View::render('admin/partials/media-picker', ['name' => 'image_id', 'label' => 'ຮູບຂະແໜງ', 'current' => $image, 'images' => $images, 'error' => $errors['image_id'] ?? null]) ?></div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມຂະແໜງ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/divisions">ຍົກເລີກ</a><?= $isNew ? '' : View::render('admin/partials/preview-link', ['page' => $d['slug']]) ?></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/divisions/<?= e($d['id']) ?>/delete" data-confirm="ລຶບຂະແໜງ <?= e($d['name_lo']) ?>?">
    <?= Csrf::field() ?>
    <h2>ລຶບຂະແໜງ</h2>
    <p>ລຶບໄດ້ສະເພາະຂະແໜງທີ່ບໍ່ມີກຸ່ມສິນຄ້າ. ຖ້າຢາກເອົາອອກຈາກເວັບຊົ່ວຄາວ, ໃຫ້ປິດ "ສະແດງໃນໜ້າເວັບ" ແທນ.<?= $published ? ' ໜ້າເກົ່າຈະພາໄປໜ້າລວມຜະລິດຕະພັນ.' : '' ?></p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
