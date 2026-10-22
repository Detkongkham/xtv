<?php
/**
 * @var string $h     heading (HTML)
 * @var string $p     intro (HTML), optional
 * @var bool $left    left-aligned, optional
 */
$p ??= '';
?>
<div class="sec-head<?= !empty($left) ? ' sec-head--left' : '' ?> rv"><h2 class="h2"><?= $h ?></h2><?= $p !== '' ? '<p>' . $p . '</p>' : '' ?></div>
