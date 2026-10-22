<?php
/** @var array $rows @var int $withoutSupplier */
use App\Core\Countries;
use App\Core\Csrf;
use App\Repository\SupplierRepository;

$featured = count(array_filter($rows, static fn ($r) => (int) $r['is_featured'] === 1));
$last = count($rows) - 1;
?>
<?php if ($withoutSupplier > 0): ?>
  <p class="flash flash--error" role="status">ມີ <?= e($withoutSupplier) ?> ສິນຄ້າທີ່ຍັງບໍ່ລະບຸຜູ້ສະໜອງ. ເປີດຜູ້ສະໜອງ ແລ້ວກົດ "ເພີ່ມສິນຄ້າ" ເພື່ອເຊື່ອມ.</p>
<?php endif; ?>
<div class="bar">
  <p><b><?= e(count($rows)) ?></b> ຜູ້ສະໜອງ · ຫຼັກ <?= e($featured) ?>/<?= SupplierRepository::MAX_FEATURED ?></p>
  <a class="btn btn--sm" href="/admin/suppliers/new">+ ເພີ່ມຜູ້ສະໜອງ</a>
</div>
<?php if ($rows === []): ?>
  <div class="card empty">
    <p>ຍັງບໍ່ມີຜູ້ສະໜອງ. ເມນູ "ຜູ້ສະໜອງ" ໃນໜ້າເວັບຈະປາກົດເມື່ອມີຜູ້ສະໜອງທີ່ສະແດງຢ່າງໜ້ອຍ 1 ລາຍ ແລະ ເຜີຍແຜ່ແລ້ວ.</p>
    <p class="hint">ໃຊ້ສະເພາະຊື່ ແລະ ໂລໂກ້ທີ່ລູກຄ້າຢືນຢັນ ແລະ ອະນຸຍາດໃຫ້ໃຊ້ແລ້ວ.</p>
  </div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ໂລໂກ້</th><th scope="col">ຊື່</th><th scope="col">ປະເທດ</th><th scope="col">ສິນຄ້າ</th><th scope="col">ຫຼັກ</th><th scope="col">ສະແດງ</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr id="s<?= e($r['id']) ?>"<?= (int) $r['is_visible'] ? '' : ' class="is-hidden"' ?>>
            <td data-label="ລຳດັບ">
              <div class="order">
                <?php foreach (['up' => ['↑', 'ຍ້າຍຂຶ້ນ', $i === 0], 'down' => ['↓', 'ຍ້າຍລົງ', $i === $last]] as $dir => [$arrow, $label, $off]): ?>
                  <form method="post" action="/admin/suppliers/<?= e($r['id']) ?>/move"><?= Csrf::field() ?><input type="hidden" name="dir" value="<?= $dir ?>"><button class="icon-btn" type="submit" aria-label="<?= e($label . ' ' . $r['name']) ?>"<?= $off ? ' disabled' : '' ?>><?= $arrow ?></button></form>
                <?php endforeach; ?>
              </div>
            </td>
            <td data-label="ໂລໂກ້">
              <span class="logo-box<?= (int) $r['logo_on_dark'] ? ' logo-box--dark' : '' ?>">
                <?php if ($r['logo_path']): ?><img src="/<?= e($r['logo_path']) ?>" alt="<?= e($r['name']) ?>"><?php else: ?><small class="muted">ບໍ່ມີໂລໂກ້</small><?php endif; ?>
              </span>
            </td>
            <td data-label="ຊື່"><a href="/admin/suppliers/<?= e($r['id']) ?>"><b><?= e($r['name']) ?></b></a></td>
            <td data-label="ປະເທດ"><?= e(Countries::name($r['country_code'], 'lo')) ?></td>
            <td data-label="ສິນຄ້າ">
              <?php if ((int) $r['product_count'] === 0): ?><a class="tag tag--error" href="/admin/suppliers/<?= e($r['id']) ?>#products">0 · ເພີ່ມສິນຄ້າ</a>
              <?php else: ?><a href="/admin/suppliers/<?= e($r['id']) ?>#products"><?= e($r['product_count']) ?></a><?php endif; ?>
            </td>
            <?php foreach (['is_featured' => 'ຜູ້ສະໜອງຫຼັກ', 'is_visible' => 'ສະແດງໃນໜ້າເວັບ'] as $field => $label): $on = (int) $r[$field] === 1 ?>
              <td data-label="<?= e($label) ?>">
                <form method="post" action="/admin/suppliers/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?><input type="hidden" name="field" value="<?= $field ?>">
                  <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e($label . ': ' . $r['name']) ?>"><span></span></button>
                </form>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="hint">ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
<?php endif; ?>
