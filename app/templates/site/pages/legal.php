<?php
/**
 * Privacy policy and terms of use (privacy.html, terms.html).
 * @var App\Publish\SiteView $v
 * @var string $slug
 * @var array $keys  section keys: <key>_h heading, <key>_p text
 */
$v->meta($slug, $v->t($slug));
$mail = $v->e($v->contact('email'));
?>
<?= $v->partial('page-head', ['title' => $v->t($slug)]) ?>
<section class="sec sec--tight"><div class="wrap"><div class="prose" style="margin:0 auto">
  <p class="small"><?= $v->t('legal_updated') ?></p>
  <?php foreach ($keys as $k): ?>
  <h2><?= $v->t($k . '_h') ?></h2><p><?= $v->t($k . '_p') ?></p>
  <?php endforeach ?>
  <h2><?= $v->t('contact') ?></h2>
  <p><?= $v->t('co_name') ?><br><a href="mailto:<?= $mail ?>"><?= $mail ?></a> · <?= $v->e($v->contact('tel')) ?></p>
</div></div></section>
