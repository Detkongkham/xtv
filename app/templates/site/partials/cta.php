<?php
/**
 * Dark call-to-action band at the end of most pages.
 * @var App\Publish\SiteView $v
 * @var ?string $h  heading, default cta_h
 */
?>
<section class="cta on-dark">
  <div class="wrap rv">
    <h2 class="h2"><?= $h ?? $v->t('cta_h') ?></h2>
    <p><?= $v->t('cta_p') ?></p>
    <div class="btns"><a class="btn btn--white" href="contact.html"><?= $v->t('contact') ?></a><a class="btn btn--light" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->ic('chat') ?><?= $v->t('whatsapp') ?></a></div>
  </div>
</section>
