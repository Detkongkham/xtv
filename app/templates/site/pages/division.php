<?php
/**
 * One division with its product groups (<division>.html).
 * @var App\Publish\SiteView $v
 * @var array $d  division row
 */
$v->meta($d['slug'], $v->raw($d, 'name'), $v->raw($d, 'body'), active: 'products');
$groups = $v->data->groups($d);
?>
<?= $v->partial('page-head', ['title' => $v->tr($d, 'name'), 'lead' => $v->tr($d, 'summary'), 'trail' => [[$v->t('products'), 'products.html']]]) ?>
<nav class="subnav" aria-label="<?= $v->t('products') ?>"><div class="wrap"><ul>
  <li><a href="products.html"><?= $v->t('all') ?></a></li>
  <?php foreach ($v->data->divisions() as $x): ?>
  <li><a href="<?= $x['slug'] ?>.html"<?= $x['id'] === $d['id'] ? ' aria-current="page"' : '' ?>><?= $v->tr($x, 'name') ?></a></li>
  <?php endforeach ?>
</ul></div></nav>
<section class="sec sec--tight"><div class="wrap"><div class="split">
  <div class="rv">
    <div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2"><?= $v->t('div_about') ?></h2></div>
    <p><?= $v->tr($d, 'body') ?></p>
    <ul class="checks"><li><?= $v->t('commit1_p') ?></li><li><?= $v->t('commit2_p') ?></li><li><?= $v->t('commit3_p') ?></li></ul>
  </div>
  <div class="split__img rv" style="border:1px solid var(--line)"><?= $v->media($v->data->media($d['image_id']), $v->raw($d, 'name')) ?></div>
</div></div></section>
<section class="sec sec--tight sec--surface"><div class="wrap">
  <?php if (count($groups) > 1): ?>
  <div class="filters" data-filter>
    <button class="pill" type="button" data-g="all" aria-pressed="true"><?= $v->t('all') ?></button>
    <?php foreach ($groups as $g): ?>
    <button class="pill" type="button" data-g="<?= $g['code'] ?>" aria-pressed="false"><?= $v->tr($g, 'name') ?></button>
    <?php endforeach ?>
  </div>
  <?php endif ?>
  <?php foreach ($groups as $g): $items = $v->data->products($g) ?>
  <section class="pgroup" id="<?= $g['code'] ?>">
    <h2 class="h3 rv"><?= $v->tr($g, 'name') ?></h2>
    <p class="rv" style="margin-bottom:8px"><?= $v->tr($g, 'summary') ?></p>
    <p class="small rv"><?= count($items) ?> <?= $v->t('items') ?></p>
    <div class="grid g-4 keep-2">
      <?php foreach ($items as $i => $p): ?>
      <?= $v->partial('cards/product', ['p' => $p, 'i' => $i]) ?>
      <?php endforeach ?>
    </div>
  </section>
  <?php endforeach ?>
</div></section>
<?= $v->partial('cta') ?>
