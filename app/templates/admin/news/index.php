<?php
/** @var array $rows @var string $status */
use App\Http\Admin\MediaController as MC;
use App\Http\Admin\NewsController as NC;
?>
<div class="bar">
  <p>
    <b><?= e(count($rows)) ?></b> ຂ່າວ ·
    <?php foreach (['' => 'ທັງໝົດ'] + NC::STATUS_NAMES as $k => $label): ?>
      <?php if ($status === $k): ?><b><?= e($label) ?></b><?php else: ?><a href="/admin/news<?= $k ? '?status=' . e($k) : '' ?>"><?= e($label) ?></a><?php endif; ?>
    <?php endforeach; ?>
  </p>
  <a class="btn btn--sm" href="/admin/news/new">+ ເພີ່ມຂ່າວ</a>
</div>
<?php if ($rows === []): ?>
  <div class="card empty"><p>ບໍ່ມີຂ່າວ.</p></div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><th scope="col">ຮູບປົກ</th><th scope="col">ຫົວຂໍ້</th><th scope="col">ໝວດ</th><th scope="col">ວັນທີ</th><th scope="col">ສະຖານະ</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr<?= $r['status'] === 'draft' ? ' class="is-hidden"' : '' ?>>
            <td data-label="ຮູບປົກ"><?php if ($r['cover_path']): ?><img class="thumb" src="<?= e(MC::thumb(['path' => $r['cover_path'], 'variants' => $r['cover_variants']])) ?>" alt=""><?php else: ?><small class="muted">ບໍ່ມີ</small><?php endif; ?></td>
            <td data-label="ຫົວຂໍ້">
              <a href="/admin/news/<?= e($r['id']) ?>"><b><?= e($r['title_lo']) ?></b></a><br><small><?= e($r['title_en']) ?></small>
              <?php if (!(int) $r['has_page']): ?><br><small class="muted">ບໍ່ມີໜ້າຂອງຕົນເອງ → <?= e($r['link_url']) ?></small><?php endif; ?>
            </td>
            <td data-label="ໝວດ"><?= e(NC::CATEGORY_NAMES[$r['category']] ?? $r['category']) ?></td>
            <td data-label="ວັນທີ"><?= e(date('d/m/Y', strtotime($r['published_on']))) ?></td>
            <td data-label="ສະຖານະ"><span class="tag tag--<?= $r['status'] === 'published' ? 'done' : 'spam' ?>"><?= e(NC::STATUS_NAMES[$r['status']]) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="hint">ຂ່າວລຽງຕາມວັນທີ, ໃໝ່ສຸດກ່ອນ. ສະບັບຮ່າງບໍ່ຂຶ້ນໜ້າເວັບ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
<?php endif; ?>
