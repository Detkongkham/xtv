<?php
/** @var array $u @var array $errors */
use App\Core\Csrf;
use App\Http\Admin\UserController as UC;
use App\Repository\UserRepository;

$isNew = (int) $u['id'] === 0;
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
?>
<p class="crumb"><a href="/admin/users">← ຜູ້ໃຊ້</a></p>
<form class="card stack narrow" method="post" action="<?= $isNew ? '/admin/users/new' : '/admin/users/' . e($u['id']) ?>">
  <?= Csrf::field() ?>
  <div class="field">
    <label for="name">ຊື່ <em>*</em></label>
    <input id="name" name="name" required maxlength="120" value="<?= e($u['name']) ?>"<?= $inv('name') ?>><?= $err('name') ?>
  </div>
  <div class="field">
    <label for="email">ອີເມວ <em>*</em></label>
    <input id="email" name="email" type="email" required maxlength="190" autocomplete="off" value="<?= e($u['email']) ?>"<?= $inv('email') ?>><?= $err('email') ?>
  </div>
  <div class="field">
    <label for="role">ສິດ <em>*</em></label>
    <select id="role" name="role"<?= $inv('role') ?>>
      <?php foreach (UC::ROLE_NAMES as $k => $label): ?>
        <option value="<?= e($k) ?>"<?= $u['role'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select><?= $err('role') ?>
    <span class="hint">ຜູ້ແກ້ໄຂເນື້ອຫາ ເຂົ້າໜ້າຜູ້ໃຊ້ ແລະ ການຕັ້ງຄ່າບໍ່ໄດ້.</span>
  </div>
  <?php if (!$isNew): ?>
    <label class="check"><input type="checkbox" name="is_active" value="1"<?= $u['is_active'] ? ' checked' : '' ?>><span>ເປີດໃຊ້ບັນຊີ</span></label>
  <?php else: ?>
    <input type="hidden" name="is_active" value="1">
  <?php endif; ?>
  <fieldset class="stack">
    <legend><?= $isNew ? 'ລະຫັດຜ່ານ <em>*</em>' : 'ຕັ້ງລະຫັດຜ່ານໃໝ່ (ປ່ອຍຫວ່າງຖ້າບໍ່ປ່ຽນ)' ?></legend>
    <div class="field">
      <label for="password">ລະຫັດຜ່ານ</label>
      <input id="password" name="password" type="password" autocomplete="new-password" minlength="<?= UserRepository::MIN_PASSWORD ?>"<?= $isNew ? ' required' : '' ?><?= $inv('password') ?>><?= $err('password') ?>
      <span class="hint">ຢ່າງໜ້ອຍ <?= UserRepository::MIN_PASSWORD ?> ຕົວອັກສອນ.</span>
    </div>
    <div class="field">
      <label for="password_confirm">ຢືນຢັນລະຫັດຜ່ານ</label>
      <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password"<?= $isNew ? ' required' : '' ?>>
    </div>
  </fieldset>
  <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມຜູ້ໃຊ້' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/users">ຍົກເລີກ</a></div>
</form>
