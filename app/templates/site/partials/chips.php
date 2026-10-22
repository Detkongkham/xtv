<?php
/**
 * Tags with the names of product groups (hidden or missing groups are left out).
 * @var App\Publish\SiteView $v
 * @var array $codes  group codes
 */
?>
<div class="chips"><?php foreach ($codes as $code): ?><?php if ($g = $v->data->group($code)): ?><span class="tag"><?= $v->tr($g, 'name') ?></span><?php endif ?><?php endforeach ?></div>
