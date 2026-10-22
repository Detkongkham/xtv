<?php
/**
 * Supplier logo on a tile; a link when $href is given.
 * @var App\Publish\SiteView $v
 * @var array $s
 * @var int $h        logo height in px
 * @var ?string $href
 * @var string $cls   extra class, optional
 * @var bool $lazy    optional, default true
 */
$c = 'logo-tile' . ((int) $s['logo_on_dark'] ? ' logo-tile--dark' : '') . (!empty($cls) ? ' ' . $cls : '');
$logo = $v->supplierLogo($s, $h, $lazy ?? true);
?>
<?php if (!empty($href)): ?>
<a class="<?= $c ?>" href="<?= $href ?>" aria-label="<?= $v->e($s['name']) ?>"><?= $logo ?></a>
<?php else: ?>
<span class="<?= $c ?>"><?= $logo ?></span>
<?php endif ?>
