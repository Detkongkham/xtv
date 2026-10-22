<?php
/**
 * After-sales service (services.html).
 * @var App\Publish\SiteView $v
 */
use App\Publish\SiteView;

$v->meta('services', $v->t('services'), $v->t('srv_lead'), active: 'services');
$security = $v->data->division('security');
$secName = $security ? $v->raw($security, 'name') : '';
$mob = $v->contact('mobile');
?>
<?= $v->partial('page-head', ['title' => $v->t('services'), 'lead' => $v->t('srv_lead'), 'image' => 'photo/4765.jpg']) ?>
<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('services'), 'p' => $v->t('home_srv_p')]) ?>
  <div class="grid g-4"><?= $v->partial('cards/text', ['items' => SiteView::SERVICES]) ?></div>
</div></section>
<section class="sec sec--surface"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('srv_process')]) ?>
  <div class="grid g-4 steps"><?= $v->partial('steps', ['keys' => ['step1', 'step2', 'step3', 'step4']]) ?></div>
</div></section>
<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('srv_fields')]) ?>
  <div class="grid g-2">
    <div class="card rv"><div class="card__img" style="aspect-ratio:1/1"><?= $v->img('photo/service-medical.jpg', $v->t('srv_med')) ?></div><div class="card__body"><h3 class="h4"><?= $v->t('srv_med') ?></h3><p><?= $v->t('srv_med_p') ?></p></div></div>
    <div class="card rv" style="--i:1"><div class="card__img" style="aspect-ratio:1/1"><?= $v->img('photo/service-security.jpg', $secName) ?></div><div class="card__body"><h3 class="h4"><?= $v->e($secName) ?></h3><p><?= $v->t('srv_sec_p') ?></p></div></div>
  </div>
</div></section>
<section class="cta on-dark">
  <div class="wrap rv">
    <h2 class="h2"><?= $v->t('srv_hot_h') ?></h2>
    <p><?= $v->t('hotline') ?>: <a href="tel:<?= $v->telHref($mob) ?>" style="color:#fff;font-weight:600;font-size:28px"><?= $v->e($mob) ?></a></p>
    <div class="btns"><a class="btn btn--white" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->ic('chat') ?><?= $v->t('whatsapp') ?></a><a class="btn btn--light" href="contact.html"><?= $v->t('contact') ?></a></div>
  </div>
</section>
