<?php
/** @var string $error @var string $email @var string $notice */
use App\Core\Csrf;
use App\Core\View;
?><!doctype html>
<html lang="lo"<?= App\Http\Admin\ThemeController::modeAttr() ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>ເຂົ້າສູ່ລະບົບ — XTK Admin</title>
<link rel="icon" href="/assets/img/favicon.png">
<link rel="stylesheet" href="/assets/fonts/fonts.css">
<link rel="stylesheet" href="/admin/assets/admin.css?v=<?= e(substr((string) @filemtime(__DIR__ . '/../../../../public/admin/assets/admin.css'), -6)) ?>">
<?= View::render('admin/partials/theme-css') ?></head>
<body class="auth">
<main class="auth__box">
  <img src="/assets/img/logo.svg" alt="XTK" width="150" height="50">
  <h1>ເຂົ້າສູ່ລະບົບ Admin</h1>
  <?php if ($notice !== ''): ?><p class="flash flash--ok" role="status"><?= e($notice) ?></p><?php endif; ?>
  <?php if ($error !== ''): ?><p class="flash flash--error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" action="/admin/login" class="stack">
    <?= Csrf::field() ?>
    <div class="field">
      <label for="email">ອີເມວ</label>
      <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($email) ?>"<?= $email === '' ? ' autofocus' : '' ?>>
    </div>
    <div class="field">
      <label for="password">ລະຫັດຜ່ານ</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required<?= $email !== '' ? ' autofocus' : '' ?>>
    </div>
    <button class="btn btn--block" type="submit">ເຂົ້າສູ່ລະບົບ</button>
  </form>
  <p class="muted">ລືມລະຫັດຜ່ານ? ກະລຸນາຕິດຕໍ່ຜູ້ດູແລລະບົບ.</p>
</main>
</body>
</html>
