<?php
/** @var array $rows */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;

$last = count($rows) - 1;
?>
<div class="bar">
  <p><b><?= e(count($rows)) ?></b> ຄົນ</p>
  <a class="btn btn--sm" href="/admin/leaders/new">+ ເພີ່ມຜູ້ບໍລິຫານ</a>
</div>
<?php if ($rows === []): ?>
  <div class="card empty">
    <p>ຍັງບໍ່ມີຜູ້ບໍລິຫານ. ໜ້າ "ກ່ຽວກັບເຮົາ" ຈະບໍ່ສະແດງພາກສ່ວນນີ້ຈົນກວ່າຈະເພີ່ມຢ່າງໜ້ອຍ 1 ຄົນ.</p>
  </div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ຮູບ</th><th scope="col">ຊື່ ແລະ ຕຳແໜ່ງ</th><th scope="col">ສະແດງ</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i => $r): $on = (int) $r['is_visible'] === 1 ?>
          <tr id="leader<?= e($r['id']) ?>"<?= $on ? '' : ' class="is-hidden"' ?>>
            <td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/leaders/' . $r['id'] . '/move', 'label' => $r['name_lo'], 'first' => $i === 0, 'last' => $i === $last]) ?></td>
            <td data-label="ຮູບ"><a href="/admin/leaders/<?= e($r['id']) ?>"><?php if ($r['path'] !== null): ?><img class="leader-thumb" src="<?= e(MC::thumb($r)) ?>" alt="" loading="lazy"><?php else: ?><span class="leader-thumb leader-thumb--none">ບໍ່ມີຮູບ</span><?php endif; ?></a></td>
            <td data-label="ຊື່ ແລະ ຕຳແໜ່ງ"><a href="/admin/leaders/<?= e($r['id']) ?>"><b><?= e($r['name_lo']) ?></b></a><br><small><?= e($r['position_lo']) ?></small></td>
            <td data-label="ສະແດງ">
              <form method="post" action="/admin/leaders/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?>
                <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $r['name_lo']) ?>"><span></span></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="hint">ສະແດງໃນໜ້າ "ກ່ຽວກັບເຮົາ" ຕາມລຳດັບນີ້, ຮູບສະຫຼັບຊ້າຍ-ຂວາ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
<?php endif; ?>
