<?php
/**
 * Translated fields in 4 language panels. admin.js turns the panels into tabs; without JS they are stacked.
 * Lao and English are required; Thai and Chinese may stay empty (the site then shows English).
 * @var array $fields  list of [column prefix, label, 'input'|'textarea', max length, hint (optional)]
 * @var array $row     current values: <prefix>_<lang>
 * @var array $errors  <prefix>_<lang> => message
 */
$langs = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
$required = ['lo', 'en'];
?>
<div class="lang-tabs" data-lang-tabs>
  <?php foreach ($langs as $l => $langName): ?>
    <?php
    $missing = !in_array($l, $required, true) && array_filter($fields, static fn ($f) => trim((string) ($row[$f[0] . '_' . $l] ?? '')) === '') !== [];
    $hasError = array_filter($fields, static fn ($f) => isset($errors[$f[0] . '_' . $l])) !== [];
    ?>
    <section class="lang-panel" data-label="<?= e($langName) ?>" data-state="<?= $hasError ? 'error' : ($missing ? 'missing' : '') ?>">
      <h3 class="lang-panel__title"><?= e($langName) ?><?= in_array($l, $required, true) ? ' <em>*</em>' : ' <small>(ຫວ່າງ = ໃຊ້ພາສາອັງກິດ)</small>' ?></h3>
      <?php foreach ($fields as $f): [$prefix, $label, $type, $max] = $f; $key = $prefix . '_' . $l; $req = in_array($l, $required, true) ?>
        <div class="field">
          <label for="<?= e($key) ?>"><?= e($label) ?><?= $req ? ' <em>*</em>' : '' ?></label>
          <?php if ($type === 'textarea'): ?>
            <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= $max > 1000 ? 6 : 3 ?>" maxlength="<?= e($max) ?>" lang="<?= $l ?>"<?= $req ? ' required' : '' ?><?= isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . e($key) . '-err"' : '' ?>><?= e($row[$key] ?? '') ?></textarea>
          <?php else: ?>
            <input id="<?= e($key) ?>" name="<?= e($key) ?>" maxlength="<?= e($max) ?>" lang="<?= $l ?>" value="<?= e($row[$key] ?? '') ?>"<?= $req ? ' required' : '' ?><?= isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . e($key) . '-err"' : '' ?>>
          <?php endif; ?>
          <?php if (isset($errors[$key])): ?><span class="field__err" id="<?= e($key) ?>-err"><?= e($errors[$key]) ?></span><?php endif; ?>
          <?php if (!empty($f[4]) && $l === 'lo'): ?><span class="hint"><?= e($f[4]) ?></span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
</div>
