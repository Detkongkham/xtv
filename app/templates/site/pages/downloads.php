<?php
/**
 * Downloads (downloads.html). Placeholder rows until the client's PDFs are added through the downloads table (P2-C9).
 * @var App\Publish\SiteView $v
 */
$v->meta('downloads', $v->t('downloads'), $v->t('dl_lead'));
$button = '<a class="btn btn--ghost btn--sm" href="#">' . $v->ic('download') . $v->t('download') . '</a>';
?>
<?= $v->partial('page-head', ['title' => $v->t('downloads'), 'lead' => $v->t('dl_lead')]) ?>
<section class="sec sec--tight"><div class="wrap" style="max-width:1040px"><ul class="rows">
  <li class="rv"><div><h3 class="h4"><?= $v->t('dl_profile') ?></h3><p class="meta"><span>PDF</span><span>XTK</span></p></div><?= $button ?></li>
  <?php foreach ($v->data->divisions() as $d): ?>
  <li class="rv"><div><h3 class="h4"><?= $v->t('dl_catalog') ?> — <?= $v->tr($d, 'name') ?></h3><p class="meta"><span>PDF</span><span><?= $v->tr($d, 'summary') ?></span></p></div><?= $button ?></li>
  <?php endforeach ?>
</ul></div></section>
<?= $v->partial('cta') ?>
