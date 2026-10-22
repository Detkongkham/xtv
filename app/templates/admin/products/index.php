<?php
/** @var array $rows @var int $total @var int $page @var int $pages @var array $filters @var array $groups @var array $divisions @var int $featured */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;
use App\Http\Admin\ProductController as PC;
use App\Repository\ProductRepository;

$query = array_filter($filters, static fn ($v) => $v !== '' && $v !== 0);
$link = static fn (array $extra): string => '/admin/products?' . http_build_query(array_merge($query, $extra));
$here = $link($page > 1 ? ['page' => $page] : []);
$canOrder = $filters['group'] > 0 && $filters['q'] === '' && $filters['show'] === '';
$last = count($rows) - 1;
?>
<form class="card filters filters--products" method="get" action="/admin/products" role="search">
  <div class="field">
    <label for="q">ຄົ້ນຫາ</label>
    <input id="q" name="q" type="search" value="<?= e($filters['q']) ?>" placeholder="ຊື່ສິນຄ້າ">
  </div>
  <div class="field">
    <label for="division">ຂະແໜງ</label>
    <select id="division" name="division">
      <option value="">ທັງໝົດ</option>
      <?php foreach ($divisions as $id => $name): ?><option value="<?= e($id) ?>"<?= $filters['division'] === (int) $id ? ' selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="group">ກຸ່ມ</label>
    <select id="group" name="group">
      <option value="">ທັງໝົດ</option>
      <?php foreach ($groups as $id => $name): ?><option value="<?= e($id) ?>"<?= $filters['group'] === (int) $id ? ' selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="show">ສະແດງ</label>
    <select id="show" name="show">
      <?php foreach (PC::SHOW_NAMES as $k => $label): ?><option value="<?= e($k) ?>"<?= $filters['show'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="filters__btns">
    <button class="btn" type="submit">ກອງ</button>
    <?php if ($query !== []): ?><a class="btn btn--ghost" href="/admin/products">ລ້າງ</a><?php endif; ?>
  </div>
</form>

<div class="bar">
  <p><b><?= e($total) ?></b> ສິນຄ້າ · ແນະນຳ <?= e($featured) ?>/<?= ProductRepository::MAX_FEATURED ?><?= $canOrder ? '' : ' · <span class="muted">ເລືອກກຸ່ມ ເພື່ອລຽງລຳດັບ</span>' ?></p>
  <a class="btn btn--sm" href="/admin/products/new<?= $filters['group'] ? '?group=' . e($filters['group']) : '' ?>">+ ເພີ່ມສິນຄ້າ</a>
</div>

<?php if ($rows === []): ?>
  <div class="card empty"><p>ບໍ່ພົບສິນຄ້າຕາມເງື່ອນໄຂນີ້.</p></div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><?= $canOrder ? '<th scope="col">ລຳດັບ</th>' : '' ?><th scope="col">ຮູບ</th><th scope="col">ຊື່</th><th scope="col">ກຸ່ມ</th><th scope="col">ຜູ້ສະໜອງ</th><th scope="col">ແນະນຳ</th><th scope="col">ສະແດງ</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr id="p<?= e($r['id']) ?>"<?= (int) $r['is_visible'] ? '' : ' class="is-hidden"' ?>>
            <?php if ($canOrder): ?><td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/products/' . $r['id'] . '/move', 'label' => $r['name'], 'first' => $i === 0, 'last' => $i === $last]) ?></td><?php endif; ?>
            <td data-label="ຮູບ"><?php if ($r['image_path']): ?><img class="thumb" src="<?= e(MC::thumb(['path' => $r['image_path'], 'variants' => $r['image_variants']])) ?>" alt=""><?php else: ?><small class="muted">ສອບຖາມລາຄາ</small><?php endif; ?></td>
            <td data-label="ຊື່"><a href="/admin/products/<?= e($r['id']) ?>"><b><?= e($r['name']) ?></b></a><?= (int) $r['is_visible'] ? '' : ' <span class="tag tag--spam">ເຊື່ອງ</span>' ?></td>
            <td data-label="ກຸ່ມ"><small><?= e($r['division_name']) ?> ›</small><br><?= e($r['group_name']) ?></td>
            <td data-label="ຜູ້ສະໜອງ"><?= $r['supplier_names'] ? e($r['supplier_names']) : '<span class="tag tag--error">ບໍ່ມີ</span>' ?></td>
            <?php foreach (['is_featured' => 'ສິນຄ້າແນະນຳ', 'is_visible' => 'ສະແດງໃນໜ້າເວັບ'] as $field => $label): $on = (int) $r[$field] === 1 ?>
              <td data-label="<?= e($label) ?>">
                <form method="post" action="/admin/products/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?><input type="hidden" name="field" value="<?= $field ?>"><input type="hidden" name="back" value="<?= e($here) ?>">
                  <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e($label . ': ' . $r['name']) ?>"<?= $field === 'is_featured' && !$r['image_path'] ? ' disabled title="ຕ້ອງມີຮູບ"' : '' ?>><span></span></button>
                </form>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="ໜ້າ">
      <?php if ($page > 1): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['page' => $page - 1])) ?>">← ກ່ອນໜ້າ</a><?php endif; ?>
      <span>ໜ້າ <?= e($page) ?> / <?= e($pages) ?></span>
      <?php if ($page < $pages): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['page' => $page + 1])) ?>">ຕໍ່ໄປ →</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
