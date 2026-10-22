<?php
/**
 * One news item (news-<slug>.html). The body is HTML that was cleaned when it was saved (P2-C8).
 * @var App\Publish\SiteView $v
 * @var array $n  news row
 */
$v->meta('news-' . $n['slug'], $v->raw($n, 'title'), $v->raw($n, 'summary'), active: 'news');
$category = ['product' => 'n_cat_prod', 'company' => 'n_cat_co', 'event' => 'n_cat_event'];
$cover = $v->data->media($n['cover_id'] === null ? null : (int) $n['cover_id']);
?>
<section class="phead"><div class="wrap">
  <?= $v->partial('breadcrumb', ['trail' => [[$v->t('news'), 'news.html']], 'last' => $v->tr($n, 'title')]) ?>
  <p style="margin-top:20px"><span class="tag"><?= $v->t($category[$n['category']]) ?></span> <time class="small" datetime="<?= $n['published_on'] ?>" style="margin-left:8px"><?= $v->fdate($n['published_on']) ?></time></p>
  <h1 style="max-width:900px"><?= $v->tr($n, 'title') ?></h1>
</div></section>
<section class="sec sec--tight"><div class="wrap"><div class="prose" style="margin:0 auto">
  <p class="lead" style="font-size:18px"><?= $v->tr($n, 'summary') ?></p>
  <?= $v->raw($n, 'body') ?>
  <?php if ($cover): ?>
  <figure style="margin:32px 0;border:1px solid var(--line)"><?= $v->media($cover, sizes: App\Publish\SiteView::SIZES_WIDE) ?></figure>
  <?php endif ?>
  <div class="btns"><?php if ($n['link_url']): ?><a class="btn" href="<?= $v->e($n['link_url']) ?>"><?= $v->t('view_products') ?></a><?php endif ?><a class="btn btn--ghost" href="news.html"><?= $v->t('back_news') ?></a></div>
</div></div></section>
<?= $v->partial('cta') ?>
