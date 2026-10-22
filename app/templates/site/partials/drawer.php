<?php
/**
 * Mobile menu (opened by the burger button).
 * @var App\Publish\SiteView $v
 * @var string $slug
 * @var array $about  [page, text key, picture] of the About menu
 */
use App\Core\Lang;
?>
<div class="drawer" id="drawer" aria-label="<?= $v->t('menu') ?>">
  <div class="drawer__top"><img src="<?= $v->a ?>/img/logo.svg" alt="XTK"><button class="burger drawer__close" type="button" aria-label="<?= $v->t('close') ?>" style="display:grid"><?= $v->ic('x') ?></button></div>
  <details>
    <summary><?= $v->t('about') ?><?= $v->ic('chev') ?></summary>
    <div>
      <?php foreach ($about as [$page, $key]): ?>
      <a href="<?= $page ?>.html"><?= $v->t($key) ?></a>
      <?php endforeach ?>
    </div>
  </details>
  <details>
    <summary><?= $v->t('products') ?><?= $v->ic('chev') ?></summary>
    <div>
      <a href="products.html"><?= $v->t('all_products') ?></a>
      <?php foreach ($v->data->divisions() as $d): ?>
      <a href="<?= $d['slug'] ?>.html"><?= $v->tr($d, 'name') ?></a>
      <?php endforeach ?>
    </div>
  </details>
  <?php if ($v->data->suppliers()): ?>
  <a class="drawer__link" href="suppliers.html"><?= $v->t('sup') ?></a>
  <?php endif ?>
  <a class="drawer__link" href="solutions.html"><?= $v->t('solutions') ?></a>
  <a class="drawer__link" href="services.html"><?= $v->t('services') ?></a>
  <a class="drawer__link" href="news.html"><?= $v->t('news') ?></a>
  <a class="drawer__link" href="contact.html"><?= $v->t('contact') ?></a>
  <div class="drawer__langs">
    <?php foreach (Lang::CODES as $l): ?>
    <a href="../<?= $l ?>/<?= $slug ?>.html"<?= $l === $v->lang ? ' aria-current="true"' : '' ?>><?= Lang::NAMES[$l] ?></a>
    <?php endforeach ?>
  </div>
  <a class="btn" href="contact.html"><?= $v->t('contact') ?></a>
</div>
