<?php
/** Team notification (Lao). @var array $m message row @var string $subjectName @var string $adminUrl */
$langNames = ['lo' => 'ລາວ', 'en' => 'ອັງກິດ', 'th' => 'ໄທ', 'zh' => 'ຈີນ'];
$rows = [
    'ຊື່' => $m['name'],
    'ອົງກອນ' => $m['org'] ?? '—',
    'ເບີໂທ' => $m['phone'],
    'ອີເມວ' => $m['email'],
    'ຫົວຂໍ້' => $subjectName,
    'ພາສາ' => $langNames[$m['lang']] ?? $m['lang'],
    'ເວລາ' => date('d/m/Y H:i', strtotime($m['created_at'])),
    'ໜ້າທີ່ສົ່ງ' => $m['page_url'] ?? '—',
];
?>
<div style="font-family:'Noto Sans Lao','Phetsarath OT',Arial,sans-serif;font-size:15px;line-height:1.7;color:#1a1a1a;max-width:640px">
  <h2 style="color:#2f368e;font-size:20px;margin:0 0 16px">ຂໍ້ຄວາມໃໝ່ຈາກຟອມຕິດຕໍ່ໃນເວັບໄຊ</h2>
  <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%;border:1px solid #dadce0">
    <?php foreach ($rows as $label => $value): ?>
      <tr>
        <td style="border:1px solid #dadce0;background:#f5f6f8;width:120px;font-weight:600"><?= e($label) ?></td>
        <td style="border:1px solid #dadce0"><?= e($value) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <h3 style="font-size:16px;margin:24px 0 8px">ຂໍ້ຄວາມ</h3>
  <div style="white-space:pre-wrap;background:#f5f6f8;padding:12px 16px;border-radius:8px"><?= e($m['body']) ?></div>
  <p style="margin-top:24px">
    <a href="<?= e($adminUrl) ?>" style="display:inline-block;background:#2f368e;color:#ffffff;padding:10px 20px;border-radius:999px;text-decoration:none">ເປີດໃນ Admin</a>
  </p>
  <p style="color:#6a6d7c;font-size:13px">ກົດ Reply ເພື່ອຕອບລູກຄ້າໂດຍກົງ (Reply-To: <?= e($m['email']) ?>).</p>
</div>
