<?php
/**
 * /admin/theme. Without JS: one HEX field per color (empty = follows its base color). theme.js adds the RGB editor,
 * the live preview, the readability checks, ready-made themes and import/export.
 * Layout: the inbox header, ready-made theme cards, the color list in a box on the left, the editor and preview in a sticky panel on the right.
 * @var array $theme {site, admin} @var array $saved @var array $history @var array $warnings {site, admin}
 */
use App\Core\Csrf;
use App\Service\ThemeService as T;

$data = T::clientData() + ['theme' => $theme, 'saved' => $saved, 'history' => $history];
$pages = ['index' => 'ໜ້າຫຼັກ', 'about' => 'ກ່ຽວກັບ', 'products' => 'ສິນຄ້າ', 'solutions' => 'Solutions', 'suppliers' => 'ຜູ້ສະໜອງ', 'contact' => 'ຕິດຕໍ່', 'faq' => 'FAQ'];
$channels = ['r' => ['R', 'ສີແດງ', 255], 'g' => ['G', 'ສີຂຽວ', 255], 'b' => ['B', 'ສີນ້ຳເງິນ', 255], 'a' => ['A', 'ຄວາມທຶບ (%)', 100]];

// lucide icon paths, drawn by $i()
$icons = [
    'palette' => 'M12 22a1 1 0 010-20 10 9 0 0110 9 5 5 0 01-5 5h-2.25a1.75 1.75 0 00-1.4 2.8l.3.4a1.75 1.75 0 01-1.4 2.8zM13.5 6.5a.5.5 0 100-1 .5.5 0 000 1zM17.5 10.5a.5.5 0 100-1 .5.5 0 000 1zM6.5 12.5a.5.5 0 100-1 .5.5 0 000 1zM8.5 7.5a.5.5 0 100-1 .5.5 0 000 1z',
    'globe' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM2 12h20M12 2a14.5 14.5 0 000 20 14.5 14.5 0 000-20',
    'sun' => 'M12 16a4 4 0 100-8 4 4 0 000 8zM12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41',
    'moon' => 'M12 3a6 6 0 009 9 9 9 0 11-9-9z',
    'eye' => 'M2.06 12.35a1 1 0 010-.7 10.75 10.75 0 0119.88 0 1 1 0 010 .7 10.75 10.75 0 01-19.88 0zM12 15a3 3 0 100-6 3 3 0 000 6z',
    'bookmark' => 'M19 21l-7-4-7 4V5a2 2 0 012-2h10a2 2 0 012 2z',
    'search' => 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.3-4.3',
    'chevron' => 'M6 9l6 6 6-6',
    'download' => 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3',
    'upload' => 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12',
    'undo' => 'M3 7v6h6M21 17a9 9 0 00-9-9 9 9 0 00-6 2.3L3 13',
    'info' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM12 16v-4M12 8h.01',
    'pipette' => 'M2 22l1-1h3l9-9M3 21v-3l9-9M15 6l3.4-3.4a2.1 2.1 0 013 3L18 9l.4.4a2.1 2.1 0 01-3 3l-3.8-3.8a2.1 2.1 0 013-3l.4.4z',
    'layers' => 'M12.83 2.18a2 2 0 00-1.66 0L2.6 6.08a1 1 0 000 1.83l8.58 3.91a2 2 0 001.66 0l8.58-3.9a1 1 0 000-1.83zM2 12a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 12M2 17a1 1 0 00.58.91l8.6 3.91a2 2 0 001.65 0l8.58-3.9A1 1 0 0022 17',
    'monitor' => 'M4 3h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2zM8 21h8M12 17v4',
    'phone' => 'M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2zM12 18h.01',
    'maximize' => 'M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7',
    'minimize' => 'M4 14h6v6M20 10h-6V4M14 10l7-7M3 21l7-7',
    'save' => 'M15.2 3a2 2 0 011.4.6l3.8 3.8a2 2 0 01.6 1.4V19a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zM17 21v-7a1 1 0 00-1-1H8a1 1 0 00-1 1v7M7 3v4a1 1 0 001 1h7',
    'trash' => 'M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6',
    'check-circle' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM9 12l2 2 4-4',
];
$i = static fn (string $name, string $class = ''): string => '<svg class="ico' . ($class !== '' ? ' ' . $class : '') . '" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
$n = static fn (int $v): string => number_format($v);

$scopeIcon = ['site' => 'globe', 'admin' => 'sun', 'dark' => 'moon'];
// the brand color of a stored theme, or the default one: drawn as three dots in the saved list
// ready-made themes as cards: the brand scale of each, the stored defaults for the XTK one (its scale is empty)
$scaleKeys = ['brand-900', 'brand-600', 'brand-500', 'brand-100', 'brand-50'];
$presetScale = static fn (array $p): array => array_map(static fn (string $k): string => (string) ($p['brand'][$k] ?? $data['scopes']['site']['tokens'][$k]['css']), $scaleKeys);
$brand = static fn (array $t, string $s): string => (string) ($t[$s]['brand-600'] ?? $data['scopes'][$s]['tokens']['brand-600']['css']);

?>
<script type="application/json" id="theme-data"><?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) ?></script>
<div class="dash ibx thm">
  <header class="dash-head">
    <div class="dash-head__blob" aria-hidden="true"></div>
    <div class="dash-head__blob dash-head__blob--2" aria-hidden="true"></div>
    <div class="dash-head__row">
      <div class="gpe-head">
        <span class="gpe-head__ico"><?= $i('palette') ?></span>
        <div class="dash-head__text">
          <h1>ຕີມສີ</h1>
          <p class="dash-head__meta">ສີທຸກສ່ວນຂອງໜ້າເວັບ ແລະ admin — ປັບດ້ວຍແຖບ R G B, ເບິ່ງຕົວຢ່າງທັນທີ, ກວດຄວາມອ່ານງ່າຍ</p>
        </div>
      </div>
      <div class="dash-head__tools" data-js hidden>
        <div class="ibx-select thm-load">
          <select id="th-load" data-load aria-label="ໂຫຼດຕີມ">
            <option value="">ໂຫຼດຕີມ…</option>
          </select>
          <?= $i('chevron') ?>
        </div>
        <button type="button" class="btn-soft" data-export><?= $i('download') ?><span>Export</span></button>
        <label class="btn-soft thm-import"><?= $i('upload') ?><span>Import</span><input type="file" accept="application/json,.json" data-import class="sr"></label>
      </div>
    </div>
  </header>

  <section class="thm-presets" aria-labelledby="thm-presets-title" data-js hidden>
    <div class="thm-presets__head">
      <h2 id="thm-presets-title"><?= $i('palette') ?>ຕີມສຳເລັດຮູບ</h2>
      <span class="gpe-box__note">ກົດເພື່ອລອງເບິ່ງ ແລ້ວກົດ "ບັນທຶກສີ" ເພື່ອໃຊ້ — ໃຊ້ທັງໜ້າເວັບ ແລະ admin</span>
    </div>
    <div class="thm-presets__list">
      <?php foreach ($data['presets'] as $k => $p): ?>
        <button type="button" class="thm-preset" data-preset="<?= e($k) ?>" aria-pressed="false">
          <span class="thm-preset__strip" aria-hidden="true"><?php foreach ($presetScale($p) as $c): ?><i data-color="<?= e($c) ?>"></i><?php endforeach; ?></span>
          <span class="thm-preset__name"><?= e($p['name']) ?></span>
          <span class="thm-preset__on"><?= $i('check-circle') ?>ໃຊ້ຢູ່</span>
        </button>
      <?php endforeach; ?>
    </div>
  </section>

  <p class="thm-note"><?= $i('info') ?><span>ສີທີ່ບໍ່ໄດ້ປ່ຽນຈະ <b>ຕາມສີຫຼັກ</b> ຂອງມັນ (ເຊັ່ນ ປຸ່ມ ຕາມ ແບຣນ ຫຼັກ). ສີ admin ມີຜົນທັນທີທີ່ບັນທຶກ — ເລືອກໂໝດມືດໄດ້ທີ່ເມນູບັນຊີ ມຸມຂວາເທິງ; ສີໜ້າເວັບຂຶ້ນເວັບຫຼັງກົດ "ເຜີຍແຜ່".</span></p>

  <form method="post" action="/admin/theme" class="th" id="theme-form" data-theme-form>
    <?= Csrf::field() ?>
    <div class="ibx-cols thm-cols">
      <section class="ibx-box thm-box" aria-labelledby="thm-list-title">
        <h2 id="thm-list-title" class="sr">ລາຍການສີ</h2>
        <div class="ibx-tools">
          <div class="ibx-tools__row">
            <div class="ibx-tabs" role="tablist" aria-label="ສ່ວນທີ່ຈະປັບ">
              <?php foreach (T::SCOPES as $scope => $label): ?>
                <button type="button" role="tab" id="tab-<?= $scope ?>" aria-controls="panel-<?= $scope ?>" aria-selected="<?= $scope === 'site' ? 'true' : 'false' ?>" data-scope-tab="<?= $scope ?>">
                  <?= $i($scopeIcon[$scope]) ?><?= e($label) ?><span class="num" data-count="<?= $scope ?>"><?= count($theme[$scope]) ?></span>
                </button>
              <?php endforeach; ?>
            </div>
            <div class="ibx-search" data-js hidden>
              <?= $i('search') ?>
              <input id="th-q" type="search" placeholder="ຄົ້ນຫາ: ປຸ່ມ, footer, hero…" aria-label="ຄົ້ນຫາສີ" aria-keyshortcuts="/" data-search-colors data-slash-focus autocomplete="off">
              <kbd aria-hidden="true">/</kbd>
            </div>
          </div>
          <div class="ibx-tools__row" data-js hidden>
            <button type="button" class="ibx-chip c-1" aria-pressed="false" data-only-set><i aria-hidden="true"></i>ສະເພາະສີທີ່ປ່ຽນ</button>
            <div class="ibx-tools__end">
              <button type="button" class="ibx-chip" data-reset-scope><?= $i('undo') ?>ຄືນຄ່າເດີມທັງແທັບ</button>
            </div>
          </div>
        </div>

        <?php foreach (T::SCOPES as $scope => $label): ?>
          <div class="th-panel" id="panel-<?= $scope ?>" role="tabpanel" aria-labelledby="tab-<?= $scope ?>" data-panel="<?= $scope ?>"<?= $scope === 'site' ? '' : ' data-off' ?>>
            <?php foreach (T::groups($scope) as $group => $tokens): $gid = 'g-' . $scope . '-' . substr(md5($group), 0, 6); ?>
              <section class="th-group" aria-labelledby="<?= $gid ?>">
                <h3 class="th-group__head" id="<?= $gid ?>"><?= e($group) ?><span class="gpe-box__count"><?= count($tokens) ?></span></h3>
                <?php foreach ($tokens as $token => [$name, $default]): ?>
                  <?php $id = 'c-' . $scope . '-' . $token; $val = $theme[$scope][$token] ?? ''; ?>
                  <div class="th-row" data-token="<?= e($token) ?>" data-text="<?= e(mb_strtolower($name . ' ' . $token . ' ' . $group)) ?>"<?= $val !== '' ? ' data-set' : '' ?>>
                    <button type="button" class="th-row__pick" data-pick aria-describedby="<?= $id ?>-val">
                      <span class="th-sw" aria-hidden="true"><i></i></span>
                      <span class="th-row__text">
                        <span class="th-row__name"><?= e($name) ?></span>
                        <code class="th-row__token">--<?= e($token) ?></code>
                      </span>
                      <span class="th-row__val" id="<?= $id ?>-val"><?= e($val !== '' ? $val : 'ຕາມ ' . (is_array($default) ? $default[0] . ' ' . $default[1] . '%' : $default)) ?></span>
                    </button>
                    <label class="sr" for="<?= $id ?>"><?= e($name) ?> (HEX)</label>
                    <input class="th-row__hex" id="<?= $id ?>" name="<?= $scope ?>[<?= e($token) ?>]" value="<?= e($val) ?>" placeholder="ຄ່າເດີມ" maxlength="9" pattern="#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?" spellcheck="false" autocomplete="off">
                  </div>
                <?php endforeach; ?>
              </section>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
        <div class="ibx-empty" data-search-empty hidden>
          <span class="ibx-empty__ico"><?= $i('search') ?></span>
          <p><b>ບໍ່ພົບສີ</b><br><span class="muted">ລອງຄຳຄົ້ນຫາອື່ນ ຫຼື ປິດ "ສະເພາະສີທີ່ປ່ຽນ".</span></p>
        </div>
      </section>

      <aside class="ibx-panel thm-panel" aria-label="ປັບສີ ແລະ ຕົວຢ່າງ">
        <section class="th-ed" aria-labelledby="th-ed-title" data-ed hidden>
          <div class="ibx-panel__head">
            <span class="th-sw th-sw--lg" aria-hidden="true"><i data-ed-sw></i></span>
            <div class="ibx-panel__who">
              <h2 id="th-ed-title" data-ed-label>ເລືອກສີ</h2>
              <p><code data-ed-token></code></p>
            </div>
          </div>
          <label class="gpe-toggle th-follow">
            <span class="gpe-toggle__text"><b data-ed-follow-text>ຕາມສີຫຼັກ</b><span>ເປີດໄວ້ = ສີນີ້ປ່ຽນຕາມສີຫຼັກຂອງມັນເອງ</span></span>
            <input type="checkbox" data-ed-follow>
          </label>
          <div class="th-sls">
            <?php foreach ($channels as $ch => [$short, $label, $max]): ?>
              <div class="th-sl" data-ch="<?= $ch ?>">
                <label for="th-<?= $ch ?>" title="<?= e($label) ?>"><?= $short ?></label>
                <input type="range" id="th-<?= $ch ?>" min="0" max="<?= $max ?>" step="1" data-ed-range="<?= $ch ?>" aria-label="<?= e($label) ?>">
                <input type="number" min="0" max="<?= $max ?>" step="1" inputmode="numeric" data-ed-num="<?= $ch ?>" aria-label="<?= e($label) ?> ຕົວເລກ">
              </div>
            <?php endforeach; ?>
          </div>
          <div class="th-ed__codes">
            <div class="field"><label for="th-hex">HEX</label><input id="th-hex" data-ed-hex maxlength="9" spellcheck="false" autocomplete="off"></div>
            <div class="field"><label for="th-rgb">RGB</label><input id="th-rgb" data-ed-rgb spellcheck="false" autocomplete="off" placeholder="rgb(47, 54, 142)"></div>
          </div>
          <div class="th-ed__tools">
            <button type="button" class="btn-soft" data-ed-drop hidden><?= $i('pipette') ?><span>ດູດສີຈາກໜ້າຈໍ</span></button>
            <button type="button" class="btn-soft" data-ed-scale hidden><?= $i('layers') ?><span>ສ້າງລະດັບສີແບຣນຈາກສີນີ້</span></button>
          </div>
          <div class="th-recent" data-ed-recent-box hidden>
            <span class="thm-label">ສີທີ່ໃຊ້ຫຼ້າສຸດ</span>
            <div data-ed-recent></div>
          </div>
          <ul class="th-pairs" data-ed-pairs></ul>
        </section>

        <section class="thm-sec th-check" aria-labelledby="th-check-title">
          <h2 id="th-check-title" class="thm-sec__title"><?= $i('eye') ?>ຄວາມອ່ານງ່າຍ</h2>
          <div data-warn aria-live="polite">
            <?php foreach (T::SCOPES as $scope => $label): ?>
              <?php if ($warnings[$scope] !== []): ?>
                <p class="hint"><?= e($label) ?>:</p>
                <ul class="th-warn">
                  <?php foreach ($warnings[$scope] as $w): ?><li><?= e($w['what']) ?> — <?= e($w['ratio']) ?>:1</li><?php endforeach; ?>
                </ul>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="thm-sec th-prev" aria-labelledby="th-prev-title" data-prev hidden>
          <div class="th-prev__bar">
            <h2 id="th-prev-title" class="thm-sec__title"><?= $i('monitor') ?>ຕົວຢ່າງໜ້າເວັບ</h2>
            <div class="th-prev__ctl">
              <div class="ibx-select">
                <select id="th-page" data-prev-page aria-label="ໜ້າທີ່ຈະເບິ່ງ">
                  <?php foreach ($pages as $slug => $label): ?><option value="<?= $slug ?>"><?= e($label) ?></option><?php endforeach; ?>
                </select>
                <?= $i('chevron') ?>
              </div>
              <div class="ibx-tabs" role="group" aria-label="ຂະໜາດຈໍ">
                <button type="button" data-prev-size="desktop" aria-pressed="true" aria-label="ຄອມ" title="ຄອມ"><?= $i('monitor') ?></button>
                <button type="button" data-prev-size="mobile" aria-pressed="false" aria-label="ມືຖື" title="ມືຖື"><?= $i('phone') ?></button>
              </div>
              <button type="button" class="th-prev__big" data-prev-big aria-pressed="false" aria-label="ຂະຫຍາຍຕົວຢ່າງເຕັມຈໍ" title="ຂະຫຍາຍ (Esc ເພື່ອປິດ)"><?= $i('maximize') ?><?= $i('minimize') ?></button>
            </div>
          </div>
          <div class="th-prev__frame" data-prev-box>
            <iframe title="ຕົວຢ່າງໜ້າເວັບ" data-prev-frame loading="lazy"></iframe>
          </div>
          <p class="thm-sec__hint">ຕົວຢ່າງປ່ຽນສີທັນທີ ກ່ອນບັນທຶກ.</p>
        </section>

        <section class="thm-sec th-admin-note" aria-labelledby="th-admin-title" data-admin-note hidden>
          <h2 id="th-admin-title" class="thm-sec__title"><?= $i('monitor') ?>ຕົວຢ່າງ admin</h2>
          <p class="thm-sec__hint">ໜ້ານີ້ທັງໝົດປ່ຽນເປັນສີ admin ທີ່ກຳລັງປັບ.</p>
          <div class="btns"><span class="btn btn--sm">ປຸ່ມຫຼັກ</span><span class="btn btn--ghost btn--sm">ປຸ່ມຂອບ</span></div>
          <p class="flash flash--ok">ບັນທຶກແລ້ວ.</p>
          <p class="flash flash--error">ມີຂໍ້ຜິດພາດ.</p>
          <p><span class="tag tag--done">ສຳເລັດ</span> <span class="tag tag--warn">ລໍຖ້າ</span></p>
        </section>
      </aside>
    </div>

    <div class="thm-save">
      <span class="thm-save__state" data-dirty-note role="status"><i aria-hidden="true"></i><span data-dirty-text>ສີທັງໝົດບັນທຶກແລ້ວ</span></span>
      <div class="field">
        <label for="th-name" class="sr">ບັນທຶກເປັນຕີມ (ບໍ່ບັງຄັບ)</label>
        <input id="th-name" name="save_as" maxlength="60" placeholder="ບັນທຶກເປັນຕີມ (ບໍ່ບັງຄັບ) ເຊັ່ນ ສີບຸນປີໃໝ່">
      </div>
      <button type="submit" class="btn"><?= $i('save') ?>ບັນທຶກສີ</button>
    </div>
  </form>

  <?php if ($saved !== []): ?>
    <section class="ibx-box" aria-labelledby="th-saved-title">
      <div class="gpe-box__head">
        <h2 id="th-saved-title"><?= $i('bookmark') ?>ຕີມທີ່ບັນທຶກໄວ້<span class="gpe-box__count"><?= e($n(count($saved))) ?></span></h2>
        <span class="gpe-box__note">ກົດ "ໂຫຼດ" ແລ້ວກົດ "ບັນທຶກສີ" ເພື່ອໃຊ້</span>
      </div>
      <ul class="ibx-list">
        <?php foreach ($saved as $k => $s): ?>
          <li class="ibx-row thm-saved">
            <span class="thm-saved__dots" aria-hidden="true">
              <?php foreach (array_keys(T::SCOPES) as $scope): ?><i data-color="<?= e($brand($s, $scope)) ?>"></i><?php endforeach; ?>
            </span>
            <span class="ibx-row__text">
              <span class="ibx-row__top"><b><?= e((string) ($s['name'] ?? '')) ?></b></span>
              <span class="ibx-row__meta"><span><?= e((string) ($s['at'] ?? '')) ?></span><span aria-hidden="true">·</span><span><?= e($n(array_sum(array_map(static fn (string $sc): int => count((array) ($s[$sc] ?? [])), array_keys(T::SCOPES))))) ?> ສີທີ່ປ່ຽນ</span></span>
            </span>
            <button type="button" class="btn-soft" data-load-saved="<?= e($k) ?>" data-js hidden>ໂຫຼດ</button>
            <form class="ibx-row__acts" method="post" action="/admin/theme/delete" data-confirm="ລຶບຕີມນີ້?">
              <?= Csrf::field() ?>
              <input type="hidden" name="name" value="<?= e((string) ($s['name'] ?? '')) ?>">
              <button type="submit" aria-label="ລຶບ “<?= e((string) ($s['name'] ?? '')) ?>”" title="ລຶບ"><?= $i('trash') ?></button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>
</div>
<script src="/admin/assets/theme.js?v=<?= substr((string) @filemtime(dirname(__DIR__, 4) . '/public/admin/assets/theme.js'), -6) ?>" defer></script>
