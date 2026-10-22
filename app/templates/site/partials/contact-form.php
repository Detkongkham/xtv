<?php
/**
 * The contact form (the only form on the site). Posts to /api/contact.php and works without JS: the server
 * redirects back with ?sent=1#sent. main.js takes over when present: time token, fetch, errors under each field.
 * @var App\Publish\SiteView $v
 */
$msgs = [];
foreach (['sending', 'err_required', 'err_invalid', 'err_too_short', 'err_too_long', 'err_consent', 'err_rate', 'err_server'] as $k) {
    // spaced like Python's json.dumps, which build.py used
    $msgs[] = json_encode($k) . ': ' . json_encode($v->t('f_' . $k), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
$err = static fn (string $name): string => '<span class="field__err" id="f-' . $name . '-err" hidden></span>';
// subject values are language-independent codes stored in the database
$fields = [
    ['f_fullname', 'name', 'text', true, false],
    ['f_org', 'org', 'text', false, false],
    ['f_phone', 'tel', 'tel', true, false],
    ['email', 'email', 'email', true, false],
    ['f_subject', 'subject', 'select', false, true],
    ['f_message', 'message', 'textarea', true, true],
];
$subjects = ['general' => $v->t('q_general'), 'sales' => $v->t('q_sales'), 'service' => $v->t('q_service'), 'other' => $v->t('f_other')];
$autocomplete = ['name' => 'name', 'tel' => 'tel', 'email' => 'email', 'org' => 'organization'];
[$tel, $wa] = [$v->contact('tel'), $v->whatsapp()];
?>
<form class="form" data-contact action="/api/contact.php" method="post" data-msgs="<?= $v->e('{' . implode(', ', $msgs) . '}') ?>">
  <input type="hidden" name="lang" value="<?= $v->lang ?>"><input type="hidden" name="ts" value="">
  <div class="hp" aria-hidden="true"><label for="f-website">Website</label><input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
  <?php foreach ($fields as [$label, $name, $kind, $req, $full]): ?>
  <div class="field<?= $full ? ' field--full' : '' ?>">
    <label for="f-<?= $name ?>"><?= $v->t($label) ?><?= $req ? ' <em>*</em>' : '' ?></label>
    <?php if ($kind === 'textarea'): ?>
    <textarea id="f-<?= $name ?>" name="<?= $name ?>"<?= $req ? ' required' : '' ?>></textarea>
    <?php elseif ($kind === 'select'): ?>
    <select id="f-<?= $name ?>" name="<?= $name ?>"<?= $req ? ' required' : '' ?>>
      <option value=""><?= $v->t('f_select') ?></option>
      <?php foreach ($subjects as $value => $text): ?>
      <option value="<?= $value ?>"><?= $text ?></option>
      <?php endforeach ?>
    </select>
    <?php else: ?>
    <input id="f-<?= $name ?>" name="<?= $name ?>" type="<?= $kind ?>" autocomplete="<?= $autocomplete[$name] ?? 'off' ?>"<?= $req ? ' required' : '' ?>>
    <?php endif ?>
    <?= $err($name) ?>
  </div>
  <?php endforeach ?>
  <div class="field field--full"><label class="check"><input type="checkbox" id="f-consent" name="consent" required><span><?= $v->t('f_consent') ?> (<a href="privacy.html"><?= $v->t('privacy') ?></a>)</span></label><?= $err('consent') ?></div>
  <p class="form__ok" id="sent" role="status" tabindex="-1" hidden><?= $v->t('f_ok') ?></p>
  <p class="form__err" role="alert" tabindex="-1" hidden><span></span> <a href="tel:<?= $v->telHref($tel) ?>"><?= $v->e($tel) ?></a> · <a href="<?= $wa ?>" target="_blank" rel="noopener">WhatsApp</a></p>
  <div class="field field--full"><div class="btns"><button class="btn" type="submit"><?= $v->t('f_send') ?></button></div><span class="hint"><?= $v->t('f_req') ?></span></div>
</form>
