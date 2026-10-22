<?php
/** @var array $rows */
use App\Core\Csrf;
use App\Core\View;

$last = count($rows) - 1;
?>
<div class="bar">
  <p><b><?= e(count($rows)) ?></b> ຄຳຖາມ</p>
  <a class="btn btn--sm" href="/admin/faqs/new">+ ເພີ່ມຄຳຖາມ</a>
</div>
<?php if ($rows === []): ?>
  <div class="card empty">
    <p>ຍັງບໍ່ມີຄຳຖາມ. ໜ້າ FAQ ຂອງເວັບຈະສະແດງພຽງປຸ່ມຕິດຕໍ່.</p>
  </div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ຄຳຖາມ</th><th scope="col">ສະແດງ</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i => $r): $on = (int) $r['is_visible'] === 1 ?>
          <tr id="faq<?= e($r['id']) ?>"<?= $on ? '' : ' class="is-hidden"' ?>>
            <td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/faqs/' . $r['id'] . '/move', 'label' => $r['question_lo'], 'first' => $i === 0, 'last' => $i === $last]) ?></td>
            <td data-label="ຄຳຖາມ"><a href="/admin/faqs/<?= e($r['id']) ?>"><b><?= e($r['question_lo']) ?></b></a><br><small><?= e($r['question_en']) ?></small></td>
            <td data-label="ສະແດງ">
              <form method="post" action="/admin/faqs/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?>
                <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $r['question_lo']) ?>"><span></span></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="hint">ຄຳຖາມທຳອິດເປີດໄວ້ໃນໜ້າເວັບ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
<?php endif; ?>
