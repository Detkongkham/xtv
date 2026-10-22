<?php
/**
 * Choose a picture from the library or upload a new one, in the same form (the form needs enctype multipart).
 * Read back with Controller::pickedMedia($name, $current). Works without JS: the radios are plain inputs.
 * @var string $name         field name, e.g. image_id; the upload field is <name>_upload
 * @var string $label
 * @var ?array $current      media row or null
 * @var array $images        MediaRepository::images()
 * @var bool $optional       offer "no picture"
 * @var ?string $error
 */
use App\Http\Admin\MediaController as MC;

$optional ??= true;
$error ??= null;
$currentId = $current ? (int) $current['id'] : 0;
?>
<fieldset class="stack media-pick">
  <legend><?= e($label) ?></legend>
  <div class="media-pick__now">
    <?php if ($current): ?><img src="<?= e(MC::thumb($current)) ?>" alt="" width="160"><span><?= e($current['original_name']) ?></span>
    <?php else: ?><span class="muted">ບໍ່ມີຮູບ</span><?php endif; ?>
  </div>
  <details<?= $error ? ' open' : '' ?>>
    <summary>ເລືອກຈາກຄັງສື່ (<?= e(count($images)) ?>)</summary>
    <div class="media-choices">
      <?php if ($optional): ?>
        <label class="media-choice media-choice--none"><input type="radio" name="<?= e($name) ?>" value="0"<?= $currentId === 0 ? ' checked' : '' ?>><span>ບໍ່ມີຮູບ</span></label>
      <?php endif; ?>
      <?php foreach ($images as $img): ?>
        <label class="media-choice"><input type="radio" name="<?= e($name) ?>" value="<?= e($img['id']) ?>"<?= (int) $img['id'] === $currentId ? ' checked' : '' ?>><img src="<?= e(MC::thumb($img)) ?>" alt="" loading="lazy"><span><?= e($img['original_name']) ?></span></label>
      <?php endforeach; ?>
    </div>
  </details>
  <div class="field">
    <label for="<?= e($name) ?>_upload">ຫຼື ອັບຮູບໃໝ່</label>
    <input id="<?= e($name) ?>_upload" name="<?= e($name) ?>_upload" type="file" accept="image/jpeg,image/png,image/webp"<?= $error ? ' aria-invalid="true" aria-describedby="' . e($name) . '-err"' : '' ?>>
    <?php if ($error): ?><span class="field__err" id="<?= e($name) ?>-err"><?= e($error) ?></span><?php endif; ?>
  </div>
</fieldset>
