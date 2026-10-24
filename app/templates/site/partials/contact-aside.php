<?php
/**
 * Office and hotline boxes beside the contact form.
 * @var App\Publish\SiteView $v
 */
[$tel, $mob, $fax, $mail] = [$v->contact('tel'), $v->contact('mobile'), $v->contact('fax'), $v->contact('email')];
?>
<div class="aside-box" style="background:#fff">
  <h3 class="h4"><?= $v->t('head_office') ?></h3>
  <ul class="contact-list">
    <li><?= $v->ic('pin') ?><span><?= $v->address() ?></span></li>
    <li><?= $v->ic('phone') ?><span><?= $v->t('tel') ?>: <a href="tel:<?= $v->telHref($tel) ?>"><?= $v->e($tel) ?></a><br><?= $v->t('mobile') ?>: <a href="tel:<?= $v->telHref($mob) ?>"><?= $v->e($mob) ?></a></span></li>
    <?php if ($fax !== ''): ?><li><?= $v->ic('print') ?><span><?= $v->t('fax') ?>: <?= $v->e($fax) ?></span></li><?php endif ?>
    <li><?= $v->ic('mail') ?><a href="mailto:<?= $v->e($mail) ?>"><?= $v->e($mail) ?></a></li>
  </ul>
</div>
<div class="aside-box" style="background:#fff">
  <h3 class="h4"><?= $v->t('hotline') ?></h3>
  <p><?= $v->t('mobile') ?>: <a href="tel:<?= $v->telHref($mob) ?>"><b><?= $v->e($mob) ?></b></a></p>
  <div class="btns" style="margin-top:16px"><a class="btn btn--sm" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->brand('whatsapp') ?><?= $v->t('whatsapp') ?></a></div>
</div>
