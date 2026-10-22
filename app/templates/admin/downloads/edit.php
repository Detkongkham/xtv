<?php
/** @var array $dl @var array $errors @var array $divisions @var array $pdfs id => label */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\DownloadController as DC;
use App\Service\MediaService;

$isNew = (int) $dl['id'] === 0;
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
?>
<p class="crumb"><a href="/admin/downloads">← ໄຟລ໌ດາວໂຫຼດ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/downloads/new' : '/admin/downloads/' . e($dl['id']) ?>" data-busy="ກຳລັງອັບ…">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => DC::FIELDS, 'row' => $dl, 'errors' => $errors]) ?>
  </div>
  <div class="stack">
    <div class="card stack">
      <h2>ໄຟລ໌ PDF <em class="req">*</em></h2>
      <div class="field">
        <label for="file_upload">ອັບໄຟລ໌</label>
        <input id="file_upload" name="file_upload" type="file" accept="application/pdf"<?= $inv('file_id') ?>>
        <span class="hint">PDF ເທົ່ານັ້ນ, ສູງສຸດ <?= MediaService::PDF_MAX_BYTES >> 20 ?> MB.</span>
      </div>
      <?php if ($pdfs !== []): ?>
        <div class="field">
          <label for="file_id">ຫຼື ເລືອກຈາກຄັງສື່</label>
          <select id="file_id" name="file_id"<?= $inv('file_id') ?>>
            <option value="">— ເລືອກ —</option>
            <?php foreach ($pdfs as $id => $label): ?><option value="<?= e($id) ?>"<?= (int) $dl['file_id'] === (int) $id ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <?= $err('file_id') ?>
    </div>
    <div class="card stack">
      <div class="field">
        <label for="division_id">ເປັນຂອງ</label>
        <select id="division_id" name="division_id"<?= $inv('division_id') ?>>
          <option value="">ບໍລິສັດ (ເຊັ່ນ Company Profile)</option>
          <?php foreach ($divisions as $id => $name): ?><option value="<?= e($id) ?>"<?= (int) $dl['division_id'] === (int) $id ? ' selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
        </select><?= $err('division_id') ?>
      </div>
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $dl['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
    </div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/downloads">ຍົກເລີກ</a></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/downloads/<?= e($dl['id']) ?>/delete" data-confirm="ລຶບ <?= e($dl['title_lo']) ?> ອອກຈາກໜ້າດາວໂຫຼດ?">
    <?= Csrf::field() ?>
    <h2>ລຶບອອກຈາກໜ້າດາວໂຫຼດ</h2>
    <p>ໄຟລ໌ PDF ຍັງຢູ່ໃນຄັງສື່ (ລຶບໄດ້ຢູ່ທີ່ນັ້ນ).</p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
