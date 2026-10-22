<?php
/**
 * Footer and the floating WhatsApp / call / back-to-top buttons.
 * @var App\Publish\SiteView $v
 */
$products = [];
foreach ($v->data->divisions() as $d) {
    $products[] = [$d['slug'], $v->tr($d, 'name')];
}
if ($v->data->suppliers()) {
    $products[] = ['suppliers', $v->t('sup')];
}
$cols = [
    [$v->t('company'), [['about', $v->t('about_profile')], ['vision', $v->t('vision')], ['news', $v->t('news')]]],
    [$v->t('products'), $products],
    [$v->t('support'), [['services', $v->t('services')], ['solutions', $v->t('solutions')], ['downloads', $v->t('downloads')], ['faq', $v->t('faq')]]],
];
[$tel, $mob, $mail, $wa] = [$v->contact('tel'), $v->contact('mobile'), $v->contact('email'), $v->whatsapp()];
?>
<footer class="ftr">
  <div class="wrap">
    <div class="ftr__top">
      <div class="ftr__brand"><img src="<?= $v->a ?>/img/logo-ring.svg" alt="XTK" width="1751" height="1157" loading="lazy"><p><?= $v->t('co_tag') ?></p></div>
      <?php foreach ($cols as [$title, $links]): ?>
      <div class="ftr__col">
        <h3><?= $title ?></h3>
        <ul>
          <?php foreach ($links as [$page, $label]): ?>
          <li><a href="<?= $page ?>.html"><?= $label ?></a></li>
          <?php endforeach ?>
        </ul>
      </div>
      <?php endforeach ?>
      <div class="ftr__contact">
        <h3><?= $v->t('contact') ?></h3>
        <ul>
          <li><?= $v->ic('pin') ?><span><?= $v->address() ?></span></li>
          <li><?= $v->ic('phone') ?><span><a href="tel:<?= $v->telHref($tel) ?>"><?= $v->e($tel) ?></a><br><a href="tel:<?= $v->telHref($mob) ?>"><?= $v->e($mob) ?></a></span></li>
          <li><?= $v->ic('mail') ?><a href="mailto:<?= $v->e($mail) ?>"><?= $v->e($mail) ?></a></li>
        </ul>
        <div class="social">
          <a href="<?= $wa ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?= $v->ic('chat') ?></a>
          <a href="tel:<?= $v->telHref($mob) ?>" aria-label="<?= $v->t('call_us') ?>"><?= $v->ic('phone') ?></a>
          <a href="mailto:<?= $v->e($mail) ?>" aria-label="<?= $v->t('email') ?>"><?= $v->ic('mail') ?></a>
        </div>
      </div>
    </div>
    <div class="ftr__bot">
      <p>&copy; 2026 <?= $v->t('co_name') ?>. <?= $v->t('rights') ?>.</p>
      <ul>
        <li><a href="privacy.html"><?= $v->t('privacy') ?></a></li>
        <li><a href="terms.html"><?= $v->t('terms') ?></a></li>
        <li><a href="sitemap.html"><?= $v->t('sitemap') ?></a></li>
      </ul>
    </div>
  </div>
</footer>
<div class="float">
  <a class="wa" href="<?= $wa ?>" target="_blank" rel="noopener" aria-label="<?= $v->t('whatsapp') ?>"><?= $v->ic('chat') ?></a>
  <a href="tel:<?= $v->telHref($mob) ?>" aria-label="<?= $v->t('call_us') ?>"><?= $v->ic('phone') ?></a>
  <button class="top" type="button" aria-label="<?= $v->t('back_top') ?>"><?= $v->ic('up') ?></button>
</div>
