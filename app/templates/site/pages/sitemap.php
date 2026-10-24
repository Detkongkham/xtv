<?php
/**
 * Human-readable site map (sitemap.html).
 * @var App\Publish\SiteView $v
 */
$v->meta('sitemap', $v->t('sitemap'));
$boxes = [
    [$v->t('company'), [['index', $v->t('home')], ['about', $v->t('about_profile')], ['vision', $v->t('vision')], ['news', $v->t('news')]]],
    [$v->t('support'), [['services', $v->t('services')], ['downloads', $v->t('downloads')], ['faq', $v->t('faq')],
        ['contact', $v->t('contact')], ['privacy', $v->t('privacy')], ['terms', $v->t('terms')]]],
];
if ($suppliers = $v->data->suppliers()) {
    $boxes[] = [$v->t('sup'), array_merge([['suppliers', $v->t('sup_all')]], array_map(static fn ($s) => ['supplier-' . $s['slug'], $v->e($s['name'])], $suppliers))];
}
foreach ($v->data->divisions() as $d) {
    $links = [[$d['slug'], $v->t('all') . ' — ' . $v->tr($d, 'name')]];
    foreach ($v->data->divisionProducts($d) as $p) {
        if ($p['image_id'] !== null) {
            $links[] = [$v->productPage($p), $v->e($p['name'])];
        }
    }
    $boxes[] = [$v->tr($d, 'name'), $links];
}
?>
<?= $v->partial('page-head', ['title' => $v->t('sitemap')]) ?>
<section class="sec sec--tight"><div class="wrap"><div class="sitemap">
  <?php foreach ($boxes as [$title, $links]): ?>
  <div>
    <h2 class="h4"><?= $title ?></h2>
    <?php foreach ($links as [$page, $label]): ?>
    <a href="<?= $page ?>.html"><?= $label ?></a>
    <?php endforeach ?>
  </div>
  <?php endforeach ?>
</div></div></section>
