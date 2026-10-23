<?php
/** @var array $slide @var array $errors @var string $kind @var ?array $image @var ?array $video @var ?array $poster @var array $images @var array $videos @var array $pages */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\HeroController as HC;
use App\Service\MediaService;

$isNew = (int) $slide['id'] === 0;
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
$videoId = (int) ($video['id'] ?? 0);
?>
<p class="crumb"><a href="/admin/hero">← Hero ໜ້າຫຼັກ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາ).</p>
<?php endif; ?>
<form class="grid2" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/hero/new' : '/admin/hero/' . e($slide['id']) ?>" data-busy="ກຳລັງບັນທຶກ…">
  <?= Csrf::field() ?>
  <div class="stack">
    <div class="card stack">
      <?= View::render('admin/partials/lang-fields', ['fields' => HC::FIELDS, 'row' => $slide, 'errors' => $errors]) ?>
    </div>
    <div class="card stack">
      <div class="field">
        <label for="button_page">ປຸ່ມພາໄປໜ້າ</label>
        <select id="button_page" name="button_page"<?= $inv('button_page') ?>>
          <option value="">— ບໍ່ມີປຸ່ມ —</option>
          <?php foreach ($pages as $group => $options): ?>
            <optgroup label="<?= e($group) ?>">
              <?php foreach ($options as $page => $label): ?><option value="<?= e($page) ?>"<?= ($slide['button_page'] ?? '') === (string) $page ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select><?= $err('button_page') ?>
        <span class="hint">ຖ້າໜ້ານັ້ນຖືກເຊື່ອງພາຍຫຼັງ, ປຸ່ມຈະພາໄປໜ້າສິນຄ້າທັງໝົດແທນ.</span>
      </div>
    </div>
  </div>
  <div class="stack">
    <div class="card stack">
      <fieldset class="stack">
        <legend>ພື້ນຫຼັງ <em class="req">*</em></legend>
        <label class="check"><input type="radio" name="kind" value="image"<?= $kind === 'image' ? ' checked' : '' ?>><span>ຮູບ</span></label>
        <label class="check"><input type="radio" name="kind" value="video"<?= $kind === 'video' ? ' checked' : '' ?>><span>ວິດີໂອ (ຫຼິ້ນເອງ, ບໍ່ມີສຽງ)</span></label>
      </fieldset>
      <div data-show-if="kind=image">
        <?= View::render('admin/partials/media-picker', ['name' => 'media_id', 'label' => 'ຮູບພື້ນຫຼັງ', 'current' => $image, 'images' => $images, 'optional' => false, 'error' => $errors['media_id'] ?? null]) ?>
        <p class="hint">ຮູບລວງນອນ ກວ້າງຢ່າງໜ້ອຍ 1600 px. ຂໍ້ຄວາມສີຂາວຢູ່ທາງຊ້າຍ, ຮູບມືດຈະອ່ານງ່າຍກວ່າ.</p>
      </div>
      <div class="stack" data-show-if="kind=video">
        <fieldset class="stack">
          <legend>ວິດີໂອ</legend>
          <?php if ($video): ?><p><video class="media-preview" src="/<?= e($video['path']) ?>" muted controls preload="metadata"></video></p><?php endif; ?>
          <?php if ($videos !== []): ?>
            <div class="field">
              <label for="video_id">ເລືອກຈາກຄັງສື່</label>
              <select id="video_id" name="video_id"<?= $inv('video_id') ?>>
                <option value="">— ເລືອກ —</option>
                <?php foreach ($videos as $vd): ?><option value="<?= e($vd['id']) ?>"<?= (int) $vd['id'] === $videoId ? ' selected' : '' ?>><?= e($vd['original_name'] . ' (' . number_format($vd['size_bytes'] / 1048576, 1) . ' MB)') ?></option><?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <div class="field">
            <label for="video_upload">ຫຼື ອັບວິດີໂອໃໝ່</label>
            <input id="video_upload" name="video_upload" type="file" accept="video/mp4"<?= $inv('video_id') ?>>
            <span class="hint">MP4 ເທົ່ານັ້ນ, ສູງສຸດ <?= MediaService::VIDEO_MAX_BYTES >> 20 ?> MB. ແນະນຳ 10–20 ວິນາທີ, 1280–1920 px, ບໍ່ມີສຽງ, ບໍ່ເກີນ 5 MB ເພື່ອໃຫ້ໂຫຼດໄວໃນມືຖື.</span>
          </div>
          <?= $err('video_id') ?>
        </fieldset>
        <?= View::render('admin/partials/media-picker', ['name' => 'poster_id', 'label' => 'ຮູບ poster (ສະແດງກ່ອນວິດີໂອໂຫຼດ)', 'current' => $poster, 'images' => $images, 'optional' => false, 'error' => $errors['poster_id'] ?? null]) ?>
      </div>
    </div>
    <div class="card stack">
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $slide['is_visible'] ? ' checked' : '' ?><?= $inv('is_visible') ?>><span>ສະແດງໃນໜ້າເວັບ</span></label><?= $err('is_visible') ?>
    </div>
    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/hero">ຍົກເລີກ</a><?= $isNew ? '' : View::render('admin/partials/preview-link', ['page' => 'index']) ?></div>
  </div>
</form>
<?php if (!$isNew): ?>
  <form class="card danger" method="post" action="/admin/hero/<?= e($slide['id']) ?>/delete" data-confirm="ລຶບ slide <?= e($slide['title_lo']) ?>?">
    <?= Csrf::field() ?>
    <h2>ລຶບ slide</h2>
    <p>ຮູບ ແລະ ວິດີໂອຍັງຢູ່ໃນຄັງສື່. ຖ້າຢາກເຊື່ອງໄວ້ຊົ່ວຄາວ, ໃຫ້ເອົາເຄື່ອງໝາຍ "ສະແດງໃນໜ້າເວັບ" ອອກແທນ.</p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
