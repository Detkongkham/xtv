<?php
/**
 * Grid of the division cards.
 * @var App\Publish\SiteView $v
 * @var int $cols  grid columns, default 5
 */
?>
<div class="grid g-<?= $cols ?? 5 ?>">
  <?php foreach ($v->data->divisions() as $i => $d): ?>
  <a class="card rv" style="--i:<?= $i ?>" href="<?= $d['slug'] ?>.html">
    <span class="card__img"><?= $v->media($v->data->media($d['image_id']), $v->raw($d, 'name')) ?></span>
    <span class="card__body"><b class="h4"><?= $v->tr($d, 'name') ?></b><p><?= $v->tr($d, 'summary') ?></p><span class="card__foot"><?= count($v->data->divisionProducts($d)) ?> <?= $v->t('items') ?><span class="circ"><?= $v->ic('arrow') ?></span></span></span>
  </a>
  <?php endforeach ?>
</div>
