<?php
/** @var array $rows */
use App\Core\Csrf;
use App\Core\View;

$last = count($rows) - 1;
$size = static fn (int $b): string => $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
?>
<div class="bar">
  <p><b><?= e(count($rows)) ?></b> ໄຟລ໌</p>
  <a class="btn btn--sm" href="/admin/downloads/new">+ ເພີ່ມໄຟລ໌</a>
</div>
<?php if ($rows === []): ?>
  <div class="card empty">
    <p>ຍັງບໍ່ມີໄຟລ໌. ໜ້າດາວໂຫຼດຂອງເວັບຈະສະແດງ "ເອກະສານກຳລັງກະກຽມ" ພ້ອມປຸ່ມຕິດຕໍ່.</p>
    <p class="hint">ຕົວຢ່າງ: Company Profile (ບໍລິສັດ) ແລະ ແຄັດຕາລັອກຂອງແຕ່ລະຂະແໜງ.</p>
  </div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ຊື່</th><th scope="col">ຂອງ</th><th scope="col">ໄຟລ໌</th><th scope="col">ສະແດງ</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i => $r): $on = (int) $r['is_visible'] === 1 ?>
          <tr id="dl<?= e($r['id']) ?>"<?= $on ? '' : ' class="is-hidden"' ?>>
            <td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/downloads/' . $r['id'] . '/move', 'label' => $r['title_lo'], 'first' => $i === 0, 'last' => $i === $last]) ?></td>
            <td data-label="ຊື່"><a href="/admin/downloads/<?= e($r['id']) ?>"><b><?= e($r['title_lo']) ?></b></a><br><small><?= e($r['title_en']) ?></small></td>
            <td data-label="ຂອງ"><?= e($r['division_name'] ?? 'ບໍລິສັດ') ?></td>
            <td data-label="ໄຟລ໌"><a href="/<?= e($r['path']) ?>" target="_blank" rel="noopener"><?= e($r['original_name']) ?></a><br><small><?= e($size((int) $r['size_bytes'])) ?></small></td>
            <td data-label="ສະແດງ">
              <form method="post" action="/admin/downloads/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?>
                <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $r['title_lo']) ?>"><span></span></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="hint">ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
<?php endif; ?>
