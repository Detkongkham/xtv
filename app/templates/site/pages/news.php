<?php
/**
 * News list (news.html).
 * @var App\Publish\SiteView $v
 */
$v->meta('news', $v->t('news'), $v->t('news_lead'), active: 'news');
?>
<?= $v->partial('page-head', ['title' => $v->t('news'), 'lead' => $v->t('news_lead')]) ?>
<section class="sec"><div class="wrap"><?= $v->partial('cards/news') ?></div></section>
<?= $v->partial('cta') ?>
