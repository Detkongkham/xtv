<?php
/**
 * Title band at the top of inner pages.
 * @var App\Publish\SiteView $v
 * @var string $title  HTML
 * @var string $lead   HTML, optional
 * @var array $trail   breadcrumb [label, href] pairs, optional
 * @var ?string $image picture under assets/img, optional
 */
$lead ??= '';
$trail ??= [];
$image ??= null;
?>
<section class="<?= $image ? 'phead phead--img' : 'phead' ?>">
  <?= $image ? $v->img($image, '', 'plx', false) : '' ?>
  <div class="wrap"><?= $v->partial('breadcrumb', ['trail' => $trail, 'last' => $title]) ?><h1><?= $title ?></h1><?= $lead !== '' ? '<p>' . $lead . '</p>' : '' ?></div>
</section>
