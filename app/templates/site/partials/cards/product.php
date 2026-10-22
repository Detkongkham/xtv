<?php
/**
 * Product card. With a picture it links to the product page; without one it is an "ask for a price" card.
 * @var App\Publish\SiteView $v
 * @var array $p  product row
 * @var int $i    position, for the reveal delay
 */
['division' => $d, 'group' => $g] = $v->data->placeOf($p);
$sups = $v->data->suppliersOf($p);
$by = $sups ? '<span class="small">' . $v->f('sup_by', $v->e($sups[0]['name']) . (count($sups) > 1 ? ' +' . (count($sups) - 1) : '')) . '</span>' : '';
?>
<?php if ($p['image_id'] !== null): ?>
<a class="card rv" style="--i:<?= $i % 4 ?>" href="<?= $v->productPage($p) ?>.html">
  <span class="card__img"><?= $v->media($v->data->media((int) $p['image_id']), $p['name']) ?></span>
  <span class="card__body"><span class="small"><?= $v->tr($d, 'name') ?></span><b class="h4"><?= $v->e($p['name']) ?></b><?= $by ?><p class="clamp2"><?= $v->productText($p, $g) ?></p><span class="card__foot"><?= $v->t('view_detail') ?><span class="circ"><?= $v->ic('arrow') ?></span></span></span>
</a>
<?php else: ?>
<a class="card card--text rv" style="--i:<?= $i % 4 ?>" href="contact.html">
  <span class="card__body"><span class="ico"><?= $v->ic($d['icon']) ?></span><b class="h4"><?= $v->e($p['name']) ?></b><?= $by ?><span class="small"><?= $v->tr($d, 'name') ?></span><span class="card__foot"><?= $v->t('ask_price') ?><span class="circ"><?= $v->ic('arrow') ?></span></span></span>
</a>
<?php endif ?>
