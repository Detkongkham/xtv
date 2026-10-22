<?php
/**
 * Downloads (downloads.html): the visible rows of the downloads table, each a PDF from the media library.
 * @var App\Publish\SiteView $v
 */
$v->meta('downloads', $v->t('downloads'), $v->t('dl_lead'));
$rows = $v->data->downloads();
$size = static fn (int $b): string => $b >= 1048576 ? number_format($b / 1048576, 1) . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
?>
<?= $v->partial('page-head', ['title' => $v->t('downloads'), 'lead' => $v->t('dl_lead')]) ?>
<section class="sec sec--tight"><div class="wrap" style="max-width:1040px">
  <?php if ($rows === []): ?>
  <div class="aside-box rv" style="text-align:center"><p><?= $v->t('dl_empty') ?></p><div class="btns" style="justify-content:center;margin-top:16px"><a class="btn" href="contact.html"><?= $v->t('contact') ?></a></div></div>
  <?php else: ?>
  <ul class="rows">
    <?php foreach ($rows as $dl): $file = $v->data->media((int) $dl['file_id']); $division = $v->data->divisionById($dl['division_id'] === null ? null : (int) $dl['division_id']) ?>
    <li class="rv"><div><h3 class="h4"><?= $v->tr($dl, 'title') ?></h3><p class="meta"><span>PDF</span><span><?= $size((int) $file['size_bytes']) ?></span><span><?= $v->raw($dl, 'summary') !== '' ? $v->tr($dl, 'summary') : ($division ? $v->tr($division, 'name') : 'XTK') ?></span></p></div><a class="btn btn--ghost btn--sm" href="../<?= $v->e($file['path']) ?>" download="<?= $v->e($file['original_name']) ?>"><?= $v->ic('download') ?><?= $v->t('download') ?></a></li>
    <?php endforeach ?>
  </ul>
  <?php endif ?>
</div></section>
<?= $v->partial('cta') ?>
