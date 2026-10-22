<?php
/** @var array $faq @var array $errors */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\FaqController as FC;

$isNew = (int) $faq['id'] === 0;
?>
<p class="crumb"><a href="/admin/faqs">← ຄຳຖາມທີ່ພົບເລື້ອຍ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" action="<?= $isNew ? '/admin/faqs/new' : '/admin/faqs/' . e($faq['id']) ?>">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => FC::FIELDS, 'row' => $faq, 'errors' => $errors]) ?>
  </div>
  <div class="stack">
    <div class="card stack">
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $faq['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
    </div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/faqs">ຍົກເລີກ</a></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/faqs/<?= e($faq['id']) ?>/delete" data-confirm="ລຶບຄຳຖາມ <?= e($faq['question_lo']) ?>?">
    <?= Csrf::field() ?>
    <h2>ລຶບຄຳຖາມ</h2>
    <p>ຖ້າຢາກເຊື່ອງໄວ້ຊົ່ວຄາວ, ໃຫ້ເອົາເຄື່ອງໝາຍ "ສະແດງໃນໜ້າເວັບ" ອອກແທນ.</p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
