<?php
/** @var array $users @var array $user */
use App\Http\Admin\UserController as UC;
?>
<div class="bar">
  <p><b><?= e(count($users)) ?></b> ຜູ້ໃຊ້</p>
  <a class="btn btn--sm" href="/admin/users/new">+ ເພີ່ມຜູ້ໃຊ້</a>
</div>
<div class="card table-wrap">
  <table class="table">
    <thead><tr><th scope="col">ຊື່</th><th scope="col">ອີເມວ</th><th scope="col">ສິດ</th><th scope="col">ສະຖານະ</th><th scope="col">ເຂົ້າລະບົບຫຼ້າສຸດ</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td data-label="ຊື່"><a href="/admin/users/<?= e($u['id']) ?>"><?= e($u['name']) ?></a><?php if ((int) $u['id'] === (int) $user['id']): ?> <small class="muted">(ທ່ານ)</small><?php endif; ?></td>
          <td data-label="ອີເມວ"><?= e($u['email']) ?></td>
          <td data-label="ສິດ"><?= e(UC::ROLE_NAMES[$u['role']] ?? $u['role']) ?></td>
          <td data-label="ສະຖານະ"><span class="tag tag--<?= (int) $u['is_active'] === 1 ? 'done' : 'spam' ?>"><?= (int) $u['is_active'] === 1 ? 'ໃຊ້ງານ' : 'ປິດ' ?></span></td>
          <td data-label="ເຂົ້າລະບົບຫຼ້າສຸດ"><?= e($u['last_login_at'] ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : '—') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
