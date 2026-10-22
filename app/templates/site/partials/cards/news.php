<?php
/**
 * Grid of news cards. An item with a body links to its own page, one without links to its link_url.
 * @var App\Publish\SiteView $v
 */
$category = ['product' => 'n_cat_prod', 'company' => 'n_cat_co', 'event' => 'n_cat_event'];
?>
<div class="grid g-3">
  <?php foreach ($v->data->news() as $i => $n): ?>
  <a class="card card--news rv" style="--i:<?= $i ?>" href="<?= $v->data->hasPage($n) ? 'news-' . $n['slug'] . '.html' : $v->e((string) $n['link_url']) ?>">
    <span class="card__img"><?= $v->media($v->data->media($n['cover_id'] === null ? null : (int) $n['cover_id'])) ?></span>
    <span class="card__body"><span><span class="tag"><?= $v->t($category[$n['category']]) ?></span></span><time datetime="<?= $n['published_on'] ?>"><?= $v->fdate($n['published_on']) ?></time><b class="h4"><?= $v->tr($n, 'title') ?></b><p class="clamp2"><?= $v->tr($n, 'summary') ?></p><span class="card__foot"><?= $v->t('learn_more') ?><span class="circ"><?= $v->ic('arrow') ?></span></span></span>
  </a>
  <?php endforeach ?>
</div>
