<?php
/**
 * Not found (404.html, served by .htaccess ErrorDocument).
 * @var App\Publish\SiteView $v
 */
$v->meta('404', $v->t('nf_h'));
?>
<section class="nf"><div class="wrap">
  <b>404</b>
  <h1 class="h2" style="margin-top:8px"><?= $v->t('nf_h') ?></h1>
  <p style="margin:12px 0 28px"><?= $v->t('nf_p') ?></p>
  <div class="btns" style="justify-content:center"><a class="btn" href="index.html"><?= $v->t('nf_b') ?></a><a class="btn btn--ghost" href="sitemap.html"><?= $v->t('sitemap') ?></a></div>
</div></section>
