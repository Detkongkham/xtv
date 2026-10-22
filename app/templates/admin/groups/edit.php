<?php
/** @var array $g @var array $errors @var array $divisions id => name @var int $productCount */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\GroupController as GC;

$isNew = (int) $g['id'] === 0;
?>
<p class="crumb"><a href="/admin/groups">← ກຸ່ມສິນຄ້າ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" action="<?= $isNew ? '/admin/groups/new' : '/admin/groups/' . e($g['id']) ?>">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => GC::FIELDS, 'row' => $g, 'errors' => $errors]) ?>
  </div>
  <div class="stack">
    <div class="card stack">
      <div class="field">
        <label for="division_id">ຂະແໜງ <em>*</em></label>
        <select id="division_id" name="division_id" required<?= isset($errors['division_id']) ? ' aria-invalid="true" aria-describedby="division_id-err"' : '' ?>>
          <option value="">— ເລືອກ —</option>
          <?php foreach ($divisions as $id => $name): ?>
            <option value="<?= e($id) ?>"<?= (int) $g['division_id'] === (int) $id ? ' selected' : '' ?>><?= e($name) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['division_id'])): ?><span class="field__err" id="division_id-err"><?= e($errors['division_id']) ?></span><?php endif; ?>
        <?php if ($productCount > 0): ?><span class="hint">ຖ້າປ່ຽນຂະແໜງ, ສິນຄ້າ <?= e($productCount) ?> ລາຍການໃນກຸ່ມນີ້ຈະຍ້າຍໄປນຳ.</span><?php endif; ?>
      </div>
      <?php if (!$isNew): ?>
        <p class="hint">Anchor: <code>#<?= e($g['code']) ?></code> (ຕັ້ງຕອນສ້າງ ແລະ ບໍ່ປ່ຽນ ເພື່ອໃຫ້ລິ້ງເກົ່າ ແລະ ໜ້າໂຊລູຊັນຍັງໃຊ້ໄດ້).</p>
      <?php endif; ?>
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $g['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
    </div>
    <div class="card"><?= View::render('admin/partials/icon-picker', ['name' => 'icon', 'current' => $g['icon'], 'error' => $errors['icon'] ?? null]) ?></div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມກຸ່ມ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/groups">ຍົກເລີກ</a></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/groups/<?= e($g['id']) ?>/delete" data-confirm="ລຶບກຸ່ມ <?= e($g['name_lo']) ?>?">
    <?= Csrf::field() ?>
    <h2>ລຶບກຸ່ມ</h2>
    <p>ລຶບໄດ້ສະເພາະກຸ່ມທີ່ບໍ່ມີສິນຄ້າ<?= $productCount > 0 ? ' (ຕອນນີ້ມີ ' . e($productCount) . ' ລາຍການ)' : '' ?>.</p>
    <?php if ($productCount === 0): ?><div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div><?php endif; ?>
  </form>
<?php endif; ?>
