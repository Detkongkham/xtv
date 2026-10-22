<?php
/** @var array $rows */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;
use App\Repository\HeroRepository;

$last = count($rows) - 1;
$full = count($rows) >= HeroRepository::MAX;
// a video shows its poster; the smallest size of the picture is enough for the list
$thumb = static function (array $r): ?string {
    if ($r['kind'] !== 'video') {
        return MC::thumb($r);
    }
    return $r['poster_path'] === null ? null : MC::thumb(['path' => $r['poster_path'], 'variants' => $r['poster_variants']]);
};
?>
<div class="bar">
  <p><b><?= e(count($rows)) ?></b> / <?= HeroRepository::MAX ?> slide</p>
  <?php if (!$full): ?><a class="btn btn--sm" href="/admin/hero/new">+ ເພີ່ມ slide</a><?php endif; ?>
</div>
<div class="card table-wrap">
  <table class="table sup-table">
    <thead><tr><th scope="col">ລຳດັບ</th><th scope="col">ຮູບ</th><th scope="col">ຫົວຂໍ້</th><th scope="col">ສະແດງ</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $i => $r): $on = (int) $r['is_visible'] === 1; $src = $thumb($r) ?>
        <tr id="hero<?= e($r['id']) ?>"<?= $on ? '' : ' class="is-hidden"' ?>>
          <td data-label="ລຳດັບ"><?= View::render('admin/partials/order-buttons', ['action' => '/admin/hero/' . $r['id'] . '/move', 'label' => $r['title_lo'], 'first' => $i === 0, 'last' => $i === $last]) ?></td>
          <td data-label="ຮູບ"><a href="/admin/hero/<?= e($r['id']) ?>"><?php if ($src !== null): ?><img class="hero-thumb" src="<?= e($src) ?>" alt="" loading="lazy"><?php else: ?><span class="hero-thumb hero-thumb--none">MP4</span><?php endif; ?></a>
            <?php if ($r['kind'] === 'video'): ?><small>ວິດີໂອ</small><?php endif; ?></td>
          <td data-label="ຫົວຂໍ້"><a href="/admin/hero/<?= e($r['id']) ?>"><b><?= e($r['title_lo']) ?></b></a><br><small><?= e($r['title_en']) ?></small></td>
          <td data-label="ສະແດງ">
            <form method="post" action="/admin/hero/<?= e($r['id']) ?>/toggle"><?= Csrf::field() ?>
              <button class="switch" type="submit" role="switch" aria-checked="<?= $on ? 'true' : 'false' ?>" aria-label="<?= e('ສະແດງໃນໜ້າເວັບ: ' . $r['title_lo']) ?>"><span></span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="hint">slide ທຳອິດສະແດງກ່ອນ ແລະ ຫົວຂໍ້ຂອງມັນເປັນ h1 ຂອງໜ້າຫຼັກ. ສູງສຸດ <?= HeroRepository::MAX ?> slide; ຕ້ອງສະແດງຢ່າງໜ້ອຍ 1. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
