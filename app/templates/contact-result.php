<?php
/** No-JS result page for /api/contact.php. @var string $lang @var array $t @var array $result @var array $contact */
$body = $result['body'];
$error = $body['error'] ?? null;
$fieldErrors = $body['errors'] ?? [];
$message = match ($error) {
    'rate_limited' => $t['rate_limited'],
    'expired', 'invalid_token' => $t['expired'],
    null => $t['fix'],
    default => $t['server'],
};
$tel = $contact['tel'] ?? '';
$wa = $contact['whatsapp'] ?? '';
?><!doctype html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($t['page_title']) ?> — XTK</title>
<link rel="stylesheet" href="/assets/fonts/fonts.css">
<style>
  :root { --brand-600: #2f368e; --ink: #1a1a1a; --body: #4c5768; --line: #dadce0; --surface: #f5f6f8; --accent-red-text: #c8161d; }
  body { margin: 0; background: var(--surface); color: var(--body); font: 16px/1.8 "Inter", "Noto Sans Lao", "Noto Sans Thai", "Noto Sans SC", system-ui, sans-serif; }
  main { max-width: 640px; margin: 64px auto; padding: 40px; background: #fff; border: 1px solid var(--line); border-radius: 8px; }
  h1 { margin: 0 0 16px; color: var(--brand-600); font-size: 28px; line-height: 1.4; font-weight: 600; }
  ul { padding-left: 20px; } li { color: var(--accent-red-text); }
  li b { color: var(--ink); font-weight: 600; }
  .hint { font-size: 14px; color: #6a6d7c; }
  a { color: var(--brand-600); }
  .btn { display: inline-block; margin-top: 16px; padding: 12px 24px; border-radius: 999px; background: var(--brand-600); color: #fff; text-decoration: none; font-weight: 500; }
  @media (max-width: 767px) { main { margin: 20px; padding: 24px 20px; } }
</style>
</head>
<body>
<main>
  <h1><?= e($t['page_title']) ?></h1>
  <p><?= e($message) ?></p>
  <?php if ($fieldErrors !== []): ?>
    <ul>
      <?php foreach ($fieldErrors as $field => $code): ?>
        <li><b><?= e($t['fields'][$field] ?? $field) ?>:</b> <?= e($t['errors'][$code] ?? $code) ?></li>
      <?php endforeach; ?>
    </ul>
    <p class="hint"><?= e($t['back_hint']) ?></p>
  <?php endif; ?>
  <?php if ($error !== null && $tel !== ''): ?>
    <p><?= e($t['call']) ?>: <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $tel)) ?>"><?= e($tel) ?></a>
    <?php if ($wa !== ''): ?> · <a href="https://api.whatsapp.com/send?phone=<?= e($wa) ?>">WhatsApp</a><?php endif; ?></p>
  <?php endif; ?>
  <a class="btn" href="/<?= e($lang) ?>/contact.html"><?= e($t['back']) ?></a>
</main>
</body>
</html>
