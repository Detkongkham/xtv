<?php
/**
 * ↑ ↓ buttons that move a row one place (POST <action> with dir=up|down). Work without JS.
 * @var string $action
 * @var string $label  what is moved, for screen readers
 * @var bool $first
 * @var bool $last
 */
use App\Core\Csrf;
?>
<div class="order">
  <?php foreach (['up' => ['↑', 'ຍ້າຍຂຶ້ນ', $first], 'down' => ['↓', 'ຍ້າຍລົງ', $last]] as $dir => [$arrow, $text, $off]): ?>
    <form method="post" action="<?= e($action) ?>"><?= Csrf::field() ?><input type="hidden" name="dir" value="<?= $dir ?>"><button class="icon-btn" type="submit" aria-label="<?= e($text . ' ' . $label) ?>"<?= $off ? ' disabled' : '' ?>><?= $arrow ?></button></form>
  <?php endforeach; ?>
</div>
