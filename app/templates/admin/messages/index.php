<?php
/**
 * @var array $filters @var array $rows @var int $total @var int $page @var int $pages @var array $query
 * @var array $counts per status @var array $subjects per subject, no spam @var array $stats see MessageRepository::inboxStats()
 * @var int $mailErrors @var ?string $oldestNew @var ?array $open message in the preview panel @var int $openNotes
 */
use App\Core\Csrf;
use App\Http\Admin\MessageController as MC;

// lucide icon paths, drawn by $i()
$icons = [
    'refresh' => 'M3 12a9 9 0 019-9 9.75 9.75 0 016.74 2.74L21 8M21 3v5h-5M21 12a9 9 0 01-9 9 9.75 9.75 0 01-6.74-2.74L3 16M8 16H3v5',
    'download' => 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3',
    'bell' => 'M10.27 21a2 2 0 003.46 0M22 8c0-2.3-.8-4.3-2-6M3.26 15.33A1 1 0 004 17h16a1 1 0 00.74-1.67C19.41 13.96 18 12.5 18 8A6 6 0 006 8c0 4.5-1.41 5.96-2.74 7.33M4 2C2.8 3.7 2 5.7 2 8',
    'loader' => 'M12 2v4M16.2 7.8l2.9-2.9M18 12h4M16.2 16.2l2.9 2.9M12 18v4M4.9 19.1l2.9-2.9M2 12h4M4.9 4.9l2.9 2.9',
    'inbox' => 'M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z',
    'check-circle' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM9 12l2 2 4-4',
    'timer' => 'M10 2h4M12 14l3-3M12 22a8 8 0 100-16 8 8 0 000 16z',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'calendar' => 'M8 2v4M16 2v4M3 6a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2zM3 10h18',
    'chevron' => 'M6 9l6 6 6-6',
    'message' => 'M7.9 20A9 9 0 104 16.1L2 22z',
    'cart' => 'M8 21a1 1 0 100-2 1 1 0 000 2zM19 21a1 1 0 100-2 1 1 0 000 2zM2.05 2.05h2l2.66 12.42a2 2 0 002 1.58h9.78a2 2 0 001.95-1.57l1.65-7.43H5.12',
    'wrench' => 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z',
    'hand' => 'M18 11V6a2 2 0 00-4 0M14 10V4a2 2 0 00-4 0v2M10 10.5V6a2 2 0 00-4 0v8M18 8a2 2 0 114 0v6a8 8 0 01-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 012.83-2.82L7 15',
    'check' => 'M20 6L9 17l-5-5',
    'ban' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM4.9 4.9l14.2 14.2',
    'phone' => 'M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0122 16.92z',
    'mail' => 'M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM22 7l-10 6L2 7',
    'mail-x' => 'M22 13V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2h9M22 7l-10 6L2 7M17 17l4 4M21 17l-4 4',
    'external' => 'M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6',
    'arrow' => 'M5 12h14M12 5l7 7-7 7',
    'x' => 'M18 6L6 18M6 6l12 12',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);
$ago = static function (string $at): string {
    $m = max(0, (int) floor((time() - strtotime($at)) / 60));
    return match (true) {
        $m < 1 => 'ຫາກໍ່ນີ້',
        $m < 60 => $m . ' ນາທີກ່ອນ',
        $m < 1440 => floor($m / 60) . ' ຊົ່ວໂມງກ່ອນ',
        $m < 43200 => floor($m / 1440) . ' ມື້ກ່ອນ',
        default => floor($m / 43200) . ' ເດືອນກ່ອນ',
    };
};

$base = $query + ($page > 1 ? ['page' => $page] : []);
$link = static fn (array $extra): string => '/admin/messages' . (($q = http_build_query(array_merge($base, $extra))) !== '' ? '?' . $q : '');
$here = $link(['open' => $open['id'] ?? null]);
$status = $filters['status'];
$openId = $open !== null ? (int) $open['id'] : 0;

$tone = ['new' => 'primary', 'in_progress' => 'warning', 'done' => 'success', 'spam' => 'muted'];
$subjectIcon = ['general' => 'message', 'sales' => 'cart', 'service' => 'wrench', 'other' => 'inbox'];
$subjectColor = ['general' => 'c-1', 'sales' => 'c-2', 'service' => 'c-3', 'other' => 'c-4'];
$active = array_sum($counts) - $counts['spam'];
$waitDays = $oldestNew !== null ? (int) floor((time() - strtotime($oldestNew)) / 86400) : null;
$tabs = ['' => ['ທັງໝົດ', $active], 'new' => ['ໃໝ່', $counts['new']], 'in_progress' => ['ກຳລັງດຳເນີນການ', $counts['in_progress']], 'done' => ['ສຳເລັດ', $counts['done']], 'spam' => ['Spam', $counts['spam']]];

// a stat card: a link that toggles a filter when $href is set
$stat = static function (string $toneName, string $icon, string $label, string $value, string $note, ?string $href = null, bool $on = false) use ($i): string {
    $inner = '<span class="ibx-stat__bar" aria-hidden="true"></span><span class="ibx-stat__ico">' . $i($icon) . '</span>'
        . '<span class="ibx-stat__text"><span class="ibx-stat__label">' . e($label) . '</span><b class="ibx-stat__value">' . e($value) . '</b>'
        . '<span class="ibx-stat__note">' . e($note) . '</span></span>';
    $cls = 'ibx-stat ibx-stat--' . $toneName;
    return $href === null
        ? '<div class="' . $cls . '">' . $inner . '</div>'
        : '<a class="' . $cls . '" href="' . e($href) . '"' . ($on ? ' aria-current="true"' : '') . '>' . $inner . $i('sliders', 'ibx-stat__go') . '</a>';
};
?>
<div class="dash ibx">
  <header class="dash-head ibx-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <h1 class="ibx-head__title">ກ່ອງຂໍ້ຄວາມ<?php if ($counts['new'] > 0): ?><span class="ibx-head__count" aria-label="ຂໍ້ຄວາມໃໝ່ <?= e($counts['new']) ?> ລາຍການ"><?= e($n($counts['new'])) ?></span><?php endif; ?></h1>
        <p class="dash-head__meta">ຂໍ້ຄວາມຈາກຟອມຕິດຕໍ່ຂອງເວັບໄຊ — ຄັດແຍກ, ຮັບເລື່ອງ ແລະ ຕອບກັບລູກຄ້າ</p>
      </div>
      <div class="dash-head__tools">
        <span class="ibx-head__updated" role="status">ອັບເດດ <?= e(date('H:i')) ?></span>
        <a class="ibx-icon-btn" href="<?= e($here) ?>" aria-label="ໂຫຼດໃໝ່" data-reload><?= $i('refresh') ?></a>
        <a class="btn-soft" href="<?= e('/admin/messages/export?' . http_build_query($query)) ?>"><?= $i('download') ?><span>ດາວໂຫຼດ CSV</span></a>
      </div>
    </div>
  </header>

  <div class="ibx-stats">
    <?= $stat('primary', 'bell', 'ຂໍ້ຄວາມໃໝ່', $n($counts['new']), 'ຈາກທັງໝົດ ' . $n($active), $link(['status' => $status === 'new' ? null : 'new', 'page' => null, 'open' => null]), $status === 'new') ?>
    <?= $stat('warning', 'loader', 'ກຳລັງດຳເນີນການ', $n($counts['in_progress']), 'ມອບໃຫ້ທ່ານ ' . $n($stats['mine']), $link(['status' => $status === 'in_progress' ? null : 'in_progress', 'page' => null, 'open' => null]), $status === 'in_progress') ?>
    <?= $stat('info', 'inbox', 'ໄດ້ຮັບມື້ນີ້', $n($stats['today']), 'ນັບແຕ່ທ່ຽງຄືນ') ?>
    <?= $stat('success', 'check-circle', 'ສຳເລັດ · 7 ມື້', $n($stats['done7']), 'ປິດເລື່ອງໃນອາທິດນີ້', $link(['status' => $status === 'done' ? null : 'done', 'page' => null, 'open' => null]), $status === 'done') ?>
    <?= $stat($waitDays !== null && $waitDays >= 2 ? 'danger' : 'accent', 'timer', 'ລໍຖ້າດົນສຸດ', $oldestNew === null ? '–' : trim(preg_replace('/ກ່ອນ$/u', '', $ago($oldestNew))), $oldestNew === null ? 'ບໍ່ມີຂໍ້ຄວາມຄ້າງ' : 'ຂໍ້ຄວາມໃໝ່ທີ່ເກົ່າສຸດ', $oldestNew === null ? null : $link(['status' => 'new', 'sort' => 'oldest', 'page' => null, 'open' => null])) ?>
  </div>

  <?php if ($mailErrors > 0): ?>
    <p class="ibx-alert" role="status"><?= $i('mail-x') ?>ອີເມວແຈ້ງເຕືອນສົ່ງບໍ່ສຳເລັດ <b class="num"><?= e($n($mailErrors)) ?></b> ຂໍ້ຄວາມ — ກວດເບິ່ງໃນກ່ອງຂໍ້ຄວາມ ເພາະທີມອາດບໍ່ໄດ້ຮັບແຈ້ງ.</p>
  <?php endif; ?>

  <div class="ibx-cols">
    <section class="ibx-box" aria-labelledby="ibx-heading">
      <h2 id="ibx-heading" class="sr">ລາຍການຂໍ້ຄວາມ</h2>
      <form class="ibx-tools" method="get" action="/admin/messages" role="search">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <?php if ($filters['subject'] !== ''): ?><input type="hidden" name="subject" value="<?= e($filters['subject']) ?>"><?php endif; ?>
        <div class="ibx-tools__row">
          <nav class="ibx-tabs" aria-label="ສະຖານະ">
            <?php foreach ($tabs as $k => [$label, $count]): ?>
              <a href="<?= e($link(['status' => $k === '' ? null : $k, 'page' => null, 'open' => null])) ?>"<?= $status === (string) $k ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="num"><?= e($n($count)) ?></span></a>
            <?php endforeach; ?>
          </nav>
          <div class="ibx-search">
            <?= $i('search') ?>
            <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="ຄົ້ນຫາຊື່, ອີເມວ, ເບີໂທ, ອົງກອນ ຫຼື ຂໍ້ຄວາມ…" aria-label="ຄົ້ນຫາຂໍ້ຄວາມ" aria-keyshortcuts="/" data-slash-focus>
            <kbd aria-hidden="true">/</kbd>
          </div>
        </div>
        <div class="ibx-tools__row">
          <?php foreach (MC::SUBJECT_NAMES as $k => $label): $on = $filters['subject'] === $k; ?>
            <a class="ibx-chip <?= e($subjectColor[$k]) ?>" href="<?= e($link(['subject' => $on ? null : $k, 'page' => null, 'open' => null])) ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>" role="button">
              <i aria-hidden="true"></i><?= e($label) ?><span class="num"><?= e($n($subjects[$k])) ?></span>
            </a>
          <?php endforeach; ?>
          <div class="ibx-tools__end">
            <details class="ibx-dates"<?= $filters['from'] !== '' || $filters['to'] !== '' ? ' data-on' : '' ?>>
              <summary class="ibx-chip"><?= $i('calendar') ?><?= $filters['from'] !== '' || $filters['to'] !== '' ? e(($filters['from'] !== '' ? date('d/m', strtotime($filters['from'])) : '…') . ' – ' . ($filters['to'] !== '' ? date('d/m', strtotime($filters['to'])) : '…')) : 'ຊ່ວງວັນທີ' ?></summary>
              <div class="ibx-dates__pop">
                <div class="field"><label for="from">ຈາກວັນທີ</label><input id="from" name="from" type="date" value="<?= e($filters['from']) ?>"></div>
                <div class="field"><label for="to">ຫາວັນທີ</label><input id="to" name="to" type="date" value="<?= e($filters['to']) ?>"></div>
                <div class="btns">
                  <button class="btn btn--sm" type="submit">ກອງ</button>
                  <?php if ($filters['from'] !== '' || $filters['to'] !== ''): ?><a class="btn btn--ghost btn--sm" href="<?= e($link(['from' => null, 'to' => null, 'page' => null])) ?>">ລ້າງວັນທີ</a><?php endif; ?>
                </div>
              </div>
            </details>
            <div class="ibx-select">
              <select name="sort" aria-label="ຮຽງລຳດັບ" data-autosubmit>
                <?php foreach (['priority' => 'ໃໝ່ກ່ອນ', 'newest' => 'ລ່າສຸດກ່ອນ', 'oldest' => 'ເກົ່າສຸດກ່ອນ'] as $k => $label): ?>
                  <option value="<?= e($k) ?>"<?= ($filters['sort'] ?: 'priority') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <?= $i('chevron') ?>
            </div>
            <button class="sr" type="submit">ຄົ້ນຫາ</button>
          </div>
        </div>
      </form>

      <form class="ibx-bulk" id="ibx-bulk" method="post" action="/admin/messages/bulk" data-bulk>
        <?= Csrf::field() ?>
        <input type="hidden" name="back" value="<?= e($here) ?>">
        <input type="checkbox" class="ibx-check" aria-label="ເລືອກທຸກຂໍ້ຄວາມໃນໜ້ານີ້" data-check-all hidden>
        <span class="ibx-bulk__info" data-bulk-info>ສະແດງ <?= e($n(count($rows))) ?> ຈາກ <?= e($n($total)) ?><?php if ($query !== [] && array_keys($query) !== ['sort']): ?> · <a href="/admin/messages">ລ້າງຕົວກອງ</a><?php endif; ?></span>
        <span class="ibx-bulk__acts" data-bulk-acts>
          <button type="submit" name="status" value="in_progress"><?= $i('hand') ?>ຮັບເລື່ອງ</button>
          <button type="submit" name="status" value="done"><?= $i('check') ?>ສຳເລັດ</button>
          <button type="submit" name="status" value="spam"><?= $i('ban') ?>Spam</button>
        </span>
      </form>

      <?php if ($rows === []): ?>
        <div class="ibx-empty">
          <span class="ibx-empty__ico"><?= $i('inbox') ?></span>
          <p><b>ບໍ່ພົບຂໍ້ຄວາມ</b><br><span class="muted">ລອງປ່ຽນຕົວກອງ ຫຼື ຄຳຄົ້ນຫາ.</span></p>
        </div>
      <?php else: ?>
        <ul class="ibx-list">
          <?php foreach ($rows as $r):
              $id = (int) $r['id'];
              $isNew = $r['status'] === 'new';
              $subject = MC::SUBJECT_NAMES[$r['subject']] ?? $r['subject'];
              $title = $r['name'] . ($r['org'] ? ' · ' . $r['org'] : '');
          ?>
            <li class="ibx-row ibx-row--<?= e($tone[$r['status']] ?? 'muted') ?><?= $isNew ? ' is-new' : '' ?><?= $id === $openId ? ' is-open' : '' ?>">
              <input type="checkbox" class="ibx-check" name="ids[]" value="<?= e($id) ?>" form="ibx-bulk" aria-label="ເລືອກ “<?= e($r['name']) ?>”">
              <a class="ibx-row__main" href="/admin/messages/<?= e($id) ?>" data-preview="<?= e($link(['open' => $id])) ?>"<?= $id === $openId ? ' aria-current="true"' : '' ?>>
                <span class="ibx-row__ico"><?= $i($subjectIcon[$r['subject']] ?? 'inbox') ?><?php if ($isNew): ?><i aria-hidden="true"></i><?php endif; ?></span>
                <span class="ibx-row__text">
                  <span class="ibx-row__top">
                    <b title="<?= e($title) ?>"><?= e($title) ?></b>
                    <time datetime="<?= e(date('c', strtotime($r['created_at']))) ?>" title="<?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?>"><?= e($ago($r['created_at'])) ?></time>
                  </span>
                  <span class="ibx-row__body"><?= e(mb_strimwidth(preg_replace('/\s+/u', ' ', $r['body']), 0, 180, '…')) ?></span>
                  <span class="ibx-row__meta">
                    <span class="ibx-status"><i aria-hidden="true"></i><?= e(MC::STATUS_NAMES[$r['status']] ?? $r['status']) ?></span>
                    <span aria-hidden="true">·</span><span><?= e($subject) ?></span>
                    <span aria-hidden="true">·</span><span><?= e(MC::LANG_NAMES[$r['lang']] ?? $r['lang']) ?></span>
                    <?php if ($r['assigned_name']): ?><span aria-hidden="true">·</span><span><?= e($r['assigned_name']) ?></span><?php endif; ?>
                    <?php if ($r['mail_error']): ?><span class="tag tag--error" title="<?= e($r['mail_error']) ?>">ອີເມວບໍ່ສຳເລັດ</span><?php endif; ?>
                  </span>
                </span>
              </a>
              <form class="ibx-row__acts" method="post" action="/admin/messages/bulk">
                <?= Csrf::field() ?>
                <input type="hidden" name="back" value="<?= e($here) ?>">
                <input type="hidden" name="ids[]" value="<?= e($id) ?>">
                <?php if ($isNew): ?><button type="submit" name="status" value="in_progress" aria-label="ຮັບເລື່ອງ" title="ຮັບເລື່ອງ"><?= $i('hand') ?></button><?php endif; ?>
                <?php if ($r['status'] !== 'done'): ?><button class="is-ok" type="submit" name="status" value="done" aria-label="ສຳເລັດ" title="ສຳເລັດ"><?= $i('check') ?></button><?php endif; ?>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if ($pages > 1): ?>
        <nav class="ibx-pager" aria-label="ແບ່ງໜ້າ">
          <?php if ($page > 1): ?><a href="<?= e($link(['page' => $page - 1, 'open' => null])) ?>" rel="prev">← ກ່ອນໜ້າ</a><?php endif; ?>
          <span>ໜ້າ <?= e($page) ?> / <?= e($pages) ?></span>
          <?php if ($page < $pages): ?><a href="<?= e($link(['page' => $page + 1, 'open' => null])) ?>" rel="next">ຕໍ່ໄປ →</a><?php endif; ?>
        </nav>
      <?php endif; ?>
    </section>

    <aside class="ibx-panel" aria-label="ຕົວຢ່າງຂໍ້ຄວາມ" aria-live="polite" data-preview-panel>
      <?php if ($open === null): ?>
        <div class="ibx-empty">
          <span class="ibx-empty__ico"><?= $i('mail') ?></span>
          <p><b>ເລືອກຂໍ້ຄວາມ</b><br><span class="muted">ກົດທີ່ຂໍ້ຄວາມໃນລາຍການ ເພື່ອອ່ານ ແລະ ຈັດການຢູ່ບ່ອນນີ້.</span></p>
        </div>
      <?php else:
          $tel = preg_replace('/[^\d+]/', '', $open['phone']);
          // WhatsApp needs the international number: 020… / 021… become 85620… / 85621…
          $wa = preg_replace('/\D/', '', $open['phone']);
          $wa = str_starts_with($wa, '0') ? '856' . substr($wa, 1) : $wa;
          $replySubject = 'Re: ' . (MC::SUBJECT_NAMES[$open['subject']] ?? $open['subject']) . ' — XTK';
      ?>
        <div class="ibx-panel__head">
          <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr(trim($open['name']), 0, 1))) ?></span>
          <div class="ibx-panel__who">
            <h2><?= e($open['name']) ?></h2>
            <p><?= e($open['org'] ?? 'ບໍ່ລະບຸອົງກອນ') ?></p>
          </div>
          <a class="ibx-icon-btn" href="<?= e($link(['open' => null])) ?>" aria-label="ປິດຕົວຢ່າງ" data-preview-close><?= $i('x') ?></a>
        </div>
        <?php if ($open['mail_error']): ?><p class="ibx-alert"><?= $i('mail-x') ?>ສົ່ງອີເມວແຈ້ງເຕືອນບໍ່ສຳເລັດ: <?= e($open['mail_error']) ?></p><?php endif; ?>
        <div class="ibx-panel__contact">
          <a href="tel:<?= e($tel) ?>"><?= $i('phone') ?><?= e($open['phone']) ?></a>
          <a href="mailto:<?= e($open['email']) ?>?subject=<?= e(rawurlencode($replySubject)) ?>"><?= $i('mail') ?><?= e($open['email']) ?></a>
          <a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= $i('message') ?>WhatsApp</a>
        </div>
        <dl class="ibx-panel__meta">
          <dt>ສະຖານະ</dt><dd><span class="tag tag--<?= e($open['status']) ?>"><?= e(MC::STATUS_NAMES[$open['status']] ?? $open['status']) ?></span></dd>
          <dt>ຫົວຂໍ້</dt><dd><?= e(MC::SUBJECT_NAMES[$open['subject']] ?? $open['subject']) ?></dd>
          <dt>ພາສາ</dt><dd><?= e(MC::LANG_NAMES[$open['lang']] ?? $open['lang']) ?></dd>
          <dt>ວັນທີ</dt><dd><?= e(date('d/m/Y H:i', strtotime($open['created_at']))) ?> · <?= e($ago($open['created_at'])) ?></dd>
          <dt>ຜູ້ຮັບຜິດຊອບ</dt><dd><?= e($open['assigned_name'] ?? '—') ?></dd>
        </dl>
        <div class="ibx-panel__body"><?= nl2br(e($open['body'])) ?></div>
        <form class="ibx-panel__acts" method="post" action="/admin/messages/bulk">
          <?= Csrf::field() ?>
          <input type="hidden" name="back" value="<?= e($here) ?>">
          <input type="hidden" name="ids[]" value="<?= e($openId) ?>">
          <?php if ($open['status'] === 'new'): ?><button class="btn btn--sm" type="submit" name="status" value="in_progress"><?= $i('hand') ?>ຮັບເລື່ອງ</button><?php endif; ?>
          <?php if ($open['status'] !== 'done'): ?><button class="btn btn--sm<?= $open['status'] === 'new' ? ' btn--ghost' : '' ?>" type="submit" name="status" value="done"><?= $i('check') ?>ສຳເລັດ</button><?php endif; ?>
          <?php if ($open['status'] !== 'spam'): ?><button class="btn btn--ghost btn--sm" type="submit" name="status" value="spam"><?= $i('ban') ?>Spam</button><?php endif; ?>
        </form>
        <a class="panel__link ibx-panel__more" href="/admin/messages/<?= e($openId) ?>">ເປີດເຕັມ · ມອບໝາຍ ແລະ ບັນທຶກ (<?= e($openNotes) ?>)<?= $i('arrow') ?></a>
      <?php endif; ?>
    </aside>
  </div>
</div>
