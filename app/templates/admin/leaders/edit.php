<?php
/** @var array $leader @var array $errors @var ?array $photo @var array $images */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\LeaderController as LC;

$isNew = (int) $leader['id'] === 0;
?>
<p class="crumb"><a href="/admin/leaders">← ຜູ້ບໍລິຫານ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/leaders/new' : '/admin/leaders/' . e($leader['id']) ?>" data-busy="ກຳລັງບັນທຶກ…">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => LC::FIELDS, 'row' => $leader, 'errors' => $errors]) ?>
  </div>
  <div class="stack">
    <div class="card stack">
      <?= View::render('admin/partials/media-picker', ['name' => 'photo_id', 'label' => 'ຮູບບຸກຄົນ', 'current' => $photo, 'images' => $images, 'error' => $errors['photo_id'] ?? null]) ?>
      <p class="hint">ຮູບລວງຕັ້ງ (ສັດສ່ວນ 4:5) ກວ້າງຢ່າງໜ້ອຍ 800 px, ໃບໜ້າຢູ່ເຄິ່ງກາງທາງເທິງ. ບໍ່ມີຮູບ = ສະແດງຕົວອັກສອນທຳອິດຂອງຊື່ແທນ.</p>
    </div>
    <div class="card stack">
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $leader['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
    </div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/leaders">ຍົກເລີກ</a><?= $isNew ? '' : View::render('admin/partials/preview-link', ['page' => 'about']) ?></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/leaders/<?= e($leader['id']) ?>/delete" data-confirm="ລຶບ <?= e($leader['name_lo']) ?>?">
    <?= Csrf::field() ?>
    <h2>ລຶບຜູ້ບໍລິຫານ</h2>
    <p>ຮູບຍັງຢູ່ໃນຄັງສື່. ຖ້າຢາກເຊື່ອງໄວ້ຊົ່ວຄາວ, ໃຫ້ເອົາເຄື່ອງໝາຍ "ສະແດງໃນໜ້າເວັບ" ອອກແທນ.</p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
