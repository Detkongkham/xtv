<?php
/**
 * Home page (index.html).
 * @var App\Publish\SiteView $v
 */
use App\Publish\PageList;
use App\Publish\SiteView;

$v->meta('index', $v->t('home'), overlay: true);
$a = $v->a;
$divisions = $v->data->divisions();
// hero slides from /admin/hero; a button whose page is gone (hidden division, product) goes to all products
$slides = $v->data->heroSlides();
$pages = array_flip(PageList::names($v->data));
$stats = [
    [(string) ($v->data->setting('stats')['founded'] ?? ''), 'stat1'],
    [($v->data->setting('stats')['experience'] ?? '') . '<sup>+</sup>', 'stat2'],
    [(string) count($divisions), 'stat3'],
    [(string) count($v->data->allProducts()), 'stat4'],
];
?>
<div class="loader" aria-hidden="true"><img src="<?= $a ?>/img/logo.svg" alt=""></div><noscript><style>.loader{display:none}</style></noscript>
<section class="hero" aria-roledescription="carousel">
  <?php foreach ($slides as $i => $s): $tag = $i === 0 ? 'h1' : 'p'; [$eye, $text, $label] = [$v->tr($s, 'eyebrow'), $v->tr($s, 'text'), $v->tr($s, 'button')] ?>
  <div class="hero__slide<?= $i === 0 ? ' is-active' : '' ?>">
    <?= $v->heroMedia($s) ?>
    <div class="hero__body"><div class="wrap"><div class="hero__text">
      <?= $eye !== '' ? '<span class="hero__eyebrow">' . $eye . '</span>' : '' ?><<?= $tag ?> class="hero__title"><?= $v->tr($s, 'title') ?></<?= $tag ?>><?= $text !== '' ? '<p>' . $text . '</p>' : '' ?>
      <div class="btns"><?= $label !== '' ? '<a class="btn btn--white" href="' . (isset($pages[$s['button_page']]) ? $s['button_page'] : 'products') . '.html">' . $label . '</a>' : '' ?><a class="btn btn--light" href="contact.html"><?= $v->t('contact') ?></a></div>
    </div></div></div>
  </div>
  <?php endforeach ?>
  <div class="hero__ui"><div class="wrap">
    <span class="hero__scroll"><?= $v->t('scroll') ?></span>
    <div class="hero__nav"><span class="hero__count" aria-live="polite"><?= sprintf('01 / %02d', count($slides)) ?></span><span class="hero__bar"><i></i></span><button class="hero__btn hero__btn--prev" type="button" aria-label="<?= $v->t('prev') ?>"><?= $v->ic('arrow') ?></button><button class="hero__btn hero__btn--next" type="button" aria-label="<?= $v->t('next') ?>"><?= $v->ic('arrow') ?></button></div>
  </div></div>
</section>

<section class="sec sec--statement"><div class="wrap" style="max-width:1000px;text-align:center"><p class="lead rv"><?= $v->t('statement') ?></p></div></section>

<?php $strip = implode('', array_map(static fn ($n) => $v->img("photo/{$n}.jpg"), ['4773', '46364', '4719', '35963', '4767', '47584', '4765', '4733'])) ?>
<div class="marquee" aria-hidden="true"><div class="marquee__track"><?= $strip ?></div><div class="marquee__track"><?= $strip ?></div></div>

<section class="sec sec--surface"><div class="wrap"><div class="split split--38">
  <div>
    <?= $v->partial('section-head', ['h' => $v->t('products'), 'p' => $v->t('home_prod_p'), 'left' => true]) ?>
    <nav class="divlist rv" aria-label="<?= $v->t('products') ?>">
      <?php foreach ($divisions as $d): $first = $v->data->groups($d)[0] ?? null ?>
      <a href="<?= $d['slug'] ?>.html"><span class="divlist__l"><?= $v->ic($first ? $first['icon'] : $d['icon']) ?><?= $v->tr($d, 'name') ?></span><?= $v->ic('arrow') ?></a>
      <?php endforeach ?>
    </nav>
    <p style="margin-top:32px" class="rv"><?= $v->arrowLink($v->t('all_products'), 'products.html') ?></p>
  </div>
  <div class="rail" tabindex="0" aria-label="<?= $v->t('products') ?>">
    <?php foreach ($v->data->featuredProducts() as $i => $p): ?>
    <?= $v->partial('cards/product', ['p' => $p, 'i' => $i]) ?>
    <?php endforeach ?>
  </div>
</div></div></section>

<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('home_groups'), 'p' => $v->t('home_groups_p')]) ?>
  <div class="gtiles">
    <?php $i = 0; foreach ($divisions as $d): foreach ($v->data->groups($d) as $g): ?>
    <a class="gtile rv" style="--i:<?= $i++ % 6 ?>" href="<?= $d['slug'] ?>.html#<?= $g['code'] ?>"><span class="gtile__ico"><?= $v->ic($g['icon']) ?></span><b><?= $v->tr($g, 'name') ?></b><span><?= $v->tr($d, 'name') ?></span></a>
    <?php endforeach; endforeach ?>
  </div>
</div></section>

<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('solutions'), 'p' => $v->t('home_sol_p')]) ?>
  <div class="tabs rv">
    <div class="tabs__list" role="tablist" aria-label="<?= $v->t('solutions') ?>">
      <?php foreach (SiteView::SOLUTIONS as $i => [$k]): ?>
      <button class="tabs__btn" type="button" role="tab" id="tab-<?= $k ?>" aria-controls="panel-<?= $k ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"<?= $i === 0 ? '' : ' tabindex="-1"' ?>><?= $v->t('sol_' . $k) ?></button>
      <?php endforeach ?>
    </div>
    <div>
      <?php foreach (SiteView::SOLUTIONS as $i => [$k, $pic, $chips]): ?>
      <div class="tabs__panel" role="tabpanel" id="panel-<?= $k ?>" aria-labelledby="tab-<?= $k ?>"<?= $i === 0 ? '' : ' hidden' ?>>
        <?= $v->img($pic) ?>
        <h3 class="h3"><?= $v->t('sol_' . $k) ?></h3>
        <p><?= $v->t('sol_' . $k . '_p') ?></p>
        <?= $v->partial('chips', ['codes' => $chips]) ?>
        <div class="btns"><?= $v->arrowLink($v->t('learn_more'), 'solutions.html#' . $k) ?></div>
      </div>
      <?php endforeach ?>
    </div>
  </div>
</div></section>

<section class="sec sec--tint"><div class="wrap">
  <div class="split">
    <div class="rv">
      <div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2"><?= $v->t('home_about_h') ?></h2></div>
      <p><?= $v->t('about_p1') ?></p>
      <p style="margin-top:1.2em"><?= $v->t('about_p2') ?></p>
      <p style="margin-top:32px"><?= $v->arrowLink($v->t('about_profile'), 'about.html') ?></p>
    </div>
    <div class="split__img rv"><?= $v->img('photo/4767.jpg') ?></div>
  </div>
  <div class="stats" style="margin-top:80px">
    <?php foreach ($stats as $i => [$value, $k]): ?>
    <div class="stat rv" style="--i:<?= $i ?>"><b data-count><?= $value ?></b><span><?= $v->t($k) ?></span></div>
    <?php endforeach ?>
  </div>
</div></section>

<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('why'), 'p' => $v->t('home_why_p')]) ?>
  <div class="grid g-4"><?= $v->partial('cards/text', ['items' => SiteView::WHY]) ?></div>
</div></section>

<section class="sec sec--surface"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('commit_h'), 'p' => $v->t('commit_p')]) ?>
  <div class="grid g-3 steps"><?= $v->partial('steps', ['keys' => ['commit1', 'commit2', 'commit3']]) ?></div>
</div></section>

<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('services'), 'p' => $v->t('home_srv_p')]) ?>
  <div class="grid g-4"><?= $v->partial('cards/text', ['items' => SiteView::SERVICES]) ?></div>
  <p style="text-align:center;margin-top:48px" class="rv"><?= $v->arrowLink($v->t('learn_more'), 'services.html') ?></p>
</div></section>

<section class="sec sec--tint"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('srv_process'), 'p' => $v->t('home_process_p')]) ?>
  <div class="grid g-4 steps"><?= $v->partial('steps', ['keys' => ['step1', 'step2', 'step3', 'step4']]) ?></div>
</div></section>

<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('news')]) ?>
  <?= $v->partial('cards/news') ?>
  <p style="text-align:center;margin-top:48px" class="rv"><?= $v->arrowLink($v->t('view_all'), 'news.html') ?></p>
</div></section>

<section class="sec sec--surface"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('home_contact_h')]) ?>
  <?= $v->partial('location') ?>
</div></section>

<?= $v->partial('cta') ?>
