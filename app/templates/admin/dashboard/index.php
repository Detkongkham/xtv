<?php
/**
 * @var int $days @var array $counts @var array $latest @var int $mailErrors @var int $withoutSupplier
 * @var array $publish see Controller::publishState() @var array $history @var array $gaps see TranslationGapRepository::all()
 * @var ?array $backups see Backup::latest(), admins only
 * @var array $daily @var array $period @var array $before @var array $publishes @var ?string $oldestNew @var array $content
 *      see DashboardRepository
 * @var array $user
 */
use App\Core\Csrf;
use App\Http\Admin\DashboardController as DC;
use App\Http\Admin\MessageController as MC;
use App\Http\Admin\PublishController as PC;
use App\Repository\TranslationGapRepository as TG;

// lucide icon paths, drawn by $i()
$icons = [
    'calendar' => 'M8 2v4M16 2v4M3 6a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2zM3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01',
    'refresh' => 'M3 12a9 9 0 019-9 9.75 9.75 0 016.74 2.74L21 8M21 3v5h-5M21 12a9 9 0 01-9 9 9.75 9.75 0 01-6.74-2.74L3 16M8 16H3v5',
    'bell' => 'M10.27 21a2 2 0 003.46 0M22 8c0-2.3-.8-4.3-2-6M3.26 15.33A1 1 0 004 17h16a1 1 0 00.74-1.67C19.41 13.96 18 12.5 18 8A6 6 0 006 8c0 4.5-1.41 5.96-2.74 7.33M4 2C2.8 3.7 2 5.7 2 8',
    'arrow' => 'M5 12h14M12 5l7 7-7 7',
    'up' => 'M7 7h10v10M7 17L17 7',
    'down' => 'M7 7l10 10M17 7v10H7',
    'mail' => 'M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM22 7l-10 6L2 7',
    'mail-x' => 'M22 13V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2h9M22 7l-10 6L2 7M17 17l4 4M21 17l-4 4',
    'inbox' => 'M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'languages' => 'M5 8l6 6M4 14l6-6 2-3M2 5h12M7 2h1M22 22l-5-10-5 10M14 18h6',
    'package' => 'M11 21.73a2 2 0 002 0l7-4A2 2 0 0021 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73zM12 22V12M3.29 7L12 12l8.71-5M7.5 4.27l9 5.15',
    'database' => 'M3 5a9 3 0 1018 0A9 3 0 103 5M3 5v14a9 3 0 0018 0V5M3 12a9 3 0 0018 0',
    'file' => 'M15 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7zM14 2v4a2 2 0 002 2h4M10 9H8M16 13H8M16 17H8',
    'clock' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM12 6v6l4 2',
    'check' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM9 12l2 2 4-4',
    'globe' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20',
    'trend' => 'M22 7l-8.5 8.5-5-5L2 17M16 7h6v6',
    'shield' => 'M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 01-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 011-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 011.52 0C14.51 3.81 17 5 19 5a1 1 0 011 1zM9 12l2 2 4-4',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.99-3.99L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'send' => 'M14.54 21.69a.5.5 0 00.94-.03l6.5-19a.5.5 0 00-.64-.64l-19 6.5a.5.5 0 00-.03.94l7.93 3.18a2 2 0 011.11 1.11zM21.85 2.15L10.91 13.09',
    'history' => 'M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8M3 3v5h5M12 7v5l4 2',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int|float $v): string => number_format((float) $v);
$pct = static fn (int $part, int $whole): int => $whole > 0 ? (int) round($part / $whole * 100) : 0;
// change against the previous period; null = nothing before to compare with
$delta = static fn (int $now, int $was): ?int => $was === 0 ? ($now === 0 ? 0 : null) : (int) round(($now - $was) / $was * 100);
$deltaHtml = static function (?int $d, string $note = '', bool $upIsBad = false) use ($i): string {
    $tone = $d === null || $d === 0 ? 'muted' : (($d > 0) !== $upIsBad ? 'up' : 'down');
    $arrow = $d === null || $d === 0 ? '' : $i($d > 0 ? 'up' : 'down');
    $text = $d === null ? 'ໃໝ່' : ($d > 0 ? '+' : '') . $d . '%';
    return '<p class="kpi__delta kpi__delta--' . $tone . '">' . $arrow . e($text) . ($note !== '' ? '<span>' . e($note) . '</span>' : '') . '</p>';
};
$ago = static function (string $at): string {
    $m = max(0, (int) floor((time() - strtotime($at)) / 60));
    return $m < 60 ? $m . ' ນາທີ' : ($m < 1440 ? floor($m / 60) . ' ຊົ່ວໂມງ' : floor($m / 1440) . ' ມື້');
};
// 100x30 sparkline: filled area + line + end dot
$spark = static function (array $values): string {
    $max = max(1, ...$values);
    $step = 100 / max(1, count($values) - 1);
    $pts = [];
    foreach (array_values($values) as $k => $v) {
        $pts[] = round($k * $step, 2) . ',' . round(28 - $v / $max * 26, 2);
    }
    $last = explode(',', end($pts));
    return '<svg class="spark" viewBox="0 0 100 30" preserveAspectRatio="none" aria-hidden="true">'
        . '<polygon points="0,30 ' . implode(' ', $pts) . ' 100,30"/>'
        . '<polyline points="' . implode(' ', $pts) . '" vector-effect="non-scaling-stroke"/>'
        . '<circle cx="' . $last[0] . '" cy="' . $last[1] . '" r="2" vector-effect="non-scaling-stroke"/></svg>';
};
$rows = static function (array $items): string {
    $h = '<ul class="kpi__rows">';
    foreach ($items as $label => $value) {
        $h .= '<li><span>' . e($label) . '</span><b>' . e($value) . '</b></li>';
    }
    return $h . '</ul>';
};
// bars are SVG: the admin CSP blocks style attributes
$meter = static fn (int $value, string $left, string $right): string => '<div class="kpi__foot"><svg class="meter" aria-hidden="true"><rect class="meter__bg" width="100%" height="100%" rx="2"/><rect width="' . min(100, max(0, $value)) . '%" height="100%" rx="2"/></svg>'
    . '<p class="kpi__split"><span>' . e($left) . '</span><b>' . e($right) . '</b></p></div>';
// a KPI card: link when $href is set
$kpi = static function (string $tone, string $label, string $icon, string $value, string $body, ?string $href) use ($i): string {
    $tag = $href === null ? 'div' : 'a';
    return '<' . $tag . ' class="kpi kpi--' . $tone . '"' . ($href !== null ? ' href="' . e($href) . '"' : '') . '>'
        . '<div class="kpi__top"><p>' . e($label) . '</p><span class="kpi__ico">' . $i($icon) . '</span></div>'
        . '<p class="kpi__value">' . e($value) . '</p>' . $body
        . ($href !== null ? $i('arrow', 'kpi__go') : '') . '</' . $tag . '>';
};

// days: first half = previous period, second half = this one
$dayKeys = array_keys($daily);
$cur = array_slice($daily, $days, null, true);
$prevDays = array_slice($daily, 0, $days, true);
$series = static fn (array $d, string $k): array => array_column(array_values($d), $k);
$today = $daily[end($dayKeys)];
$yesterday = $daily[$dayKeys[count($dayKeys) - 2]];
$editsNow = array_sum($series($cur, 'edits'));
$editsWas = array_sum($series($prevDays, 'edits'));
$open = $counts['new'] + $counts['in_progress'];
$live = $publish['live'];
$gapCount = array_map(static fn (string $l): int => count(array_filter($gaps, static fn (array $g): bool => isset($g['missing'][$l]))), array_keys(TG::LANGS));
$backupLate = $backups !== null && ($backups['db'] === null || $backups['db']['time'] < time() - 2 * 86400);
$hour = (int) date('G');
$greet = $hour < 11 ? 'ສະບາຍດີຕອນເຊົ້າ' : ($hour < 17 ? 'ສະບາຍດີຕອນບ່າຍ' : 'ສະບາຍດີຕອນແລງ');
$subjectColors = ['general' => 1, 'sales' => 2, 'service' => 3, 'other' => 4];
$fmtDay = static fn (string $d): string => date('d/m', strtotime($d));

// "needs action" chips: [tone, href, icon, text, extra]
$todo = [];
if ($mailErrors > 0) {
    $todo[] = ['danger', '/admin/messages', 'mail-x', 'ສົ່ງອີເມວແຈ້ງເຕືອນບໍ່ສຳເລັດ ' . $n($mailErrors) . ' ຂໍ້ຄວາມ', null];
}
if ($backupLate) {
    $todo[] = ['danger', '#backup-h', 'database', 'ບໍ່ມີ backup ໃນ 2 ມື້ຫຼ້າສຸດ', null];
}
if ($counts['new'] > 0) {
    $todo[] = ['warn', '/admin/messages?status=new', 'inbox', 'ຂໍ້ຄວາມໃໝ່ລໍຖ້າຕອບ ' . $n($counts['new']) . ' ຂໍ້ຄວາມ', $oldestNew !== null ? $ago($oldestNew) : null];
}
if ($publish['reason'] !== null) {
    $todo[] = ['warn', '/admin/publish', 'upload', 'ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່', null];
}
if ($counts['in_progress'] > 0) {
    $todo[] = ['info', '/admin/messages?status=in_progress', 'clock', 'ກຳລັງດຳເນີນການ ' . $n($counts['in_progress']) . ' ຂໍ້ຄວາມ', null];
}
if ($gaps !== []) {
    $todo[] = ['info', '#gaps-h', 'languages', 'ຄຳແປທີ່ຍັງຂາດ ' . $n(count($gaps)) . ' ລາຍການ', null];
}
if ($withoutSupplier > 0) {
    $todo[] = ['info', '/admin/products?show=nosupplier', 'package', 'ສິນຄ້າບໍ່ມີຜູ້ສະໜອງ ' . $n($withoutSupplier) . ' ລາຍການ', null];
}
if ($content['drafts'] > 0) {
    $todo[] = ['info', '/admin/news?status=draft', 'file', 'ຂ່າວສະບັບຮ່າງ ' . $n($content['drafts']) . ' ຂ່າວ', null];
}

$pendingText = [
    'never' => 'ຍັງບໍ່ເຄີຍເຜີຍແຜ່ຈາກລະບົບນີ້.',
    'rolled_back' => 'ເວັບທີ່ອອນລາຍເປັນຊຸດເກົ່າ (ກັບຄືນຊຸດກ່ອນ). ເຜີຍແຜ່ເພື່ອໃຊ້ຂໍ້ມູນປັດຈຸບັນ.',
    'changed' => 'ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່. ໜ້າເວັບຍັງບໍ່ປ່ຽນຈົນກວ່າຈະກົດເຜີຍແຜ່.',
];
$tagClass = ['success' => 'done', 'failed' => 'error', 'rolled_back' => 'in_progress', 'running' => 'new'];
$gapRow = static function (array $g): string {
    $parts = [];
    foreach ($g['missing'] as $l => $fields) {
        $parts[] = TG::LANGS[$l] . ': ' . implode(', ', $fields);
    }
    return '<li><a href="' . e($g['url']) . '"><b>' . e($g['name']) . '</b><small>' . e($g['type'] . ' · ' . implode(' · ', $parts)) . '</small></a></li>';
};
$panelHead = static fn (string $icon, string $id, string $title, string $sub, string $link = '') => '<header class="panel__head"><div class="panel__title"><span class="panel__ico">' . $i($icon) . '</span><div>'
    . '<h2 id="' . e($id) . '">' . e($title) . '</h2>' . ($sub !== '' ? '<p>' . e($sub) . '</p>' : '') . '</div></div>' . $link . '</header>';

// trend chart: 600x180 drawing area, three series
$chartSeries = ['messages' => ['ຂໍ້ຄວາມ', 1], 'edits' => ['ການແກ້ໄຂເນື້ອຫາ', 2], 'spam' => ['Spam', 5]];
$chartMax = max(1, ...$series($cur, 'messages'), ...$series($cur, 'edits'), ...$series($cur, 'spam'));
$chartMax = (int) (ceil($chartMax / 4) * 4);
$cx = static fn (int $k): float => round($days > 1 ? $k * 600 / ($days - 1) : 300, 2);
$cy = static fn (int $v): float => round(176 - $v / $chartMax * 168, 2);
?>
<div class="dash">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <p class="dash-head__hi"><?= e($greet) ?>, <?= e($user['name']) ?></p>
        <h1>ໜ້າຫຼັກ</h1>
        <p class="dash-head__meta">
          <span><?= $i('calendar') ?><span class="num"><?= e(date('d/m/Y · H:i')) ?></span></span>
          <span aria-hidden="true">·</span><span>ພາບລວມເວັບໄຊ ແລະ ກ່ອງຂໍ້ຄວາມມື້ນີ້</span>
        </p>
      </div>
      <div class="dash-head__tools">
        <nav class="seg" aria-label="ຊ່ວງເວລາວິເຄາະ">
          <?php foreach (DC::PERIODS as $p): ?>
            <a href="/admin?days=<?= e($p) ?>"<?= $p === $days ? ' aria-current="true"' : '' ?>><?= e($p) ?> ມື້</a>
          <?php endforeach; ?>
        </nav>
        <a class="btn-soft" href="/admin?days=<?= e($days) ?>" title="ອັບເດດອັດຕະໂນມັດທຸກນາທີ" data-refresh><?= $i('refresh') ?><span>ອັບເດດ ຕອນນີ້</span></a>
      </div>
    </div>
  </header>

  <?php if ($todo !== []): ?>
  <section class="dash-sec" aria-labelledby="dash-attention">
    <h2 class="dash-h2" id="dash-attention"><?= $i('bell', 'ico--warn') ?>ຕ້ອງດຳເນີນການ</h2>
    <nav aria-label="ຕ້ອງດຳເນີນການ">
      <ul class="chips">
        <?php foreach ($todo as [$tone, $href, $icon, $text, $extra]): ?>
          <li><a class="chip chip--<?= e($tone) ?>" href="<?= e($href) ?>">
            <span class="chip__ico"><?= $i($icon) ?><?php if ($tone === 'danger'): ?><i aria-hidden="true"></i><?php endif; ?></span>
            <span class="chip__text"><?= e($text) ?></span>
            <?php if ($extra !== null): ?><span class="chip__extra" title="ລໍຖ້າດົນສຸດ"><?= e($extra) ?></span><?php endif; ?>
            <?= $i('arrow', 'chip__go') ?>
          </a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </section>
  <?php endif; ?>

  <div class="kpi-top">
    <a class="hero-kpi" href="/admin/messages">
      <div class="hero-kpi__blob" aria-hidden="true"></div>
      <?= $i('arrow', 'kpi__go') ?>
      <div class="hero-kpi__top">
        <div>
          <p class="hero-kpi__label">ຂໍ້ຄວາມມື້ນີ້</p>
          <p class="hero-kpi__value num"><?= e($n($today['messages'])) ?> <small>ຂໍ້ຄວາມ</small></p>
          <?php $d = $delta($today['messages'], $yesterday['messages']); ?>
          <p class="hero-kpi__delta num"><?= e($d === null ? 'ໃໝ່' : ($d > 0 ? '+' : '') . $d . '%') ?> · ທຽບກັບມື້ວານ (<?= e($n($yesterday['messages'])) ?>)</p>
        </div>
        <span class="hero-kpi__ico"><?= $i('mail') ?></span>
      </div>
      <div class="hero-kpi__body">
        <p class="hero-kpi__label">ແບ່ງຕາມຫົວຂໍ້ · <?= e($days) ?> ມື້ຫຼ້າສຸດ</p>
        <svg class="stackbar" aria-hidden="true"><rect class="stackbar__bg" width="100%" height="100%" rx="4"/>
          <?php $x = 0.0; foreach ($period['subjects'] as $s => $c): if ($c > 0): $w = $c / $period['total'] * 100; ?><rect class="c-<?= e($subjectColors[$s]) ?>" x="<?= e(round($x, 2)) ?>%" width="<?= e(round($w, 2)) ?>%" height="100%"/><?php $x += $w; endif; endforeach; ?>
        </svg>
        <ul class="hero-kpi__legend">
          <?php foreach ($period['subjects'] as $s => $c): ?>
            <li><span><i class="dot c-<?= e($subjectColors[$s]) ?>" aria-hidden="true"></i><?= e(MC::SUBJECT_NAMES[$s] ?? $s) ?></span><b class="num"><?= e($pct($c, $period['total'])) ?>%</b></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </a>
    <div class="kpi-top__grid">
      <?= $kpi('primary', 'ລໍຖ້າຕອບ', 'inbox', $n($open), $rows([
          'ຂໍ້ຄວາມໃໝ່' => $n($counts['new']),
          'ກຳລັງດຳເນີນການ' => $n($counts['in_progress']),
          'ລໍຖ້າດົນສຸດ' => $oldestNew !== null ? $ago($oldestNew) : '—',
          'ອີເມວບໍ່ສຳເລັດ' => $n($mailErrors),
      ]), '/admin/messages?status=new') ?>
      <?= $kpi('info', 'ສະຖານະເຜີຍແຜ່', 'upload', $publish['reason'] === null ? 'ເປັນປັດຈຸບັນ' : 'ລໍຖ້າເຜີຍແຜ່', $rows([
          'ເຜີຍແຜ່ລ່າສຸດ' => $live ? date('d/m H:i', strtotime($live['started_at'])) : '—',
          'ໂດຍ' => $live ? ($live['user_name'] ?? 'CLI') : '—',
          'ແກ້ໄຂລ່າສຸດ' => $publish['changedAt'] ? date('d/m H:i', strtotime($publish['changedAt'])) : '—',
      ]) . $meter($pct($publishes['success'], $publishes['success'] + $publishes['failed']), 'ສຳເລັດ ' . $days . ' ມື້', $publishes['success'] . ' / ' . ($publishes['success'] + $publishes['failed'])), '/admin/publish') ?>
      <?= $kpi('success', 'ເນື້ອຫາໃນເວັບ', 'globe', $n($content['products']) . ' ສິນຄ້າ', $rows([
          'ຂ່າວທີ່ເຜີຍແຜ່' => $n($content['news']),
          'ໄຟລ໌ດາວໂຫຼດ' => $n($content['downloads']),
          'FAQ' => $n($content['faqs']),
      ]) . $meter($pct($content['products'] - $withoutSupplier, $content['products']), 'ມີຜູ້ສະໜອງ', $n(max(0, $content['products'] - $withoutSupplier)) . ' / ' . $n($content['products'])), '/admin/products') ?>
    </div>
  </div>

  <section class="dash-sec" aria-labelledby="dash-period">
    <div class="dash-sec__head">
      <h2 class="dash-h2" id="dash-period"><?= $i('trend', 'ico--primary') ?>ຜົນງານ · <?= e($days) ?> ມື້ຫຼ້າສຸດ</h2>
      <p>ທຽບກັບ <?= e($days) ?> ມື້ກ່ອນໜ້າ</p>
    </div>
    <div class="kpi-grid">
      <?= $kpi('primary', 'ຂໍ້ຄວາມທີ່ໄດ້ຮັບ', 'mail', $n($period['total']), $deltaHtml($delta($period['total'], $before['total']))
          . '<div class="kpi__foot">' . $spark($series($cur, 'messages')) . '<p class="kpi__note">ສະເລ່ຍ ' . e(round($period['total'] / $days, 1)) . ' / ມື້</p></div>', '/admin/messages') ?>
      <?= $kpi('info', 'ອັດຕາຕອບກັບ', 'send', $pct($period['answered'], $period['total']) . '%', $meter($pct($period['answered'], $period['total']), 'ສຳເລັດ ' . $n($period['done']) . ' · ກຳລັງເຮັດ ' . $n($period['answered'] - $period['done']), $n($period['answered']) . ' / ' . $n($period['total'])), '/admin/messages') ?>
      <?= $kpi('primary', 'Spam ທີ່ກັ່ນອອກ', 'shield', $n($period['spam']), $deltaHtml($delta($period['spam'], $before['spam']), '', true)
          . '<div class="kpi__foot"><p class="kpi__note">ຊ່ວງກ່ອນ ' . e($n($before['spam'])) . '</p></div>', '/admin/messages?status=spam') ?>
      <?= $kpi('success', 'ອີເມວແຈ້ງເຕືອນ', 'check', $period['total'] > 0 ? $pct($period['total'] - $period['mailErrors'], $period['total']) . '%' : '—', $rows([
          'ສົ່ງສຳເລັດ' => $n($period['total'] - $period['mailErrors']),
          'ບໍ່ສຳເລັດ' => $n($period['mailErrors']),
      ]), '/admin/messages') ?>
      <?= $kpi('info', 'ການແກ້ໄຂເນື້ອຫາ', 'pencil', $n($editsNow), $deltaHtml($delta($editsNow, $editsWas))
          . '<div class="kpi__foot">' . $spark($series($cur, 'edits')) . '<p class="kpi__note">ທຸກການເພີ່ມ, ແກ້ໄຂ, ລຶບ ໃນລະບົບ</p></div>', ($user['role'] ?? '') === 'admin' ? '/admin/activity' : null) ?>
      <?= $kpi('primary', 'ການເຜີຍແຜ່', 'upload', $n($publishes['success']), $rows([
          'ສຳເລັດ' => $n($publishes['success']),
          'ລົ້ມເຫຼວ' => $n($publishes['failed']),
      ]), '/admin/publish') ?>
      <?= $kpi('success', 'ຄຳແປທີ່ຍັງຂາດ', 'languages', $gaps === [] ? 'ຄົບ' : $n(count($gaps)), '<div class="kpi__foot"><p class="kpi__note">ໄທ ' . e($n($gapCount[0])) . ' · ຈີນ ' . e($n($gapCount[1])) . ' ລາຍການ</p></div>', null) ?>
      <?php
      $topLang = array_key_first($period['langs']);
      echo $kpi('info', 'ພາສາຂອງຜູ້ຕິດຕໍ່', 'globe', $topLang === null ? '—' : (MC::LANG_NAMES[$topLang] ?? $topLang), $topLang === null
          ? '<div class="kpi__foot"><p class="kpi__note">ຍັງບໍ່ມີຂໍ້ຄວາມໃນຊ່ວງນີ້</p></div>'
          : $meter($pct($period['langs'][$topLang], $period['total']), implode(' · ', array_map(static fn ($l, $c) => (MC::LANG_NAMES[$l] ?? $l) . ' ' . $c, array_keys($period['langs']), $period['langs'])), $pct($period['langs'][$topLang], $period['total']) . '%'), '/admin/messages');
      ?>
    </div>
  </section>

  <div class="dash-cols">
    <section class="panel panel--wide" aria-labelledby="trend-h">
      <?= $panelHead('trend', 'trend-h', 'ແນວໂນ້ມ (' . $days . ' ມື້)', $fmtDay(array_key_first($cur)) . '/' . date('Y', strtotime(array_key_first($cur))) . ' – ' . date('d/m/Y')) ?>
      <div class="trend">
        <div class="trend__sum">
          <div>
            <p class="trend__total num"><?= e($n($period['total'])) ?> <small>ຂໍ້ຄວາມ</small></p>
            <?php $d = $delta($period['total'], $before['total']); ?>
            <p class="trend__meta"><span class="kpi__delta kpi__delta--<?= $d === null || $d === 0 ? 'muted' : ($d > 0 ? 'up' : 'down') ?>"><?= $d ? $i($d > 0 ? 'up' : 'down') : '' ?><?= e($d === null ? 'ໃໝ່' : ($d > 0 ? '+' : '') . $d . '%') ?></span>
              <span aria-hidden="true">·</span><span>ທຽບກັບ <?= e($days) ?> ມື້ກ່ອນໜ້າ</span><span aria-hidden="true">·</span><span class="num">ສະເລ່ຍ <?= e(round($period['total'] / $days, 1)) ?> / ມື້</span></p>
          </div>
          <div class="trend__toggles" data-chart-toggles>
            <?php foreach ($chartSeries as $key => [$label, $c]): ?>
              <button type="button" aria-pressed="true" data-series="<?= e($key) ?>" class="c-<?= e($c) ?>"><i aria-hidden="true"></i><?= e($label) ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="chart" data-chart>
          <div class="chart__y" aria-hidden="true"><?php for ($g = 4; $g >= 0; $g--): ?><span><?= e($n($chartMax * $g / 4)) ?></span><?php endfor; ?></div>
          <svg class="chart__svg" viewBox="0 0 600 180" preserveAspectRatio="none" role="img" aria-label="ຈຳນວນຂໍ້ຄວາມ, ການແກ້ໄຂ ແລະ spam ຕໍ່ມື້">
            <?php for ($g = 0; $g <= 4; $g++): ?><line class="chart__grid" x1="0" x2="600" y1="<?= e(8 + $g * 42) ?>" y2="<?= e(8 + $g * 42) ?>" vector-effect="non-scaling-stroke"/><?php endfor; ?>
            <?php foreach ($chartSeries as $key => [$label, $c]):
                $pts = [];
                foreach ($series($cur, $key) as $k => $v) {
                    $pts[] = $cx($k) . ',' . $cy($v);
                }
            ?>
              <g data-line="<?= e($key) ?>" class="c-<?= e($c) ?>">
                <?php if ($key === 'messages'): ?><polygon class="chart__area" points="0,176 <?= e(implode(' ', $pts)) ?> 600,176"/><?php endif; ?>
                <polyline class="chart__line" points="<?= e(implode(' ', $pts)) ?>" vector-effect="non-scaling-stroke"/>
              </g>
            <?php endforeach; ?>
            <?php $k = 0; foreach ($cur as $date => $v): ?>
              <rect class="chart__hit" x="<?= e(max(0, $cx($k) - 300 / max(1, $days - 1))) ?>" y="0" width="<?= e(600 / max(1, $days - 1)) ?>" height="180"><title><?= e($fmtDay($date) . ' — ຂໍ້ຄວາມ ' . $v['messages'] . ' · ແກ້ໄຂ ' . $v['edits'] . ' · spam ' . $v['spam']) ?></title></rect>
            <?php $k++; endforeach; ?>
          </svg>
          <div class="chart__x" aria-hidden="true">
            <?php $k = 0; $every = (int) ceil(($days - 1) / 4); /* about five dates, so they fit at phone width */ foreach ($cur as $date => $v): ?>
              <span><?= ($k % $every === 0 && $days - 1 - $k >= $every) || $k === $days - 1 ? e($fmtDay($date)) : '' ?></span>
            <?php $k++; endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="panel" aria-labelledby="latest-h">
      <?= $panelHead('inbox', 'latest-h', 'ຂໍ້ຄວາມໃໝ່ຫຼ້າສຸດ', $counts['new'] > 0 ? 'ຍັງບໍ່ໄດ້ຕອບ ' . $n($counts['new']) . ' ຂໍ້ຄວາມ' : 'ຕອບຄົບແລ້ວ', '<a class="panel__link" href="/admin/messages?status=new">ທັງໝົດ' . $i('arrow') . '</a>') ?>
      <?php if ($latest === []): ?>
        <p class="panel__empty"><?= $i('check') ?>ບໍ່ມີຂໍ້ຄວາມໃໝ່.</p>
      <?php else: ?>
        <ul class="feed">
          <?php foreach ($latest as $m): ?>
            <li><a href="/admin/messages/<?= e($m['id']) ?>">
              <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr(trim($m['name']), 0, 1))) ?></span>
              <span class="feed__text"><b><?= e($m['name']) ?><?php if ($m['org']): ?> · <?= e($m['org']) ?><?php endif; ?></b>
                <small><?= e(MC::SUBJECT_NAMES[$m['subject']] ?? $m['subject']) ?> · <?= e(MC::LANG_NAMES[$m['lang']] ?? $m['lang']) ?></small></span>
              <span class="feed__time"><?= e($ago($m['created_at'])) ?><?php if ($m['mail_error']): ?><span class="tag tag--error">ອີເມວບໍ່ສຳເລັດ</span><?php endif; ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>

  <div class="dash-cols">
    <section class="panel panel--wide<?= $publish['reason'] ? ' panel--warn' : '' ?>" aria-labelledby="publish-h">
      <?= $panelHead('upload', 'publish-h', 'ສະຖານະເຜີຍແຜ່', $live ? 'ເຜີຍແຜ່ລ່າສຸດ ' . date('d/m/Y H:i', strtotime($live['started_at'])) . ' ໂດຍ ' . ($live['user_name'] ?? 'CLI') : 'ຍັງບໍ່ເຄີຍເຜີຍແຜ່', '<a class="panel__link" href="/admin/publish">ປະຫວັດ ແລະ ກັບຄືນ' . $i('arrow') . '</a>') ?>
      <div class="publish-row">
        <?php if ($publish['reason']): ?>
          <p class="warn" role="status"><?= e($pendingText[$publish['reason']]) ?></p>
          <form method="post" action="/admin/publish" data-busy="ກຳລັງເຜີຍແຜ່…">
            <?= Csrf::field() ?>
            <button class="btn btn--sm" type="submit"><?= $i('upload') ?>ເຜີຍແຜ່ດຽວນີ້</button>
          </form>
        <?php else: ?>
          <p><span class="tag tag--done">ເປັນປັດຈຸບັນ</span> ໜ້າເວັບກົງກັບຂໍ້ມູນໃນລະບົບ.</p>
        <?php endif; ?>
      </div>
      <?php if ($history !== []): ?>
        <ul class="list">
          <?php foreach ($history as $h): ?>
            <li><span><?= e(date('d/m/Y H:i', strtotime($h['started_at']))) ?> · <?= e($h['user_name'] ?? 'CLI') ?><?= $h['pages_written'] ? ' · ' . e($h['pages_written']) . ' ໜ້າ' : '' ?></span>
              <span class="tag tag--<?= e($tagClass[$h['status']] ?? 'new') ?>"><?= e(PC::STATUS_NAMES[$h['status']] ?? $h['status']) ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <?php if ($backups !== null):
        $when = static fn (?array $b): string => $b === null ? 'ຍັງບໍ່ມີ' : date('d/m/Y H:i', $b['time'])
            . ($b['bytes'] < 1048576 ? ' (' . round($b['bytes'] / 1024) . ' KB)' : ' (' . round($b['bytes'] / 1048576, 1) . ' MB)');
    ?>
    <section class="panel<?= $backupLate ? ' panel--danger' : '' ?>" aria-labelledby="backup-h">
      <?= $panelHead('database', 'backup-h', 'Backup', 'ເກັບໃນ server ທີ່ app/storage/backups/') ?>
      <?php if ($backupLate): ?><p class="warn" role="status">ບໍ່ມີ backup ຖານຂໍ້ມູນໃນ 2 ມື້ຫຼ້າສຸດ. ກວດ cron ຂອງ <code>bin/backup.php</code>.</p><?php endif; ?>
      <ul class="list">
        <li><span>ຖານຂໍ້ມູນ (ທຸກມື້)</span><span><?= e($when($backups['db'])) ?></span></li>
        <li><span>ເຕັມ: ຖານຂໍ້ມູນ + ໄຟລ໌ອັບໂຫຼດ (ທຸກອາທິດ)</span><span><?= e($when($backups['full'])) ?></span></li>
      </ul>
      <p class="hint">ສຳເນົາ backup ເຕັມອອກນອກ server ທຸກອາທິດ.</p>
    </section>
    <?php endif; ?>
  </div>

  <section class="panel" aria-labelledby="gaps-h">
    <?= $panelHead('languages', 'gaps-h', 'ຄຳແປທີ່ຍັງຂາດ', $gaps === [] ? 'ທຸກລາຍການມີພາສາໄທ ແລະ ຈີນຄົບ' : 'ຂາດພາສາໄທ ' . $gapCount[0] . ' ລາຍການ, ພາສາຈີນ ' . $gapCount[1] . ' ລາຍການ. ຊ່ອງທີ່ຂາດຈະສະແດງເປັນພາສາອັງກິດແທນ.') ?>
    <?php if ($gaps === []): ?>
      <p><span class="tag tag--done">ຄົບ</span> ທຸກລາຍການທີ່ສະແດງໃນເວັບມີພາສາໄທ ແລະ ຈີນຄົບ.</p>
    <?php else: ?>
      <ul class="list"><?php foreach (array_slice($gaps, 0, 8) as $g): ?><?= $gapRow($g) ?><?php endforeach; ?></ul>
      <?php if (count($gaps) > 8): ?>
        <details class="more">
          <summary>ເບິ່ງອີກ <?= e(count($gaps) - 8) ?> ລາຍການ</summary>
          <ul class="list"><?php foreach (array_slice($gaps, 8) as $g): ?><?= $gapRow($g) ?><?php endforeach; ?></ul>
        </details>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
