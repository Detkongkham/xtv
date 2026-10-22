<?php
/**
 * All divisions (products.html).
 * @var App\Publish\SiteView $v
 */
$v->meta('products', $v->t('products'), $v->t('prod_lead'), active: 'products');
?>
<?= $v->partial('page-head', ['title' => $v->t('products'), 'lead' => $v->t('prod_lead')]) ?>
<section class="sec"><div class="wrap"><?= $v->partial('cards/divisions', ['cols' => 3]) ?></div></section>
<?php foreach ($v->data->divisions() as $i => $d): ?>
<?php
$pic = '<div class="split__img rv" style="border:1px solid var(--line);background:#fff">' . $v->media($v->data->media($d['image_id']), $v->raw($d, 'name')) . '</div>';
$text = '<div class="rv"><span class="tag">0' . ($i + 1) . '</span><h2 class="h2" style="margin:16px 0">' . $v->tr($d, 'name') . '</h2><p>' . $v->tr($d, 'body') . '</p>'
    . $v->partial('chips', ['codes' => array_column($v->data->groups($d), 'code')])
    . '<div class="btns" style="margin-top:28px"><a class="btn" href="' . $d['slug'] . '.html">' . $v->t('view_products') . '</a><a class="btn btn--ghost" href="contact.html">' . $v->t('contact') . '</a></div></div>';
?>
<section class="sec sec--tight<?= $i % 2 === 0 ? ' sec--surface' : '' ?>"><div class="wrap"><div class="split"><?= $i % 2 === 0 ? $pic . $text : $text . $pic ?></div></div></section>
<?php endforeach ?>
<section class="sec"><div class="wrap">
  <?= $v->partial('section-head', ['h' => $v->t('commit_h'), 'p' => $v->t('commit_p')]) ?>
  <div class="grid g-3 steps"><?= $v->partial('steps', ['keys' => ['commit1', 'commit2', 'commit3']]) ?></div>
</div></section>
<?= $v->partial('cta') ?>
