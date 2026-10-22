<?php
/**
 * Skip link, header with mega menus and language menu, then the mobile drawer.
 * @var App\Publish\SiteView $v
 * @var string $slug  this page, for the language links
 * @var bool $overlay
 * @var ?string $active  top menu item to mark as current
 */
use App\Core\Lang;

$cur = static fn (string $k): string => $active === $k ? ' aria-current="page"' : '';
$about = [['about', 'about_profile', 'photo/4767.jpg'], ['vision', 'vision', 'photo/4733.jpg'], ['downloads', 'downloads', 'photo/4766.jpg']];
$divisions = $v->data->divisions();
$suppliers = $v->data->suppliers();
?>
<a class="skip" href="#main"><?= $v->t('skip') ?></a>
<header class="hdr<?= $overlay ? ' hdr--overlay' : '' ?>">
  <div class="wrap wrap--wide hdr__in">
    <a class="logo" href="index.html" aria-label="XTK — <?= $v->t('home') ?>"><img src="<?= $v->a ?>/img/logo.svg" alt="XTK" width="1663" height="1069"></a>
    <nav class="nav" aria-label="<?= $v->t('menu') ?>">
      <ul>
        <li class="has-mega">
          <a class="nav__link" href="about.html"<?= $cur('about') ?>><?= $v->t('about') ?><?= $v->ic('chev') ?></a>
          <div class="mega"><div class="wrap mega__in">
            <div><p class="mega__title"><?= $v->t('about') ?></p><p><?= $v->t('co_tag') ?></p><?= $v->arrowLink($v->t('about_profile'), 'about.html') ?></div>
            <div class="mega__cards mega__cards--3">
              <?php foreach ($about as [$page, $key, $pic]): ?>
              <a class="mega__card" href="<?= $page ?>.html"><span class="ph"><?= $v->img($pic) ?></span><?= $v->t($key) ?></a>
              <?php endforeach ?>
            </div>
          </div></div>
        </li>
        <li class="has-mega">
          <a class="nav__link" href="products.html"<?= $cur('products') ?>><?= $v->t('products') ?><?= $v->ic('chev') ?></a>
          <div class="mega"><div class="wrap mega__in">
            <div><p class="mega__title"><?= $v->t('products') ?></p><p><?= $v->t('home_prod_p') ?></p><?= $v->arrowLink($v->t('all_products'), 'products.html') ?></div>
            <div class="mega__cards">
              <?php foreach ($divisions as $d): ?>
              <a class="mega__card" href="<?= $d['slug'] ?>.html"><span class="ph"><?= $v->media($v->data->media($d['image_id']), sizes: '240px') ?></span><?= $v->tr($d, 'name') ?></a>
              <?php endforeach ?>
            </div>
          </div></div>
        </li>
        <?php if ($suppliers): ?>
        <li class="has-mega">
          <a class="nav__link" href="suppliers.html"<?= $cur('suppliers') ?>><?= $v->t('sup') ?><?= $v->ic('chev') ?></a>
          <div class="mega"><div class="wrap mega__in">
            <div><p class="mega__title"><?= $v->t('sup') ?></p><p><?= $v->t('sup_mega_p') ?></p><p class="mega__meta"><?= $v->supplierMeta() ?></p><?= $v->arrowLink($v->t('sup_all'), 'suppliers.html') ?></div>
            <div class="mega__logos">
              <?php foreach (array_slice($v->data->featuredSuppliers(), 0, 10) as $s): ?>
              <?= $v->partial('cards/logo-tile', ['s' => $s, 'h' => 40, 'href' => 'supplier-' . $s['slug'] . '.html']) ?>
              <?php endforeach ?>
            </div>
          </div></div>
        </li>
        <?php endif ?>
        <li><a class="nav__link" href="solutions.html"<?= $cur('solutions') ?>><?= $v->t('solutions') ?></a></li>
        <li><a class="nav__link" href="services.html"<?= $cur('services') ?>><?= $v->t('services') ?></a></li>
        <li><a class="nav__link" href="news.html"<?= $cur('news') ?>><?= $v->t('news') ?></a></li>
        <li><a class="nav__link" href="contact.html"<?= $cur('contact') ?>><?= $v->t('contact') ?></a></li>
      </ul>
    </nav>
    <div class="hdr__tools">
      <details class="lang">
        <summary aria-label="<?= $v->t('language') ?>"><?= $v->ic('globe') ?><?= Lang::SHORT[$v->lang] ?><?= $v->ic('chev') ?></summary>
        <div class="lang__menu">
          <?php foreach (Lang::CODES as $l): ?>
          <a href="../<?= $l ?>/<?= $slug ?>.html" lang="<?= Lang::HTML[$l] ?>" hreflang="<?= Lang::HTML[$l] ?>"<?= $l === $v->lang ? ' aria-current="true"' : '' ?>><?= Lang::NAMES[$l] ?></a>
          <?php endforeach ?>
        </div>
      </details>
      <a class="btn btn--sm hdr__cta" href="contact.html"><?= $v->t('contact') ?></a>
      <button class="burger" type="button" aria-label="<?= $v->t('menu') ?>" aria-expanded="false" aria-controls="drawer"><?= $v->ic('menu') ?></button>
    </div>
  </div>
</header>
<?= $v->partial('drawer', ['slug' => $slug, 'about' => $about]) ?>
