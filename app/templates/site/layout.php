<?php
/**
 * Page shell. Not compacted: its line breaks are part of the output.
 * PHP drops the newline right after "?>", so lines that end in a value add "\n" themselves.
 *
 * @var App\Publish\SiteView $v
 * @var array $meta  slug, title, desc, overlay, active, own (title from /admin/seo), ld (JSON-LD objects); raw text, escaped here
 * @var string $body
 * @var bool $noindex
 */
use App\Core\Lang;

$a = $v->a;
$full = $v->e($meta['slug'] !== 'index' ? $meta['title'] . ' | XTK' : ($meta['own'] ? $meta['title'] : 'XTK — ' . $v->t('co_name')));
$desc = $v->e($meta['desc'] !== '' ? $meta['desc'] : $v->t('co_tag'));
// with a base URL: full URLs, a canonical (not on 404, which is served at any address) and og:url
$href = static fn (string $l): string => $v->e($v->url($meta['slug'], $l) ?? '../' . $l . '/' . $meta['slug'] . '.html');
$alts = '';
foreach (Lang::CODES as $l) {
    $alts .= '<link rel="alternate" hreflang="' . Lang::HTML[$l] . '" href="' . $href($l) . '">';
}
$alts .= '<link rel="alternate" hreflang="x-default" href="' . $href(Lang::X_DEFAULT) . '">';
$canonical = $v->url($meta['slug']) === null || $meta['slug'] === '404' ? ''
    : '<link rel="canonical" href="' . $href($v->lang) . '">' . "\n" . '<meta property="og:url" content="' . $href($v->lang) . '">' . "\n";
// JSON-LD: JSON_HEX_TAG keeps a "</script>" in the text from closing the tag
$ld = '';
foreach ($meta['ld'] as $thing) {
    $ld .= '<script type="application/ld+json">' . json_encode($thing, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_THROW_ON_ERROR) . "</script>\n";
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
// Google Analytics 4 only when the admin set a Measurement ID (/admin/settings); the ID is checked there (G-XXXX)
$ga = $v->preview ? '' : (string) $v->data->setting('analytics_id', '');
$analytics = $ga === '' ? '' : '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $v->e($ga) . '"></script>' . "\n"
    . "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','" . $v->e($ga) . "')</script>\n";
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
<meta property="og:type" content="<?= str_starts_with($meta['slug'], 'news-') ? 'article' : 'website' ?>">
<?= $canonical ?><?= $alts . "\n" ?>
<link rel="icon" href="<?= $a ?>/img/logo.svg" type="image/svg+xml">
<link rel="alternate icon" href="<?= $a ?>/img/favicon.png">
<?= $fonts . "\n" ?>
<link rel="stylesheet" href="<?= $a ?>/css/style.css">
<?= $ld ?><?= $analytics ?></head>
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
