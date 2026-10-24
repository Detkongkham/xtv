<?php
/**
 * One product (product-<slug>.html). Only products with a picture have a page.
 * @var App\Publish\SiteView $v
 * @var array $p  product row
 */
use App\Publish\SiteView;

['division' => $d, 'group' => $g] = $v->data->placeOf($p);
$v->meta($v->productPage($p), $v->raw($p, 'seo_title') ?: $p['name'], $v->raw($p, 'seo_desc') ?: $p['name'] . ' — ' . $v->raw($d, 'name'), active: 'products');
$sups = $v->data->suppliersOf($p);
$picture = $v->data->media((int) $p['image_id']);
// brand only when one supplier makes it: a product that is a range (rapid tests, syringes) comes from several
$v->schema([
    '@type' => 'Product',
    'name' => $p['name'],
    'description' => $v->raw($p, 'summary') ?: $v->raw($g, 'summary'),
    'image' => $v->fileUrl($picture['path']),
    'url' => $v->url($v->productPage($p)),
    'category' => $v->raw($d, 'name') . ' > ' . $v->raw($g, 'name'),
    'brand' => count($sups) === 1 ? ['@type' => 'Brand', 'name' => $sups[0]['name']] : null,
]);

$spec = [[$v->t('sp_cat'), $v->tr($d, 'name')], [$v->t('sp_group'), $v->tr($g, 'name')]];
$custom = $v->data->specs($p);
if ($custom) {
    foreach ($custom as $row) {
        $spec[] = [$v->tr($row, 'label'), $v->tr($row, 'value')];
    }
} else {
    // until specifications are entered in the admin: the same general rows for every product
    $spec[] = [$v->t('sp_model'), $d['slug'] === 'security' ? $v->e($p['name']) : $v->t('sp_by_mfr')];
    $spec[] = [$v->t('sp_std'), $v->t('sp_by_mfr')];
    $spec[] = [$v->t('sp_warranty'), $v->t('sp_on_request')];
    $spec[] = [$v->t('sp_install'), $v->t('sp_by_xtk')];
}
if ($sups) {
    $spec[] = [$v->t('sp_sup'), implode(', ', array_map(static fn ($s) => '<a href="supplier-' . $s['slug'] . '.html">' . $v->e($s['name']) . '</a>', $sups))];
}

// related: others with a picture from the same group first, then from the rest of the division
$withPic = static fn (array $rows) => array_filter($rows, static fn ($x) => $x['image_id'] !== null && $x['id'] !== $p['id']);
$related = array_slice(array_values(array_unique(array_merge($withPic($v->data->products($g)), $withPic($v->data->divisionProducts($d))), SORT_REGULAR)), 0, 4);
$crumb = $v->partial('breadcrumb', ['trail' => [[$v->t('products'), 'products.html'], [$v->tr($d, 'name'), $d['slug'] . '.html']], 'last' => $v->e($p['name'])]);
?>
<section class="phead" style="padding-bottom:32px"><div class="wrap"><?= $crumb ?></div></section>
<section class="sec sec--tight" style="padding-top:40px"><div class="wrap"><div class="pd">
  <div class="pd__img rv"><?= $v->media($picture, $p['name'], '', false, App\Publish\SiteView::SIZES_WIDE) ?></div>
  <div class="rv">
    <span class="tag"><?= $v->tr($g, 'name') ?></span>
    <h1><?= $v->e($p['name']) ?></h1>
    <p class="lead" style="font-size:18px;line-height:1.8;margin-bottom:12px"><?= $v->productText($p, $g) ?></p>
    <?php if ($p['variants_note']): ?>
    <p class="small" style="margin-bottom:12px"><?= $v->e($p['variants_note']) ?></p>
    <?php endif ?>
    <p><?= $v->t('pd_p') ?></p>
    <?= $v->partial('supplied-by', ['p' => $p]) ?>
    <div class="btns"><a class="btn" href="contact.html"><?= $v->t('contact') ?></a><a class="btn btn--ghost" href="<?= $v->whatsapp() ?>" target="_blank" rel="noopener"><?= $v->brand('whatsapp') ?><?= $v->t('whatsapp') ?></a></div>
    <h2 class="h4" style="margin-bottom:12px"><?= $v->t('pd_spec') ?></h2>
    <table class="spec"><tbody>
      <?php foreach ($spec as [$th, $td]): ?>
      <tr><th scope="row"><?= $th ?></th><td><?= $td ?></td></tr>
      <?php endforeach ?>
    </tbody></table>
    <p class="small" style="margin-top:12px"><?= $v->t('pd_note') ?></p>
  </div>
</div></div></section>
<section class="sec sec--tight sec--tint"><div class="wrap"><div class="split" style="align-items:start">
  <?php if ($v->raw($p, 'body') !== ''): ?>
  <div class="rv"><h2 class="h3"><?= $v->t('pd_use') ?></h2><?= $v->paragraphs($v->raw($p, 'body'), ' style="margin-top:12px"') ?></div>
  <?php else: ?>
  <div class="rv"><h2 class="h3"><?= $v->t('pd_use') ?></h2><p style="margin-top:12px"><?= $v->tr($g, 'summary') ?></p><p style="margin-top:1.2em"><?= $v->tr($d, 'body') ?></p></div>
  <?php endif ?>
  <div class="rv">
    <h2 class="h3"><?= $v->t('pd_srv') ?></h2>
    <ul class="checks">
      <?php foreach (SiteView::SERVICES as [$k]): ?>
      <li><b><?= $v->t($k) ?></b> — <?= $v->t($k . '_p') ?></li>
      <?php endforeach ?>
    </ul>
  </div>
</div></div></section>
<section class="sec sec--surface sec--tight"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('related'), 'left' => true]) ?>
  <div class="grid g-4 keep-2">
    <?php foreach ($related as $i => $x): ?>
    <?= $v->partial('cards/product', ['p' => $x, 'i' => $i]) ?>
    <?php endforeach ?>
  </div>
</div></section>
<?= $v->partial('cta') ?>
