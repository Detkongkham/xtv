<?php
/**
 * All suppliers (suppliers.html): stats, key suppliers, filterable grid. Spec: docs/plan/08-suppliers.md.
 * @var App\Publish\SiteView $v
 */
$suppliers = $v->data->suppliers();
$featured = array_slice($v->data->featuredSuppliers(), 0, 6);
$nProducts = count(array_filter($v->data->allProducts(), static fn ($place) => $v->data->suppliersOf($place['product']) !== []));
$stats = [[count($suppliers), 'sup_stat1'], [count(array_unique(array_column($suppliers, 'country_code'))), 'sup_stat2'], [$nProducts, 'sup_stat3']];
$used = [];
foreach ($suppliers as $s) {
    foreach ($v->data->supplierDivisions($s) as $d) {
        $used[$d['slug']] = true;
    }
}
$v->meta('suppliers', $v->t('sup_h'), $v->t('sup_lead') . ' ' . implode(', ', array_column(array_slice($featured ?: $suppliers, 0, 5), 'name')), active: 'suppliers');
?>
<section class="phead phead--img on-dark"><div class="wrap">
  <?= $v->partial('breadcrumb', ['trail' => [], 'last' => $v->t('sup_h')]) ?>
  <h1><?= $v->t('sup_h') ?></h1>
  <p><?= $v->t('sup_lead') ?></p>
  <div class="stats stats--3">
    <?php foreach ($stats as $i => [$value, $k]): ?>
    <div class="stat rv" style="--i:<?= $i ?>"><b data-count><?= $value ?></b><span><?= $v->t($k) ?></span></div>
    <?php endforeach ?>
  </div>
</div></section>
<?php if ($featured): ?>
<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('sup_featured'), 'left' => true]) ?>
  <div class="grid g-3">
    <?php foreach ($featured as $i => $s): ?>
    <?= $v->partial('cards/supplier', ['s' => $s, 'i' => $i]) ?>
    <?php endforeach ?>
  </div>
</div></section>
<?php endif ?>
<section class="sec sec--surface"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('sup_grid_h'), 'left' => true]) ?>
  <div class="filters" data-filter="items">
    <button class="pill" type="button" data-g="all" aria-pressed="true"><?= $v->t('all') ?></button>
    <?php foreach ($v->data->divisions() as $d): if (isset($used[$d['slug']])): ?>
    <button class="pill" type="button" data-g="<?= $d['slug'] ?>" aria-pressed="false"><?= $v->tr($d, 'name') ?></button>
    <?php endif; endforeach ?>
  </div>
  <p class="small sup-result" aria-live="polite" data-result="<?= $v->e($v->t('sup_result')) ?>"><?= $v->f('sup_result', count($suppliers)) ?></p>
  <div class="grid g-5">
    <?php foreach ($suppliers as $i => $s): ?>
    <?= $v->partial('cards/supplier-tile', ['s' => $s, 'i' => $i]) ?>
    <?php endforeach ?>
  </div>
</div></section>
<?= $v->partial('cta', ['h' => $v->t('sup_cta_h')]) ?>
