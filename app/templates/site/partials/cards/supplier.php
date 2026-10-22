<?php
/**
 * Key-supplier card: logo, country, about, up to 3 product pictures.
 * @var App\Publish\SiteView $v
 * @var array $s
 * @var int $i
 */
$items = $v->data->supplierProducts($s);
$pics = array_slice(array_values(array_filter(array_column($items, 'product'), static fn ($p) => $p['image_id'] !== null)), 0, 3);
?>
<a class="card card--supplier rv" style="--i:<?= $i % 3 ?>" href="supplier-<?= $s['slug'] ?>.html">
  <span class="card__logo<?= (int) $s['logo_on_dark'] ? ' logo-tile--dark' : '' ?>"><?= $v->supplierLogo($s, 72) ?></span>
  <span class="card__body"><b class="h4"><?= $v->e($s['name']) ?></b><span class="small"><?= $v->country($s) ?></span><?= $v->raw($s, 'about') !== '' ? '<p class="clamp2">' . $v->tr($s, 'about') . '</p>' : '' ?><span class="thumbs"><?php foreach ($pics as $p): ?><?= $v->media($v->data->media((int) $p['image_id']), $p['name']) ?><?php endforeach ?><?= count($items) > count($pics) ? '<span class="small">' . $v->f('sup_more', count($items) - count($pics)) . '</span>' : '' ?></span><span class="card__foot"><?= $v->f('sup_view', $v->e($s['name'])) ?><span class="circ"><?= $v->ic('arrow') ?></span></span></span>
</a>
