<?php
/**
 * Vision and mission (vision.html).
 * @var App\Publish\SiteView $v
 */
$v->meta('vision', $v->t('vision'), $v->t('vision_p'), active: 'about');
?>
<?= $v->partial('page-head', ['title' => $v->t('vision'), 'trail' => [[$v->t('about'), 'about.html']]]) ?>
<section class="sec"><div class="wrap" style="max-width:1000px;text-align:center">
  <span class="tag rv"><?= $v->t('vision_h') ?></span>
  <p class="lead rv" style="font-size:28px;line-height:1.7;margin-top:20px"><?= $v->t('vision_p') ?></p>
</div></section>
<section class="sec sec--surface"><div class="wrap"><div class="split">
  <div class="split__img rv"><?= $v->img('photo/4719.jpg') ?></div>
  <div class="rv">
    <div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2"><?= $v->t('mission_h') ?></h2></div>
    <ul class="checks"><li><?= $v->t('mission1') ?></li><li><?= $v->t('mission2') ?></li><li><?= $v->t('mission3') ?></li></ul>
  </div>
</div></div></section>
<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('values_h')]) ?>
  <div class="grid g-4 steps"><?= $v->partial('steps', ['keys' => ['val1', 'val2', 'val3', 'val4']]) ?></div>
</div></section>
<?= $v->partial('cta') ?>
