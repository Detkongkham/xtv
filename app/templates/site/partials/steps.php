<?php
/**
 * Numbered steps (CSS counts them): heading key + <key>_p.
 * @var App\Publish\SiteView $v
 * @var array $keys
 */
?>
<?php foreach ($keys as $i => $k): ?>
<div class="step rv" style="--i:<?= $i ?>"><h3 class="h4"><?= $v->t($k) ?></h3><p><?= $v->t($k . '_p') ?></p></div>
<?php endforeach ?>
