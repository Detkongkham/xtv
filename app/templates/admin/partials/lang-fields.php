<?php
/**
 * Translated fields in 4 language panels. admin.js turns the panels into tabs; without JS they are stacked.
 * Lao and English are required; Thai and Chinese may stay empty (the site then shows English).
 * @var array $fields  list of [column prefix, label, 'input'|'textarea'|'html', max length, hint (optional), optional field (bool)]
 *                     'html' is a textarea that admin.js turns into the simple editor (data-editor)
 * @var array $row     current values: <prefix>_<lang>
 * @var array $errors  <prefix>_<lang> => message
 * @var array $placeholders  (optional) <prefix>_<lang> => greyed text shown while the field is empty
 * The Lao panel has the "ແປເປັນ 3 ພາສາ" button (partials/translate-button.php, only with a Gemini key).
 */
use App\Core\View;

$langs = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
$required = ['lo', 'en'];
$placeholders ??= [];
$ph = static fn (string $key): string => isset($placeholders[$key]) ? ' placeholder="' . e($placeholders[$key]) . '"' : '';
?>
<div class="lang-tabs" data-lang-tabs data-translate-box>
  <?php foreach ($langs as $l => $langName): ?>
    <?php
    $missing = !in_array($l, $required, true) && array_filter($fields, static fn ($f) => trim((string) ($row[$f[0] . '_' . $l] ?? '')) === '' && trim((string) ($row[$f[0] . '_en'] ?? '')) !== '') !== [];
    $hasError = array_filter($fields, static fn ($f) => isset($errors[$f[0] . '_' . $l])) !== [];
    ?>
    <section class="lang-panel" data-label="<?= e($langName) ?>" data-state="<?= $hasError ? 'error' : ($missing ? 'missing' : '') ?>">
      <h3 class="lang-panel__title"><?= e($langName) ?><?= in_array($l, $required, true) ? ' <em>*</em>' : ' <small>(ຫວ່າງ = ໃຊ້ພາສາອັງກິດ)</small>' ?></h3>
      <?php if ($l === 'lo'): ?><?= View::render('admin/partials/translate-button') ?><?php endif; ?>
      <?php foreach ($fields as $f): [$prefix, $label, $type, $max] = $f; $key = $prefix . '_' . $l; $req = in_array($l, $required, true) && empty($f[5]) ?>
        <div class="field">
          <label for="<?= e($key) ?>"><?= e($label) ?><?= $req ? ' <em>*</em>' : '' ?></label>
          <?php if ($type === 'html'): ?>
            <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="12" lang="<?= $l ?>" data-editor<?= isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . e($key) . '-err"' : '' ?>><?= e($row[$key] ?? '') ?></textarea>
          <?php elseif ($type === 'textarea'): ?>
            <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= $max > 1000 ? 6 : 3 ?>" maxlength="<?= e($max) ?>" lang="<?= $l ?>"<?= $ph($key) ?><?= $req ? ' required' : '' ?><?= isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . e($key) . '-err"' : '' ?>><?= e($row[$key] ?? '') ?></textarea>
          <?php else: ?>
            <input id="<?= e($key) ?>" name="<?= e($key) ?>" maxlength="<?= e($max) ?>" lang="<?= $l ?>"<?= $ph($key) ?> value="<?= e($row[$key] ?? '') ?>"<?= $req ? ' required' : '' ?><?= isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . e($key) . '-err"' : '' ?>>
          <?php endif; ?>
          <?php if (isset($errors[$key])): ?><span class="field__err" id="<?= e($key) ?>-err"><?= e($errors[$key]) ?></span><?php endif; ?>
          <?php if (!empty($f[4]) && $l === 'lo'): ?><span class="hint"><?= e($f[4]) ?></span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
</div>
