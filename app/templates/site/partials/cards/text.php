<?php
/**
 * Icon cards with a heading and a sentence: services and "why choose us".
 * @var App\Publish\SiteView $v
 * @var array $items  [text key, icon] pairs; the sentence is <key>_p
 */
?>
<?php foreach ($items as $i => [$k, $icon]): ?>
<div class="card card--text rv" style="--i:<?= $i ?>"><div class="card__body"><span class="ico"><?= $v->ic($icon) ?></span><h3 class="h4"><?= $v->t($k) ?></h3><p><?= $v->t($k . '_p') ?></p></div></div>
<?php endforeach ?>
