<?php
/**
 * Company profile (about.html).
 * @var App\Publish\SiteView $v
 */
use App\Publish\SiteView;

$v->meta('about', $v->t('about_profile'), $v->t('about_lead'), active: 'about');
$names = array_map(static fn ($d) => $v->tr($d, 'name'), $v->data->divisions());
$facts = [
    [$v->t('f_name'), $v->t('co_name') . ' (XTK)'],
    [$v->t('f_reg'), $v->t('f_reg_v')],
    [$v->t('f_biz'), implode(' · ', $names)],
    [$v->t('head_office'), $v->address()],
    [$v->t('contact'), $v->t('tel') . ' ' . $v->e($v->contact('tel')) . ($v->contact('fax') !== '' ? ' · ' . $v->t('fax') . ' ' . $v->e($v->contact('fax')) : '')],
    [$v->t('email'), $v->e($v->contact('email'))],
];
?>
<?= $v->partial('page-head', ['title' => $v->t('about_profile'), 'lead' => $v->t('about_lead'), 'trail' => [[$v->t('about'), 'about.html']], 'image' => 'photo/4767.jpg']) ?>
<section class="sec"><div class="wrap"><div class="split">
  <div class="rv">
    <div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2"><?= $v->t('co_name') ?></h2></div>
    <div class="prose"><p><?= $v->t('about_p1') ?></p><p><?= $v->t('about_p2') ?></p></div>
    <div class="btns" style="margin-top:32px"><a class="btn" href="downloads.html"><?= $v->ic('download') ?><?= $v->t('profile_dl') ?></a><a class="btn btn--ghost" href="vision.html"><?= $v->t('vision') ?></a></div>
  </div>
  <div class="split__img rv"><?= $v->img('photo/4733.jpg') ?></div>
</div></div></section>
<section class="sec sec--surface"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('facts')]) ?>
  <div class="rv" style="max-width:960px;margin:0 auto;background:#fff">
    <table class="spec"><tbody>
      <?php foreach ($facts as [$th, $td]): ?>
      <tr><th scope="row"><?= $th ?></th><td><?= $td ?></td></tr>
      <?php endforeach ?>
    </tbody></table>
  </div>
</div></section>
<section class="sec" id="why"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('why')]) ?>
  <div class="grid g-4"><?= $v->partial('cards/text', ['items' => SiteView::WHY]) ?></div>
</div></section>
<section class="sec sec--tint"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('our_divisions')]) ?>
  <?= $v->partial('cards/divisions') ?>
</div></section>
<?= $v->partial('cta') ?>
