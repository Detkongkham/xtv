<?php
/**
 * Choose one of the site's icons (templates/site/partials/icons.php), shown as they appear on the site.
 * @var string $name
 * @var string $current
 * @var ?string $error
 */
use App\Core\App;

$icons = require App::path('templates/site/partials/icons.php');
// UI-only icons are left out
$icons = array_diff_key($icons, array_flip(['arrow', 'chev', 'menu', 'x', 'globe', 'up', 'download', 'print', 'chat', 'fb']));
?>
<fieldset class="icon-pick">
  <legend>ໄອຄອນ <em>*</em></legend>
  <div class="icon-choices">
    <?php foreach ($icons as $key => $d): ?>
      <label class="icon-choice" title="<?= e($key) ?>"><input type="radio" name="<?= e($name) ?>" value="<?= e($key) ?>"<?= $current === $key ? ' checked' : '' ?> required><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= e($d) ?>"/></svg><span class="sr"><?= e($key) ?></span></label>
    <?php endforeach; ?>
  </div>
  <?php if (!empty($error)): ?><span class="field__err"><?= e($error) ?></span><?php endif; ?>
</fieldset>
