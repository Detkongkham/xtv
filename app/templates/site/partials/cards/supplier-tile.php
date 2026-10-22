<?php
/**
 * Tile in the all-suppliers grid; data-divs feeds the division filter.
 * @var App\Publish\SiteView $v
 * @var array $s
 * @var int $i
 */
$divs = implode(' ', array_column($v->data->supplierDivisions($s), 'slug'));
?>
<a class="sup-tile rv" style="--i:<?= $i % 5 ?>" href="supplier-<?= $s['slug'] ?>.html" data-divs="<?= $divs ?>"><?= $v->partial('cards/logo-tile', ['s' => $s, 'h' => 56]) ?><b><?= $v->e($s['name']) ?></b><span class="small"><?= $v->country($s) ?></span><span class="small"><?= count($v->data->supplierProducts($s)) ?> <?= $v->t('items') ?></span></a>
