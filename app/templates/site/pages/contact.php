<?php
/**
 * Contact page with the form (contact.html).
 * @var App\Publish\SiteView $v
 */
$v->meta('contact', $v->t('contact'), $v->t('contact_lead'), active: 'contact');
[$tel, $mob, $mail, $wa] = [$v->contact('tel'), $v->contact('mobile'), $v->contact('email'), $v->whatsapp()];
$tiles = [
    ['q_general', 'phone', 'tel:' . $v->telHref($tel), $tel],
    ['q_sales', 'mail', 'mailto:' . $mail, $mail],
    ['q_service', 'tool', 'tel:' . $v->telHref($mob), $mob],
    ['whatsapp', 'chat', $wa, $mob],
];
?>
<?= $v->partial('page-head', ['title' => $v->t('contact'), 'lead' => $v->t('contact_lead')]) ?>
<section class="sec sec--tight"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('quick'), 'left' => true]) ?>
  <div class="tiles rv">
    <?php foreach ($tiles as [$k, $icon, $href, $text]): ?>
    <a class="tile" href="<?= $v->e($href) ?>"<?= str_starts_with($href, 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= $v->ic($icon) ?><b><?= $v->t($k) ?></b><span><?= $v->e($text) ?></span></a>
    <?php endforeach ?>
  </div>
</div></section>
<section class="sec sec--surface"><div class="wrap"><div class="split split--38">
  <div class="rv">
    <?= $v->partial('section-head', ['h' => $v->t('contact_form_h'), 'left' => true]) ?>
    <?= $v->partial('contact-aside') ?>
  </div>
  <div class="panel rv"><?= $v->partial('contact-form') ?></div>
</div></div></section>
<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('home_contact_h')]) ?>
  <?= $v->partial('location') ?>
</div></section>
