<?php
/** @var array $user @var array $errors */
use App\Core\Csrf;
use App\Http\Admin\UserController as UC;
use App\Repository\UserRepository;

$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
?>
<section class="card narrow">
  <dl class="dl">
    <dt>ຊື່</dt><dd><?= e($user['name']) ?></dd>
    <dt>ອີເມວ</dt><dd><?= e($user['email']) ?></dd>
    <dt>ສິດ</dt><dd><?= e(UC::ROLE_NAMES[$user['role']] ?? $user['role']) ?></dd>
  </dl>
</section>
<form class="card stack narrow" method="post" action="/admin/account">
  <h2 class="h3">ປ່ຽນລະຫັດຜ່ານ</h2>
  <?= Csrf::field() ?>
  <div class="field">
    <label for="current">ລະຫັດຜ່ານປັດຈຸບັນ</label>
    <input id="current" name="current" type="password" autocomplete="current-password" required<?= $inv('current') ?>><?= $err('current') ?>
  </div>
  <div class="field">
    <label for="password">ລະຫັດຜ່ານໃໝ່</label>
    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="<?= UserRepository::MIN_PASSWORD ?>"<?= $inv('password') ?>><?= $err('password') ?>
    <span class="hint">ຢ່າງໜ້ອຍ <?= UserRepository::MIN_PASSWORD ?> ຕົວອັກສອນ.</span>
  </div>
  <div class="field">
    <label for="password_confirm">ຢືນຢັນລະຫັດຜ່ານໃໝ່</label>
    <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
  </div>
  <div class="btns"><button class="btn" type="submit">ປ່ຽນລະຫັດຜ່ານ</button></div>
</form>
