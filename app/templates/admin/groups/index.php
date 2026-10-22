<?php
/** @var array $byDivision division id => groups @var array $divisions id => name */
use App\Core\Csrf;
use App\Core\View;

$icons = require App\Core\App::path('templates/site/partials/icons.php');
?>
<div class="bar">
  <p><a href="/admin/divisions">← ຂະແໜງທຸລະກິດ</a></p>
  <a class="btn btn--sm" href="/admin/groups/new">+ ເພີ່ມກຸ່ມ</a>
</div>
<?php foreach ($divisions as $divId => $divName): $groups = $byDivision[$divId] ?? []; $last = count($groups) - 1 ?>
  <section class="card table-wrap" id="div<?= e($divId) ?>">
    <div class="card__head" style="padding:20px 24px 0"><h2><?= e($divName) ?></h2><a class="btn btn--ghost btn--sm" href="/admin/groups/new?division=<?= e($divId) ?>">+ ເພີ່ມກຸ່ມໃນຂະແໜງນີ້</a></div>
    <?php if ($groups === []): ?>
      <p class="empty">ຍັງບໍ່ມີກຸ່ມ.</p>
    <?php else: ?>
      <table class="table sup-table">
        <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ໄອຄອນ</th><th scope="col">ຊື່</th><th scope="col">Anchor</th><th scope="col">ສິນຄ້າ</th><th scope="col">ສະແດງ</th></tr></thead>
        <tbody>
          <?php foreach ($groups as $i => $g): $on = (int) $g['is_visible'] === 1 ?>
            <tr id="g<?= e($g['id']) ?>"<?= $on ? '' : ' class="is-hidden"' ?>>
              <td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/groups/' . $g['id'] . '/move', 'label' => $g['name_lo'], 'first' => $i === 0, 'last' => $i === $last]) ?></td>
              <td data-label="ໄອຄອນ"><svg class="icon-cell" viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($icons[$g['icon']] ?? '') ?>"/></svg></td>
              <td data-label="ຊື່"><a href="/admin/groups/<?= e($g['id']) ?>"><b><?= e($g['name_lo']) ?></b></a><br><small><?= e($g['name_en']) ?></small></td>
              <td data-label="Anchor"><code>#<?= e($g['code']) ?></code></td>
              <td data-label="ສິນຄ້າ"><?= e($g['product_count']) ?></td>
              <td data-label="ສະແດງ">
                <form method="post" action="/admin/groups/<?= e($g['id']) ?>/toggle"><?= Csrf::field() ?>
                  <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $g['name_lo']) ?>"><span></span></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
<?php endforeach; ?>
<p class="hint">ສິນຄ້າໃນກຸ່ມທີ່ເຊື່ອງໄວ້ ຈະບໍ່ປາກົດໃນໜ້າເວັບ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
