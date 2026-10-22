<?php
/**
 * Page shell. Not compacted: its line breaks are part of the output.
 * PHP drops the newline right after "?>", so lines that end in a value add "\n" themselves.
 *
 * @var App\Publish\SiteView $v
 * @var array $meta  slug, title, desc, overlay, active (raw text, escaped here)
 * @var string $body
 * @var bool $noindex
 */
use App\Core\Lang;

$a = $v->a;
$full = $v->e($meta['slug'] !== 'index' ? $meta['title'] . ' | XTK' : 'XTK — ' . $v->t('co_name'));
$desc = $v->e($meta['desc'] !== '' ? $meta['desc'] : $v->t('co_tag'));
$alts = '';
foreach (Lang::CODES as $l) {
    $alts .= '<link rel="alternate" hreflang="' . Lang::HTML[$l] . '" href="../' . $l . '/' . $meta['slug'] . '.html">';
}
// all fonts are self-hosted; Chinese uses a subset of Noto Sans SC that only zh pages download.
// main.js loads it after the page (meta font-zh): at 134 KB it would otherwise compete with the CSS and delay the
// first paint on slow phones. Until it arrives the system Chinese font is used. No JS: plain @font-face.
$fonts = '<link rel="stylesheet" href="' . $a . '/fonts/fonts.css">';
if ($v->lang === 'zh') {
    $fonts .= "\n" . '<meta name="font-zh" content="' . $a . '/fonts/noto-sans-sc-sub.woff2">' . "\n"
        . "<noscript><style>@font-face{font-family:'Noto Sans SC';font-style:normal;font-weight:400 600;font-display:swap;"
        . "src:url(" . $a . "/fonts/noto-sans-sc-sub.woff2) format('woff2')}</style></noscript>";
}
?>
<!doctype html>
<html lang="<?= Lang::HTML[$v->lang] ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $full ?></title>
<meta name="description" content="<?= $desc ?>">
<meta name="theme-color" content="#2f368e">
<?= $noindex ? '<meta name="robots" content="noindex, nofollow">' . "\n" : '' ?>
<meta property="og:title" content="<?= $full ?>">
<meta property="og:description" content="<?= $desc ?>">
<meta property="og:type" content="website">
<?= $alts . "\n" ?>
<link rel="icon" href="<?= $a ?>/img/logo.svg" type="image/svg+xml">
<link rel="alternate icon" href="<?= $a ?>/img/favicon.png">
<?= $fonts . "\n" ?>
<link rel="stylesheet" href="<?= $a ?>/css/style.css">
</head>
<body>
<?= $v->partial('header', ['slug' => $meta['slug'], 'overlay' => $meta['overlay'], 'active' => $meta['active']]) . "\n" ?>
<main id="main">
<?= $body . "\n" ?>
</main>
<?= $v->partial('footer') . "\n" ?>
<?= $v->preview ? '<p class="preview-bar" role="note">' . $v->t('preview_note') . "</p>\n" : '' ?>
<script src="<?= $a ?>/js/main.js" defer></script>
</body>
</html>
