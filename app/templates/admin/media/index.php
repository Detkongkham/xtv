<?php
/** @var array $rows @var int $total @var int $page @var int $pages @var array $filters */
use App\Core\Csrf;
use App\Http\Admin\MediaController as MC;
use App\Service\MediaService;

$link = static fn (array $extra): string => '/admin/media?' . http_build_query(array_filter(array_merge($filters, $extra), static fn ($v) => $v !== '' && $v !== 1));
$size = static fn (int $b): string => $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
?>
<form class="card stack" method="post" action="/admin/media" enctype="multipart/form-data" data-busy="ກຳລັງອັບ…">
  <?= Csrf::field() ?>
  <div class="field">
    <label for="files">ອັບໄຟລ໌ໃໝ່</label>
    <input id="files" name="files[]" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf">
    <span class="hint">ຮູບ JPG, PNG, WebP (ສູງສຸດ <?= MediaService::IMAGE_MAX_BYTES >> 20 ?> MB) ຫຼື PDF (ສູງສຸດ <?= MediaService::PDF_MAX_BYTES >> 20 ?> MB). ເລືອກຫຼາຍໄຟລ໌ພ້ອມກັນໄດ້. ລະບົບສ້າງຮູບ 3 ຂະໜາດໃຫ້ເອງ.</span>
  </div>
  <div class="btns"><button class="btn" type="submit">ອັບ</button></div>
</form>

<form class="card filters filters--2" method="get" action="/admin/media" role="search">
  <div class="field">
    <label for="q">ຄົ້ນຫາ</label>
    <input id="q" name="q" type="search" value="<?= e($filters['q']) ?>" placeholder="ຊື່ໄຟລ໌ ຫຼື ຄຳອະທິບາຍຮູບ">
  </div>
  <div class="field">
    <label for="kind">ປະເພດ</label>
    <select id="kind" name="kind">
      <?php foreach (MC::KIND_NAMES as $k => $label): ?>
        <option value="<?= e($k) ?>"<?= $filters['kind'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filters__btns"><button class="btn" type="submit">ກອງ</button></div>
</form>

<div class="bar"><p><b><?= e($total) ?></b> ໄຟລ໌</p></div>
<?php if ($rows === []): ?>
  <div class="card empty"><p>ບໍ່ພົບໄຟລ໌.</p></div>
<?php else: ?>
  <ul class="media-grid">
    <?php foreach ($rows as $m): ?>
      <li>
        <a href="/admin/media/<?= e($m['id']) ?>">
          <span class="media-grid__img">
            <?php if ($m['kind'] === 'image'): ?><img src="<?= e(MC::thumb($m)) ?>" alt="" loading="lazy"><?php else: ?><b class="media-grid__pdf"><?= $m['kind'] === 'video' ? 'MP4' : 'PDF' ?></b><?php endif; ?>
          </span>
          <span class="media-grid__name"><?= e($m['original_name']) ?></span>
          <small><?= $m['width'] ? e($m['width'] . '×' . $m['height']) . ' · ' : '' ?><?= e($size((int) $m['size_bytes'])) ?></small>
          <?php if ($m['used'] === 0): ?><span class="tag tag--spam">ບໍ່ໄດ້ໃຊ້</span><?php else: ?><span class="tag">ໃຊ້ <?= e($m['used']) ?> ບ່ອນ</span><?php endif; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="ໜ້າ">
      <?php if ($page > 1): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['page' => $page - 1])) ?>">← ກ່ອນໜ້າ</a><?php endif; ?>
      <span>ໜ້າ <?= e($page) ?> / <?= e($pages) ?></span>
      <?php if ($page < $pages): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['page' => $page + 1])) ?>">ຕໍ່ໄປ →</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
