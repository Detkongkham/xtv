<?php
/** @var array $m @var array $usage */
use App\Core\Csrf;
use App\Http\Admin\MediaController as MC;
use App\Http\Admin\SupplierController as SC;
use App\Repository\MediaRepository;

$variants = json_decode((string) ($m['variants'] ?? 'null'), true) ?: [];
$size = static fn (int $b): string => $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
?>
<p class="crumb"><a href="/admin/media">← ຄັງສື່</a></p>
<div class="grid2">
  <div class="card">
    <?php if ($m['kind'] === 'image'): ?>
      <img class="media-preview" src="/<?= e($m['path']) ?>" alt="" width="<?= e($m['width']) ?>" height="<?= e($m['height']) ?>">
    <?php else: ?>
      <p><a class="btn btn--ghost" href="/<?= e($m['path']) ?>" target="_blank" rel="noopener">ເປີດ PDF ↗</a></p>
    <?php endif; ?>
    <dl class="dl" style="margin-top:20px">
      <dt>ຊື່ໄຟລ໌</dt><dd class="break"><?= e($m['original_name']) ?></dd>
      <dt>ປະເພດ</dt><dd><?= e(MC::KIND_NAMES[$m['kind']] ?? $m['kind']) ?> · <?= e($size((int) $m['size_bytes'])) ?></dd>
      <?php if ($m['width']): ?><dt>ຂະໜາດ</dt><dd><?= e($m['width'] . ' × ' . $m['height']) ?> px</dd><?php endif; ?>
      <?php if ($variants): ?><dt>ສະບັບ</dt><dd><?= e(implode(', ', array_map(static fn ($v) => $v['w'] . ' px', $variants))) ?> (WebP + <?= str_ends_with($m['path'], '.png') ? 'PNG' : 'JPEG' ?>)</dd><?php endif; ?>
      <dt>ທີ່ຢູ່</dt><dd class="break"><code>/<?= e($m['path']) ?></code></dd>
      <dt>ອັບເມື່ອ</dt><dd><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></dd>
    </dl>
  </div>
  <div class="stack">
    <section class="card">
      <h2>ໃຊ້ຢູ່ໃສ</h2>
      <?php if ($usage === []): ?>
        <p class="empty">ບໍ່ໄດ້ໃຊ້ຢູ່ບ່ອນໃດ.</p>
      <?php else: ?>
        <ul class="list">
          <?php foreach ($usage as $u): ?>
            <li><a href="<?= e($u['href']) ?>"><b><?= e($u['label']) ?></b><small><?= e($u['what']) ?></small></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
    <?php if ($m['kind'] === 'image'): ?>
      <form class="card stack" method="post" action="/admin/media/<?= e($m['id']) ?>">
        <?= Csrf::field() ?>
        <h2>ຄຳອະທິບາຍຮູບ (alt)</h2>
        <p class="hint">ສຳລັບຄົນທີ່ໃຊ້ໂປຣແກຣມອ່ານໜ້າຈໍ ແລະ Google. ຮູບສິນຄ້າໃຊ້ຊື່ສິນຄ້າແທນເອງ.</p>
        <?php foreach (MediaRepository::LANGS as $l): ?>
          <div class="field">
            <label for="alt_<?= $l ?>"><?= e(SC::LANG_NAMES[$l]) ?></label>
            <input id="alt_<?= $l ?>" name="alt_<?= $l ?>" maxlength="255" lang="<?= $l ?>" value="<?= e($m['alt_' . $l] ?? '') ?>">
          </div>
        <?php endforeach; ?>
        <div class="btns"><button class="btn" type="submit">ບັນທຶກ</button></div>
      </form>
    <?php endif; ?>
    <form class="card danger" method="post" action="/admin/media/<?= e($m['id']) ?>/delete" data-confirm="ລຶບ <?= e($m['original_name']) ?>?">
      <?= Csrf::field() ?>
      <h2>ລຶບໄຟລ໌</h2>
      <?php if ($usage !== []): ?>
        <p>ລຶບບໍ່ໄດ້ ເພາະຍັງຖືກໃຊ້ຢູ່ <?= e(count($usage)) ?> ບ່ອນ.</p>
      <?php else: ?>
        <p>ໄຟລ໌ທຸກຂະໜາດຈະຖືກລຶບອອກຈາກ server.</p>
        <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
      <?php endif; ?>
    </form>
  </div>
</div>
