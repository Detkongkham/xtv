<?php
/** @var string $page @var array $row @var array $errors @var array $defaults title_<lang>/desc_<lang> shown while empty */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\SeoController as SC;
?>
<p class="crumb"><a href="/admin/seo">← SEO ໜ້າຄົງທີ່</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" action="/admin/seo/<?= e($page) ?>">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => SC::fields($page), 'row' => $row, 'errors' => $errors, 'placeholders' => $defaults]) ?>
  </div>
  <div class="stack">
    <div class="card stack">
      <h2><?= e(SC::PAGES[$page]) ?></h2>
      <p class="hint"><?= e($page) ?>.html ທັງ 4 ພາສາ. ຖ້າໃສ່ພາສາໃດໜຶ່ງ ຕ້ອງໃສ່ທັງລາວ ແລະ ອັງກິດ; ໄທ ແລະ ຈີນ ຫວ່າງ = ໃຊ້ພາສາອັງກິດ.</p>
      <p class="hint">ລຶບຂໍ້ຄວາມອອກໝົດແລ້ວບັນທຶກ = ກັບໄປໃຊ້ຫົວຂໍ້ປົກກະຕິ.</p>
    </div>
    <div class="btns"><button class="btn" type="submit">ບັນທຶກ</button><a class="btn btn--ghost" href="/admin/seo">ຍົກເລີກ</a></div>
  </div>
</form>
