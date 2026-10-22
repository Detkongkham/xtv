<?php
/** @var array $history @var list<string> $builds @var bool $busy */
use App\Core\Csrf;
use App\Http\Admin\PublishController as PC;

$tagClass = ['success' => 'done', 'failed' => 'error', 'rolled_back' => 'in_progress', 'running' => 'new'];
?>
<section class="card">
  <div class="card__head"><h2>ເຜີຍແຜ່ໜ້າເວັບ</h2></div>
  <p>ສ້າງໜ້າເວັບທັງ 4 ພາສາຈາກຂໍ້ມູນປັດຈຸບັນ, ກວດລິ້ງ ແລ້ວປ່ຽນເວັບທີ່ອອນລາຍໃນຄັ້ງດຽວ. ຖ້າກວດບໍ່ຜ່ານ, ເວັບທີ່ອອນລາຍຈະບໍ່ຖືກແຕະ.</p>
  <?php if ($busy): ?>
    <p class="flash flash--error" role="status">ມີຄົນກຳລັງເຜີຍແຜ່ຢູ່. ກະລຸນາລໍຖ້າຈັກໜ້ອຍ ແລ້ວໂຫຼດໜ້ານີ້ໃໝ່.</p>
  <?php endif; ?>
  <form method="post" action="/admin/publish" data-busy="ກຳລັງເຜີຍແຜ່…">
    <?= Csrf::field() ?>
    <button class="btn" type="submit"<?= $busy ? ' disabled' : '' ?>>ເຜີຍແຜ່ດຽວນີ້</button>
  </form>
</section>

<section class="card">
  <div class="card__head"><h2>ກັບຄືນຊຸດກ່ອນ</h2></div>
  <?php if ($builds === []): ?>
    <p class="empty">ຍັງບໍ່ມີຊຸດເກົ່າ.</p>
  <?php else: ?>
    <p>ເວັບຈະກັບໄປເປັນຄືຊຸດທີ່ເລືອກທັນທີ. ຂໍ້ມູນໃນລະບົບບໍ່ປ່ຽນ: ການເຜີຍແຜ່ຄັ້ງຕໍ່ໄປຈະໃຊ້ຂໍ້ມູນປັດຈຸບັນອີກ.</p>
    <ul class="list">
      <?php foreach ($builds as $i => $id): ?>
        <li>
          <span><b>ຊຸດ <?= e(PC::buildLabel($id)) ?></b><?= $i === 0 ? ' <span class="tag">ຊຸດກ່ອນໜ້ານີ້</span>' : '' ?></span>
          <form method="post" action="/admin/publish/rollback" data-confirm="ກັບຄືນໄປຊຸດ <?= e(PC::buildLabel($id)) ?>?">
            <?= Csrf::field() ?>
            <input type="hidden" name="build" value="<?= e($id) ?>">
            <button class="btn btn--ghost btn--sm" type="submit"<?= $busy ? ' disabled' : '' ?>>ກັບຄືນຊຸດນີ້</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<section class="card table-wrap">
  <div class="card__head" style="padding:24px 24px 0"><h2>ປະຫວັດ</h2></div>
  <?php if ($history === []): ?>
    <p class="empty">ຍັງບໍ່ເຄີຍເຜີຍແຜ່.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th scope="col">ເວລາ</th><th scope="col">ໂດຍ</th><th scope="col">ຜົນ</th><th scope="col">ໜ້າ</th><th scope="col">ໃຊ້ເວລາ</th><th scope="col">ລາຍລະອຽດ</th></tr></thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= e(date('d/m/Y H:i:s', strtotime($h['started_at']))) ?></td>
            <td><?= e($h['user_name'] ?? 'CLI') ?></td>
            <td><span class="tag tag--<?= e($tagClass[$h['status']] ?? 'new') ?>"><?= e(PC::STATUS_NAMES[$h['status']] ?? $h['status']) ?></span></td>
            <td><?= e($h['pages_written'] ?? '—') ?></td>
            <td><?= $h['duration_ms'] === null ? '—' : e(number_format($h['duration_ms'] / 1000, 1)) . ' ວິ' ?></td>
            <td>
              <?php if ($h['status'] === 'rolled_back'): ?>ກັບໄປຊຸດ <?= e(PC::buildLabel($h['build_dir'])) ?><?php endif; ?>
              <?php if ($h['error']): ?><pre class="pre"><?= e($h['error']) ?></pre><?php endif; ?>
              <?php if ($h['warnings']): ?><p class="warn"><?= e($h['warnings']) ?></p><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
