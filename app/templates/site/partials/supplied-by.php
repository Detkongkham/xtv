<?php
/**
 * "Supplied by" block on a product page: up to 4 suppliers, primary first.
 * @var App\Publish\SiteView $v
 * @var array $p
 */
$sups = array_slice($v->data->suppliersOf($p), 0, 4);
if (!$sups) {
    return;
}
?>
<div class="supplied-by">
  <p class="small"><?= $v->t('supplied_by') ?></p>
  <ul>
    <?php foreach ($sups as $s): ?>
    <li><?= $v->partial('cards/logo-tile', ['s' => $s, 'h' => 40, 'href' => 'supplier-' . $s['slug'] . '.html']) ?><span><b><a href="supplier-<?= $s['slug'] ?>.html"><?= $v->e($s['name']) ?></a></b> <span class="small">(<?= $v->country($s) ?>)</span><br><a class="small" href="supplier-<?= $s['slug'] ?>.html"><?= $v->f('sup_other_from', $v->e($s['name'])) ?></a></span></li>
    <?php endforeach ?>
  </ul>
</div>
