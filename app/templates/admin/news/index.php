<?php
/** @var array $rows all news, newest first, with live (own page is on the site) @var array $filters status, category, show ('' | nocover), q */
use App\Http\Admin\MediaController as MC;
use App\Http\Admin\NewsController as NC;

// lucide icon paths, drawn by $i()
$icons = [
    'newspaper' => 'M15 18h-5M18 14h-8M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-4 0v-9a2 2 0 012-2h2M10 6h8v4h-8z',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'file-pen' => 'M12.5 22H18a2 2 0 002-2V7l-5-5H6a2 2 0 00-2 2v9.5M14 2v4a2 2 0 002 2h4M13.38 12.62a1 1 0 00-3-3L5.37 14.6a2 2 0 00-.5.85l-.84 2.86a.5.5 0 00.62.62l2.86-.84a2 2 0 00.85-.5z',
    'upload' => 'M12 16V4M7 9l5-5 5 5M5 20h14',
    'image' => 'M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zM9 11a2 2 0 100-4 2 2 0 000 4zM21 15l-3.09-3.09a2 2 0 00-2.82 0L6 21',
    'check-circle' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM9 12l2 2 4-4',
    'box' => 'M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12',
    'building' => 'M6 22V4a2 2 0 012-2h8a2 2 0 012 2v18zM6 12H4a2 2 0 00-2 2v6a2 2 0 002 2h2M18 9h2a2 2 0 012 2v9a2 2 0 01-2 2h-2M10 6h4M10 10h4M10 14h4M10 18h4',
    'calendar' => 'M8 2v4M16 2v4M3 6a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2zM3 10h18',
    'file' => 'M15 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7zM14 2v4a2 2 0 002 2h4',
    'link' => 'M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'sliders' => 'M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4',
    'plus' => 'M5 12h14M12 5v14',
    'pencil' => 'M21.17 6.81a1 1 0 00-3.98-3.98L3.84 16.17a2 2 0 00-.5.83l-1.32 4.35a.5.5 0 00.62.62l4.35-1.32a2 2 0 00.83-.5zM15 5l4 4',
    'external' => 'M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);
$ago = static function (string $day): string {
    $d = (int) floor((strtotime('today') - strtotime($day)) / 86400);
    return match (true) {
        $d < 0 => 'ອີກ ' . -$d . ' ມື້',
        $d === 0 => 'ມື້ນີ້',
        $d === 1 => 'ມື້ວານ',
        $d < 31 => $d . ' ມື້ກ່ອນ',
        $d < 365 => floor($d / 30) . ' ເດືອນກ່ອນ',
        default => floor($d / 365) . ' ປີກ່ອນ',
    };
};

['status' => $status, 'category' => $category, 'show' => $show, 'q' => $q] = $filters;
$link = static fn (array $extra): string => '/admin/news' . (($s = http_build_query(array_filter(array_merge($filters, $extra), static fn ($v) => $v !== null && $v !== ''))) !== '' ? '?' . $s : '');
$filtered = $status !== '' || $category !== '' || $show !== '' || $q !== '';

$catIcon = ['product' => 'box', 'company' => 'building', 'event' => 'calendar'];
$catColor = ['product' => 'c-1', 'company' => 'c-2', 'event' => 'c-3'];
$byStatus = array_count_values(array_column($rows, 'status')) + ['draft' => 0, 'published' => 0];
$byCategory = array_count_values(array_column($rows, 'category')) + array_fill_keys(array_keys(NC::CATEGORY_NAMES), 0);
$noCover = count(array_filter($rows, static fn (array $r): bool => !$r['cover_path']));
// published with a page of its own, but that page is not on the site yet
$waiting = count(array_filter($rows, static fn (array $r): bool => $r['status'] === 'published' && (int) $r['has_page'] === 1 && !$r['live']));
$latest = array_values(array_filter($rows, static fn (array $r): bool => $r['status'] === 'published'))[0] ?? null;

$list = array_values(array_filter($rows, static function (array $r) use ($status, $category, $show, $q): bool {
    return ($status === '' || $r['status'] === $status)
        && ($category === '' || $r['category'] === $category)
        && ($show === '' || !$r['cover_path'])
        && ($q === '' || mb_stripos($r['title_lo'] . ' ' . $r['title_en'] . ' ' . $r['slug'] . ' ' . ($r['link_url'] ?? ''), $q) !== false);
}));
$tabs = ['' => ['ທັງໝົດ', count($rows)], 'published' => ['ເຜີຍແຜ່', $byStatus['published']], 'draft' => ['ສະບັບຮ່າງ', $byStatus['draft']]];

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
<div class="dash ibx dv nw">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="dash-head__text">
        <h1>ຂ່າວສານ</h1>
        <p class="dash-head__meta">ຂ່າວ ແລະ ກິດຈະກຳຂອງ XTK — ຂ່າວທີ່ມີເນື້ອຫາມີໜ້າເວັບຂອງຕົນເອງ, ບໍ່ມີເນື້ອຫາເປັນກາດທີ່ລິ້ງອອກໄປ</p>
      </div>
      <div class="dash-head__tools">
        <a class="btn-soft" href="/admin/publish"><?= $i('upload') ?><span>ເຜີຍແຜ່</span></a>
        <a class="btn btn--sm" href="/admin/news/new"><?= $i('plus') ?>ເພີ່ມຂ່າວ</a>
      </div>
    </div>
  </header>

  <div class="ibx-stats">
    <?= $stat('primary', 'newspaper', 'ຂ່າວທັງໝົດ', $n(count($rows)), $latest !== null ? 'ລ່າສຸດ ' . $ago($latest['published_on']) : 'ຍັງບໍ່ມີຂ່າວທີ່ເຜີຍແຜ່', $filtered ? '/admin/news' : null) ?>
    <?= $stat('success', 'eye', 'ເຜີຍແຜ່', $n($byStatus['published']), 'ສະແດງໃນໜ້າເວັບ', $link(['status' => $status === 'published' ? null : 'published']), $status === 'published') ?>
    <?= $stat('warning', 'file-pen', 'ສະບັບຮ່າງ', $n($byStatus['draft']), 'ບໍ່ຂຶ້ນໜ້າເວັບ', $link(['status' => $status === 'draft' ? null : 'draft']), $status === 'draft') ?>
    <?= $waiting > 0
        ? $stat('accent', 'upload', 'ລໍຖ້າເຜີຍແຜ່', $n($waiting), 'ກົດ "ເຜີຍແຜ່" ໃຫ້ຂຶ້ນເວັບ', '/admin/publish')
        : $stat('info', 'check-circle', 'ຂຶ້ນເວັບແລ້ວ', $n(count(array_filter($rows, static fn (array $r): bool => $r['live']))), 'ໜ້າຂ່າວທີ່ຢູ່ໃນເວັບ') ?>
    <?= $stat($noCover > 0 ? 'danger' : 'success', 'image', 'ບໍ່ມີຮູບປົກ', $n($noCover), $noCover > 0 ? 'ກາດຂ່າວຈະບໍ່ມີຮູບ' : 'ທຸກຂ່າວມີຮູບປົກ', $noCover > 0 ? $link(['show' => $show === 'nocover' ? null : 'nocover']) : null, $show === 'nocover') ?>
  </div>

  <section class="ibx-box" aria-labelledby="nw-heading">
    <h2 id="nw-heading" class="sr">ລາຍການຂ່າວ</h2>
    <form class="ibx-tools" method="get" action="/admin/news" role="search">
      <?php foreach (['status' => $status, 'category' => $category, 'show' => $show] as $k => $v): ?>
        <?php if ($v !== ''): ?><input type="hidden" name="<?= $k ?>" value="<?= e($v) ?>"><?php endif; ?>
      <?php endforeach; ?>
      <div class="ibx-tools__row">
        <nav class="ibx-tabs" aria-label="ສະຖານະ">
          <?php foreach ($tabs as $k => [$label, $count]): ?>
            <a href="<?= e($link(['status' => $k === '' ? null : $k])) ?>"<?= $status === (string) $k ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="num"><?= e($n($count)) ?></span></a>
          <?php endforeach; ?>
        </nav>
        <div class="ibx-search">
          <?= $i('search') ?>
          <input type="search" name="q" value="<?= e($q) ?>" placeholder="ຄົ້ນຫາຫົວຂໍ້, ຊື່ໜ້າເວັບ ຫຼື ລິ້ງ…" aria-label="ຄົ້ນຫາຂ່າວ" aria-keyshortcuts="/" data-slash-focus>
          <kbd aria-hidden="true">/</kbd>
        </div>
      </div>
      <div class="ibx-tools__row">
        <?php foreach (NC::CATEGORY_NAMES as $k => $label): $on = $category === $k; ?>
          <a class="ibx-chip <?= e($catColor[$k]) ?>" href="<?= e($link(['category' => $on ? null : $k])) ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>" role="button">
            <i aria-hidden="true"></i><?= e($label) ?><span class="num"><?= e($n($byCategory[$k])) ?></span>
          </a>
        <?php endforeach; ?>
        <button class="sr" type="submit">ຄົ້ນຫາ</button>
      </div>
    </form>

    <div class="ibx-bulk">
      <span class="ibx-bulk__info">
        ສະແດງ <?= e($n(count($list))) ?> ຈາກ <?= e($n(count($rows))) ?> · ລຽງຕາມວັນທີ, ໃໝ່ສຸດກ່ອນ
        <?php if ($filtered): ?> · <a href="/admin/news">ລ້າງຕົວກອງ</a><?php endif; ?>
      </span>
    </div>

    <?php if ($list === []): ?>
      <div class="ibx-empty">
        <span class="ibx-empty__ico"><?= $i('newspaper') ?></span>
        <?php if ($rows === []): ?>
          <p><b>ຍັງບໍ່ມີຂ່າວ</b><br><span class="muted">ເພີ່ມຂ່າວທຳອິດ — ບັນທຶກເປັນສະບັບຮ່າງກ່ອນກໍໄດ້, ສະບັບຮ່າງບໍ່ຂຶ້ນໜ້າເວັບ.</span></p>
          <a class="btn btn--sm" href="/admin/news/new"><?= $i('plus') ?>ເພີ່ມຂ່າວ</a>
        <?php else: ?>
          <p><b>ບໍ່ພົບຂ່າວ</b><br><span class="muted">ລອງປ່ຽນຕົວກອງ ຫຼື ຄຳຄົ້ນຫາ.</span></p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <ul class="ibx-list">
        <?php foreach ($list as $r):
            $id = (int) $r['id'];
            $draft = $r['status'] === 'draft';
            $page = (int) $r['has_page'] === 1;
            $pending = !$draft && $page && !$r['live'];
            $title = $r['title_lo'];
        ?>
          <li class="ibx-row dv-row nw-row ibx-row--<?= $draft ? 'warning' : ($pending ? 'accent' : 'success') ?><?= $draft ? ' is-hidden' : '' ?>" id="n<?= e($id) ?>">
            <a class="ibx-row__main" href="/admin/news/<?= e($id) ?>">
              <span class="dv-thumb nw-thumb">
                <?php if ($r['cover_path']): ?>
                  <img src="<?= e(MC::thumb(['path' => $r['cover_path'], 'variants' => $r['cover_variants']])) ?>" alt="" loading="lazy" width="96" height="64">
                <?php else: ?>
                  <span class="dv-thumb__ico" title="ບໍ່ມີຮູບປົກ"><?= $i('image') ?></span>
                <?php endif; ?>
              </span>
              <span class="ibx-row__text">
                <span class="ibx-row__top">
                  <b title="<?= e($title) ?>"><?= e($title) ?></b>
                  <time datetime="<?= e($r['published_on']) ?>" title="<?= e($ago($r['published_on'])) ?>"><?= e(date('d/m/Y', strtotime($r['published_on']))) ?></time>
                </span>
                <span class="ibx-row__body"><?= e($r['title_en']) ?></span>
                <span class="ibx-row__meta">
                  <span class="ibx-status"><i aria-hidden="true"></i><?= $draft ? 'ສະບັບຮ່າງ' : ($pending ? 'ລໍຖ້າເຜີຍແຜ່' : 'ເຜີຍແຜ່') ?></span>
                  <span aria-hidden="true">·</span><span class="dv-meta nw-cat <?= e($catColor[$r['category']] ?? 'c-4') ?>"><?= $i($catIcon[$r['category']] ?? 'newspaper') ?><?= e(NC::CATEGORY_NAMES[$r['category']] ?? $r['category']) ?></span>
                  <?php if ($page): ?>
                    <span aria-hidden="true">·</span><span class="dv-meta"><?= $i('file') ?><code>news-<?= e($r['slug']) ?>.html</code></span>
                  <?php else: ?>
                    <span aria-hidden="true">·</span><span class="dv-meta nw-link" title="<?= e('ບໍ່ມີໜ້າຂອງຕົນເອງ — ກາດລິ້ງໄປ ' . $r['link_url']) ?>"><?= $i('link') ?><?= e($r['link_url']) ?></span>
                  <?php endif; ?>
                  <?php if (!$r['cover_path']): ?><span class="tag tag--warn">ບໍ່ມີຮູບປົກ</span><?php endif; ?>
                </span>
              </span>
            </a>
            <div class="dv-acts">
              <?php if ($r['live']): ?><a class="ibx-icon-btn" href="/lo/news-<?= e($r['slug']) ?>.html" target="_blank" rel="noopener" aria-label="<?= e('ເບິ່ງໜ້າເວັບ ' . $title . ' (ເປີດແຖບໃໝ່)') ?>" title="ເບິ່ງໜ້າເວັບ"><?= $i('external') ?></a><?php endif; ?>
              <a class="ibx-icon-btn" href="/admin/news/<?= e($id) ?>" aria-label="<?= e('ແກ້ໄຂ ' . $title) ?>" title="ແກ້ໄຂ"><?= $i('pencil') ?></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <p class="dv-foot">ສະບັບຮ່າງບໍ່ຂຶ້ນໜ້າເວັບ. ຂ່າວທີ່ບໍ່ມີເນື້ອຫາ ບໍ່ມີໜ້າຂອງຕົນເອງ — ກາດຂ່າວລິ້ງໄປ "ລິ້ງປຸ່ມ" ແທນ. ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
  </section>
</div>
