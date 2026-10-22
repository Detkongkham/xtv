<?php
/** @var array $counts @var int $week @var int $month @var array $latest @var int $mailErrors @var int $withoutSupplier */
use App\Http\Admin\MessageController as MC;
?>
<?php if ($mailErrors > 0): ?>
  <p class="flash flash--error" role="alert">
    ມີ <?= e($mailErrors) ?> ຂໍ້ຄວາມທີ່ສົ່ງອີເມວແຈ້ງເຕືອນບໍ່ສຳເລັດ. ຂໍ້ຄວາມຍັງຢູ່ໃນລະບົບ, ກະລຸນາກວດເບິ່ງໃນກ່ອງຂໍ້ຄວາມ.
  </p>
<?php endif; ?>

<section class="stats" aria-label="ສະຖິຕິຂໍ້ຄວາມ">
  <a class="stat stat--accent" href="/admin/messages?status=new"><b><?= e($counts['new']) ?></b><span>ຂໍ້ຄວາມໃໝ່</span></a>
  <a class="stat" href="/admin/messages?status=in_progress"><b><?= e($counts['in_progress']) ?></b><span>ກຳລັງດຳເນີນການ</span></a>
  <div class="stat"><b><?= e($week) ?></b><span>7 ວັນຫຼ້າສຸດ</span></div>
  <div class="stat"><b><?= e($month) ?></b><span>30 ວັນຫຼ້າສຸດ</span></div>
</section>

<section class="card">
  <div class="card__head">
    <h2>ຂໍ້ຄວາມໃໝ່ຫຼ້າສຸດ</h2>
    <a href="/admin/messages?status=new">ເບິ່ງທັງໝົດ →</a>
  </div>
  <?php if ($latest === []): ?>
    <p class="empty">ບໍ່ມີຂໍ້ຄວາມໃໝ່.</p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($latest as $m): ?>
        <li>
          <a href="/admin/messages/<?= e($m['id']) ?>">
            <b><?= e($m['name']) ?></b><?php if ($m['org']): ?> · <?= e($m['org']) ?><?php endif; ?>
            <small><?= e(MC::SUBJECT_NAMES[$m['subject']] ?? $m['subject']) ?> · <?= e(MC::LANG_NAMES[$m['lang']] ?? $m['lang']) ?> · <?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></small>
          </a>
          <?php if ($m['mail_error']): ?><span class="tag tag--error">ອີເມວບໍ່ສຳເລັດ</span><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<?php if ($withoutSupplier > 0): ?>
<section class="card">
  <div class="card__head"><h2>ຜູ້ສະໜອງ</h2><a href="/admin/products?show=nosupplier">ເບິ່ງລາຍການ →</a></div>
  <p>ສິນຄ້າທີ່ຍັງບໍ່ລະບຸຜູ້ສະໜອງ: <b><?= e($withoutSupplier) ?></b>. ເປີດຜູ້ສະໜອງ ແລ້ວກົດ "ເພີ່ມສິນຄ້າ" ຈົນຄົບ.</p>
</section>
<?php endif; ?>
