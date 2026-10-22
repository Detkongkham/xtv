<?php
/**
 * @var App\Publish\SiteView $v
 * @var array $trail  [label, href] pairs between Home and this page
 * @var string $last  this page (HTML)
 */
$parts = ['<a href="index.html">' . $v->t('home') . '</a>'];
foreach ($trail as [$label, $href]) {
    $parts[] = '<a href="' . $href . '">' . $label . '</a>';
}
$parts[] = '<span aria-current="page">' . $last . '</span>';
?>
<nav class="crumb" aria-label="Breadcrumb"><?= implode('<span>&gt;</span>', $parts) ?></nav>
