<?php
/** Auto-reply to the customer in their language. @var array $m @var array $t texts @var array $contact */
$tel = $contact['tel'] ?? '';
$mobile = $contact['mobile'] ?? '';
$font = ['lo' => "'Noto Sans Lao','Phetsarath OT',", 'th' => "'Noto Sans Thai',", 'zh' => "'Noto Sans SC','PingFang SC','Microsoft YaHei',"][$m['lang']] ?? '';
?>
<div style="font-family:<?= $font ?>Arial,sans-serif;font-size:15px;line-height:1.8;color:#1a1a1a;max-width:640px">
  <p><?= e(sprintf($t['reply_hello'], $m['name'])) ?></p>
  <p><?= e($t['reply_thanks']) ?></p>
  <h3 style="font-size:16px;color:#2f368e;margin:24px 0 8px"><?= e($t['reply_copy']) ?></h3>
  <p style="margin:0 0 8px"><b><?= e($t['fields']['subject']) ?>:</b> <?= e($t['subjects'][$m['subject']] ?? $m['subject']) ?></p>
  <div style="white-space:pre-wrap;background:#f5f6f8;padding:12px 16px;border-radius:8px"><?= e($m['body']) ?></div>
  <?php if ($tel !== '' || $mobile !== ''): ?>
    <p style="margin-top:24px"><?= e($t['reply_urgent']) ?><br>
      <?php if ($tel !== ''): ?><?= e($t['call']) ?>: <?= e($tel) ?><br><?php endif; ?>
      <?php if ($mobile !== ''): ?>WhatsApp: <?= e($mobile) ?><?php endif; ?>
    </p>
  <?php endif; ?>
  <p style="margin-top:24px"><?= e($t['company']) ?></p>
  <p style="color:#6a6d7c;font-size:13px"><?= e($t['reply_note']) ?></p>
</div>
