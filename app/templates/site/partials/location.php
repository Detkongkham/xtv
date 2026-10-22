<?php
/**
 * Head office address, phones and map.
 * @var App\Publish\SiteView $v
 */
[$tel, $mob, $fax, $mail] = [$v->contact('tel'), $v->contact('mobile'), $v->contact('fax'), $v->contact('email')];
?>
<div class="loc rv">
  <div class="loc__list on-dark">
    <div>
      <span class="tag"><?= $v->t('head_office') ?></span>
      <h3 class="h4"><?= $v->t('co_name') ?></h3>
      <dl>
        <div><?= $v->ic('pin') ?><dd><?= $v->address() ?></dd></div>
        <div><?= $v->ic('phone') ?><dd><?= $v->t('tel') ?>: <a href="tel:<?= $v->telHref($tel) ?>"><?= $v->e($tel) ?></a><br><?= $v->t('mobile') ?>: <a href="tel:<?= $v->telHref($mob) ?>"><?= $v->e($mob) ?></a><?php if ($fax !== ''): ?><br><?= $v->t('fax') ?>: <?= $v->e($fax) ?><?php endif ?></dd></div>
        <div><?= $v->ic('mail') ?><dd><a href="mailto:<?= $v->e($mail) ?>"><?= $v->e($mail) ?></a></dd></div>
      </dl>
    </div>
    <div class="btns"><a class="btn btn--white btn--sm" href="<?= $v->mapLink() ?>" target="_blank" rel="noopener"><?= $v->t('open_map') ?></a><a class="btn btn--light btn--sm" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->ic('chat') ?>WhatsApp</a></div>
  </div>
  <div class="loc__map"><iframe title="<?= $v->t('head_office') ?>" src="<?= $v->mapEmbed() ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
</div>
