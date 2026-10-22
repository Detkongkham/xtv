<?php
/**
 * One supplier (supplier-<slug>.html): its products by division, about text, other suppliers in the same divisions.
 * @var App\Publish\SiteView $v
 * @var array $s  supplier row
 */
$items = $v->data->supplierProducts($s);
$divs = $v->data->supplierDivisions($s);
$name = $v->e($s['name']);
$v->meta('supplier-' . $s['slug'], $v->f('sup_title', $s['name']), $v->f('sup_sells', n: count($items), name: $s['name']), active: 'suppliers');
$partnership = ['exclusive' => 'sup_exclusive', 'authorized' => 'sup_auth', 'distributor' => 'sup_distributor'];
$ids = array_flip(array_column($divs, 'id'));
$others = array_slice(array_values(array_filter($v->data->suppliers(), static function ($o) use ($v, $s, $ids): bool {
    return $o['id'] !== $s['id'] && array_intersect_key(array_flip(array_column($v->data->supplierDivisions($o), 'id')), $ids) !== [];
})), 0, 6);
?>
<section class="phead"><div class="wrap">
  <?= $v->partial('breadcrumb', ['trail' => [[$v->t('sup'), 'suppliers.html']], 'last' => $name]) ?>
  <div class="sup-head">
    <?= $v->partial('cards/logo-tile', ['s' => $s, 'h' => 120, 'cls' => 'logo-tile--lg', 'lazy' => false]) ?>
    <div>
      <h1><?= $name ?></h1>
      <p class="small"><?= $v->country($s) ?></p>
      <div class="chips"><?php if ($s['partnership']): ?><span class="badge-auth"><?= $v->ic('shield') ?><?= $v->t($partnership[$s['partnership']]) ?></span><?php endif ?><?php if ($s['since_year']): ?><span class="tag"><?= $v->f('sup_since', (int) $s['since_year']) ?></span><?php endif ?><?php foreach ($divs as $d): ?><a class="tag" href="<?= $d['slug'] ?>.html"><?= $v->tr($d, 'name') ?></a><?php endforeach ?></div>
      <p class="lead"><?= $v->f('sup_sells', n: count($items), name: $name) ?></p>
      <div class="btns"><a class="btn" href="contact.html"><?= $v->t('contact') ?></a><?php if ($s['website_url']): ?><a class="btn btn--ghost" href="<?= $v->e($s['website_url']) ?>" target="_blank" rel="noopener"><?= $v->t('sup_web') ?></a><?php endif ?></div>
    </div>
  </div>
</div></section>
<section class="sec sec--tight sec--surface"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->f('sup_prod_h', $name), 'left' => true]) ?>
  <?php foreach ($divs as $d): $mine = array_values(array_filter($items, static fn ($place) => $place['division']['id'] === $d['id'])) ?>
  <section class="pgroup">
    <h3 class="h3 rv"><?= $v->tr($d, 'name') ?></h3>
    <p class="small rv"><?= count($mine) ?> <?= $v->t('items') ?></p>
    <div class="grid g-4 keep-2">
      <?php foreach ($mine as $i => $place): ?>
      <?= $v->partial('cards/product', ['p' => $place['product'], 'i' => $i]) ?>
      <?php endforeach ?>
    </div>
  </section>
  <?php endforeach ?>
</div></section>
<?php if ($v->raw($s, 'about') !== ''): ?>
<section class="sec sec--tight"><div class="wrap"><div class="prose rv"><h2 class="h3"><?= $v->f('sup_about', $name) ?></h2><p><?= $v->tr($s, 'about') ?></p></div></div></section>
<?php endif ?>
<?php if ($others): ?>
<section class="sec sec--tight sec--tint"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('sup_others'), 'left' => true]) ?>
  <div class="logo-row rv">
    <?php foreach ($others as $o): ?>
    <?= $v->partial('cards/logo-tile', ['s' => $o, 'h' => 56, 'href' => 'supplier-' . $o['slug'] . '.html']) ?>
    <?php endforeach ?>
  </div>
</div></section>
<?php endif ?>
<?= $v->partial('cta') ?>
