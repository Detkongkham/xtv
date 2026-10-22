<?php
/**
 * Solutions by type of facility (solutions.html).
 * @var App\Publish\SiteView $v
 */
use App\Publish\SiteView;

$v->meta('solutions', $v->t('solutions'), $v->t('sol_lead'), active: 'solutions');
?>
<?= $v->partial('page-head', ['title' => $v->t('solutions'), 'lead' => $v->t('sol_lead'), 'image' => 'photo/4779.jpg']) ?>
<?php foreach (SiteView::SOLUTIONS as $i => [$k, $pic, $chips, $link]): ?>
<?php
$text = '<div class="rv"><span class="tag">0' . ($i + 1) . '</span><h2 class="h2" style="margin:16px 0">' . $v->t('sol_' . $k) . '</h2><p>' . $v->t('sol_' . $k . '_p') . '</p>'
    . '<h3 class="h4" style="margin-top:28px">' . $v->t('sol_rec') . '</h3>' . $v->partial('chips', ['codes' => $chips])
    . '<div class="btns" style="margin-top:28px"><a class="btn" href="' . ($v->data->division($link) ? $link : 'products') . '.html">' . $v->t('view_products') . '</a><a class="btn btn--ghost" href="contact.html">' . $v->t('contact') . '</a></div></div>';
$img = '<div class="split__img rv">' . $v->img($pic) . '</div>';
?>
<section class="sec<?= $i % 2 ? ' sec--surface' : '' ?>" id="<?= $k ?>"><div class="wrap"><div class="split"><?= $i % 2 ? $img . $text : $text . $img ?></div></div></section>
<?php endforeach ?>
<?= $v->partial('cta') ?>
