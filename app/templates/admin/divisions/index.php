<?php
/** @var array $rows */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;

$last = count($rows) - 1;
?>
<div class="bar">
  <p><b><?= e(count($rows)) ?></b> ຂະແໜງ · <a href="/admin/groups">ກຸ່ມສິນຄ້າ →</a></p>
  <a class="btn btn--sm" href="/admin/divisions/new">+ ເພີ່ມຂະແໜງ</a>
</div>
<div class="card table-wrap">
  <table class="table sup-table">
    <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ຮູບ</th><th scope="col">ຊື່</th><th scope="col">ໜ້າເວັບ</th><th scope="col">ກຸ່ມ</th><th scope="col">ສິນຄ້າ</th><th scope="col">ສະແດງ</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $i => $r): $on = (int) $r['is_visible'] === 1 ?>
        <tr id="d<?= e($r['id']) ?>"<?= $on ? '' : ' class="is-hidden"' ?>>
          <td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/divisions/' . $r['id'] . '/move', 'label' => $r['name_lo'], 'first' => $i === 0, 'last' => $i === $last]) ?></td>
          <td data-label="ຮູບ"><?php if ($r['image_path']): ?><img class="thumb" src="<?= e(MC::thumb(['path' => $r['image_path'], 'variants' => $r['image_variants']])) ?>" alt=""><?php else: ?><small class="muted">ບໍ່ມີຮູບ</small><?php endif; ?></td>
          <td data-label="ຊື່"><a href="/admin/divisions/<?= e($r['id']) ?>"><b><?= e($r['name_lo']) ?></b></a><br><small><?= e($r['name_en']) ?></small></td>
          <td data-label="ໜ້າເວັບ"><code><?= e($r['slug']) ?>.html</code></td>
          <td data-label="ກຸ່ມ"><a href="/admin/groups#div<?= e($r['id']) ?>"><?= e($r['group_count']) ?></a></td>
          <td data-label="ສິນຄ້າ"><?= e($r['product_count']) ?></td>
          <td data-label="ສະແດງ">
            <form method="post" action="/admin/divisions/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?>
              <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $r['name_lo']) ?>"><span></span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="hint">ຂະແໜງທີ່ເຊື່ອງໄວ້ ບໍ່ມີໜ້າຂອງຕົນເອງ ແລະ ບໍ່ປາກົດໃນເມນູ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
