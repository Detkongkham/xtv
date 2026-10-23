<?php
/** @var array $n @var array $errors @var ?array $cover @var array $images @var bool $published */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\MediaController as MC;
use App\Http\Admin\NewsController as NC;

$isNew = (int) $n['id'] === 0;
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
// pictures the editor can insert: the 960px size (or the largest smaller one), with its real size
$editorImages = array_map(static function (array $m): array {
    $v = json_decode((string) ($m['variants'] ?? 'null'), true) ?: [['w' => $m['width'], 'h' => $m['height'], 'src' => $m['path']]];
    $pick = array_values(array_filter($v, static fn ($x) => $x['w'] <= 960)) ?: $v;
    $best = end($pick);
    return ['thumb' => MC::thumb($m), 'src' => '../' . $best['src'], 'w' => (int) $best['w'], 'h' => (int) $best['h'], 'name' => $m['original_name']];
}, $images);
?>
<p class="crumb"><a href="/admin/news">← ຂ່າວສານ</a><?php if ($published): ?> · <a href="/lo/news-<?= e($n['slug']) ?>.html" target="_blank" rel="noopener">ເບິ່ງໜ້າເວັບ ↗</a><?php endif; ?></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2 grid2--wide" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/news/new' : '/admin/news/' . e($n['id']) ?>">
  <?= Csrf::field() ?>
  <div class="card stack">
    <?= View::render('admin/partials/lang-fields', ['fields' => NC::FIELDS, 'row' => $n, 'errors' => $errors]) ?>
    <div hidden data-rte-images="<?= e(json_encode($editorImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>"></div>
    <p class="hint">ເນື້ອຫາຖືກກວດ ແລະ ລ້າງກ່ອນບັນທຶກ: ຮັບສະເພາະ ຫົວຂໍ້ຍ່ອຍ, ຕົວໜາ, ຕົວອຽງ, ລາຍການ, ຄຳອ້າງອີງ, ລິ້ງ ແລະ ຮູບຈາກຄັງສື່.</p>
  </div>
  <div class="stack">
    <div class="card stack">
      <div class="field">
        <label for="status">ສະຖານະ</label>
        <select id="status" name="status">
          <?php foreach (NC::STATUS_NAMES as $k => $label): ?><option value="<?= e($k) ?>"<?= $n['status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <span class="hint">ສະບັບຮ່າງ ບໍ່ຂຶ້ນໜ້າເວັບ.</span>
      </div>
      <div class="field">
        <label for="published_on">ວັນທີ <em>*</em></label>
        <input id="published_on" name="published_on" type="date" required value="<?= e($n['published_on']) ?>"<?= $inv('published_on') ?>><?= $err('published_on') ?>
      </div>
      <div class="field">
        <label for="category">ໝວດ <em>*</em></label>
        <select id="category" name="category"<?= $inv('category') ?>>
          <?php foreach (NC::CATEGORY_NAMES as $k => $label): ?><option value="<?= e($k) ?>"<?= $n['category'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select><?= $err('category') ?>
      </div>
      <div class="field">
        <label for="slug">ຊື່ໜ້າເວັບ</label>
        <input id="slug" name="slug" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($n['slug']) ?>"<?= $inv('slug') ?>><?= $err('slug') ?>
        <span class="hint">news-<b><?= e($n['slug'] ?: '…') ?></b>.html. ປ່ອຍຫວ່າງ = ສ້າງຈາກຫົວຂໍ້ອັງກິດ.</span>
      </div>
      <div class="field">
        <label for="link_url">ລິ້ງປຸ່ມ "ເບິ່ງຜະລິດຕະພັນ"</label>
        <input id="link_url" name="link_url" maxlength="255" placeholder="security.html" value="<?= e($n['link_url'] ?? '') ?>"<?= $inv('link_url') ?>><?= $err('link_url') ?>
        <span class="hint">ຊື່ໜ້າໃນເວັບ ຫຼື ລິ້ງເຕັມ https://… ຫວ່າງ = ບໍ່ມີປຸ່ມ.</span>
      </div>
    </div>
    <div class="card"><?= View::render('admin/partials/media-picker', ['name' => 'cover_id', 'label' => 'ຮູບປົກ', 'current' => $cover, 'images' => $images, 'error' => $errors['cover_id'] ?? null]) ?></div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມຂ່າວ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/news">ຍົກເລີກ</a><?= $isNew ? '' : View::render('admin/partials/preview-link', ['page' => 'news-' . $n['slug']]) ?></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/news/<?= e($n['id']) ?>/delete" data-confirm="ລຶບຂ່າວ <?= e($n['title_lo']) ?>?">
    <?= Csrf::field() ?>
    <h2>ລຶບຂ່າວ</h2>
    <p>ຖ້າຢາກເອົາອອກຊົ່ວຄາວ ໃຫ້ປ່ຽນເປັນ "ສະບັບຮ່າງ" ແທນ.<?= $published ? ' ໜ້າເກົ່າຈະພາໄປໜ້າລວມຂ່າວ.' : '' ?></p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
