<?php
/**
 * @var array $counts @var int $week @var int $month @var array $latest @var int $mailErrors @var int $withoutSupplier
 * @var array $publish see Controller::publishState() @var array $history @var array $gaps see TranslationGapRepository::all()
 */
use App\Core\Csrf;
use App\Http\Admin\MessageController as MC;
use App\Http\Admin\PublishController as PC;
use App\Repository\TranslationGapRepository as TG;

$live = $publish['live'];
$pendingText = [
    'never' => 'ຍັງບໍ່ເຄີຍເຜີຍແຜ່ຈາກລະບົບນີ້.',
    'rolled_back' => 'ເວັບທີ່ອອນລາຍເປັນຊຸດເກົ່າ (ກັບຄືນຊຸດກ່ອນ). ເຜີຍແຜ່ເພື່ອໃຊ້ຂໍ້ມູນປັດຈຸບັນ.',
    'changed' => 'ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່. ໜ້າເວັບຍັງບໍ່ປ່ຽນຈົນກວ່າຈະກົດເຜີຍແຜ່.',
];
$gapCount = array_map(static fn (string $l): int => count(array_filter($gaps, static fn (array $g): bool => isset($g['missing'][$l]))), array_keys(TG::LANGS));
$tagClass = ['success' => 'done', 'failed' => 'error', 'rolled_back' => 'in_progress', 'running' => 'new'];
$gapRow = static function (array $g): string {
    $parts = [];
    foreach ($g['missing'] as $l => $fields) {
        $parts[] = TG::LANGS[$l] . ': ' . implode(', ', $fields);
    }
    return '<li><a href="' . e($g['url']) . '"><b>' . e($g['name']) . '</b><small>' . e($g['type'] . ' · ' . implode(' · ', $parts)) . '</small></a></li>';
};
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

<section class="card publish-state<?= $publish['reason'] ? ' publish-state--pending' : '' ?>" aria-labelledby="publish-h">
  <div class="card__head"><h2 id="publish-h">ສະຖານະເຜີຍແຜ່</h2><a href="/admin/publish">ປະຫວັດ ແລະ ກັບຄືນ →</a></div>
  <?php if ($publish['reason']): ?>
    <p class="warn" role="status"><?= e($pendingText[$publish['reason']]) ?></p>
  <?php else: ?>
    <p><span class="tag tag--done">ເປັນປັດຈຸບັນ</span> ໜ້າເວັບກົງກັບຂໍ້ມູນໃນລະບົບ.</p>
  <?php endif; ?>
  <p class="hint">
    <?php if ($live): ?>ເຜີຍແຜ່ລ່າສຸດ: <?= e(date('d/m/Y H:i', strtotime($live['started_at']))) ?> ໂດຍ <?= e($live['user_name'] ?? 'CLI') ?>.<?php endif; ?>
    <?php if ($publish['changedAt']): ?>ແກ້ໄຂຂໍ້ມູນລ່າສຸດ: <?= e(date('d/m/Y H:i', strtotime($publish['changedAt']))) ?>.<?php endif; ?>
  </p>
  <?php if ($publish['reason']): ?>
    <form method="post" action="/admin/publish" data-busy="ກຳລັງເຜີຍແຜ່…">
      <?= Csrf::field() ?>
      <button class="btn" type="submit">ເຜີຍແຜ່ດຽວນີ້</button>
    </form>
  <?php endif; ?>
  <?php if ($history !== []): ?>
    <ul class="list">
      <?php foreach ($history as $h): ?>
        <li><span><?= e(date('d/m/Y H:i', strtotime($h['started_at']))) ?> · <?= e($h['user_name'] ?? 'CLI') ?><?= $h['pages_written'] ? ' · ' . e($h['pages_written']) . ' ໜ້າ' : '' ?></span>
          <span class="tag tag--<?= e($tagClass[$h['status']] ?? 'new') ?>"><?= e(PC::STATUS_NAMES[$h['status']] ?? $h['status']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
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

<section class="card" aria-labelledby="gaps-h">
  <div class="card__head"><h2 id="gaps-h">ຄຳແປທີ່ຍັງຂາດ</h2></div>
  <?php if ($gaps === []): ?>
    <p><span class="tag tag--done">ຄົບ</span> ທຸກລາຍການທີ່ສະແດງໃນເວັບມີພາສາໄທ ແລະ ຈີນຄົບ.</p>
  <?php else: ?>
    <p>ຂາດພາສາໄທ <b><?= e($gapCount[0]) ?></b> ລາຍການ, ພາສາຈີນ <b><?= e($gapCount[1]) ?></b> ລາຍການ. ຊ່ອງທີ່ຂາດຈະສະແດງເປັນພາສາອັງກິດແທນ.</p>
    <ul class="list"><?php foreach (array_slice($gaps, 0, 8) as $g): ?><?= $gapRow($g) ?><?php endforeach; ?></ul>
    <?php if (count($gaps) > 8): ?>
      <details class="more">
        <summary>ເບິ່ງອີກ <?= e(count($gaps) - 8) ?> ລາຍການ</summary>
        <ul class="list"><?php foreach (array_slice($gaps, 8) as $g): ?><?= $gapRow($g) ?><?php endforeach; ?></ul>
      </details>
    <?php endif; ?>
  <?php endif; ?>
</section>
