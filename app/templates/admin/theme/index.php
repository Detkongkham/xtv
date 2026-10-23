<?php
/**
 * /admin/theme. Without JS: one HEX field per color (empty = follows its base color). theme.js adds the RGB editor,
 * the live preview, the readability checks, ready-made themes and import/export.
 * @var array $theme {site, admin} @var array $saved @var array $history @var array $warnings {site, admin}
 */
use App\Core\Csrf;
use App\Service\ThemeService as T;

$data = T::clientData() + ['theme' => $theme, 'saved' => $saved, 'history' => $history];
$pages = ['index' => 'ໜ້າຫຼັກ', 'about' => 'ກ່ຽວກັບ', 'products' => 'ສິນຄ້າ', 'solutions' => 'Solutions', 'suppliers' => 'ຜູ້ສະໜອງ', 'contact' => 'ຕິດຕໍ່', 'faq' => 'FAQ'];
$channels = ['r' => ['R', 'ສີແດງ', 255], 'g' => ['G', 'ສີຂຽວ', 255], 'b' => ['B', 'ສີນ້ຳເງິນ', 255], 'a' => ['A', 'ຄວາມທຶບ (%)', 100]];
?>
<script type="application/json" id="theme-data"><?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) ?></script>
<p class="hint th-intro">ເລືອກສີຂອງແຕ່ລະສ່ວນດ້ວຍແຖບ R G B. ສີທີ່ບໍ່ໄດ້ປ່ຽນຈະ <b>ຕາມສີຫຼັກ</b> ຂອງມັນ (ເຊັ່ນ ປຸ່ມ ຕາມ ແບຣນ ຫຼັກ). ສີ admin (ສະຫວ່າງ ແລະ ມືດ) ມີຜົນທັນທີທີ່ບັນທຶກ; ເລືອກໂໝດມືດໄດ້ທີ່ເມນູບັນຊີ ມຸມຂວາເທິງ; ສີໜ້າເວັບຂຶ້ນເວັບຫຼັງກົດ "ເຜີຍແຜ່".</p>

<form method="post" action="/admin/theme" class="th" id="theme-form" data-theme-form>
  <?= Csrf::field() ?>
  <div class="th-bar">
    <div class="th-tabs" role="tablist" aria-label="ສ່ວນທີ່ຈະປັບ">
      <?php foreach (T::SCOPES as $scope => $label): ?>
        <button type="button" role="tab" id="tab-<?= $scope ?>" aria-controls="panel-<?= $scope ?>" aria-selected="<?= $scope === 'site' ? 'true' : 'false' ?>" data-scope-tab="<?= $scope ?>">
          <?= e($label) ?> <span class="th-count" data-count="<?= $scope ?>"><?= count($theme[$scope]) ?></span>
        </button>
      <?php endforeach; ?>
    </div>
    <div class="th-tools" data-js hidden>
      <label class="sr" for="th-load">ໂຫຼດຕີມ</label>
      <select id="th-load" data-load>
        <option value="">ໂຫຼດຕີມ…</option>
      </select>
      <button type="button" class="btn btn--ghost btn--sm" data-export>Export</button>
      <label class="btn btn--ghost btn--sm th-import">Import<input type="file" accept="application/json,.json" data-import class="sr"></label>
      <button type="button" class="btn btn--ghost btn--sm" data-reset-scope>ຄືນຄ່າເດີມທັງແທັບ</button>
    </div>
  </div>

  <div class="th-grid">
    <div class="th-list">
      <div class="field th-search" data-js hidden>
        <label for="th-q" class="sr">ຄົ້ນຫາສີ</label>
        <input id="th-q" type="search" placeholder="ຄົ້ນຫາ: ປຸ່ມ, footer, hero…" data-search-colors autocomplete="off">
      </div>
      <?php foreach (T::SCOPES as $scope => $label): ?>
        <div class="th-panel" id="panel-<?= $scope ?>" role="tabpanel" aria-labelledby="tab-<?= $scope ?>" data-panel="<?= $scope ?>"<?= $scope === 'site' ? '' : ' data-off' ?>>
          <?php foreach (T::groups($scope) as $group => $tokens): ?>
            <fieldset class="th-group">
              <legend><?= e($group) ?></legend>
              <?php foreach ($tokens as $token => [$name, $default]): ?>
                <?php $id = 'c-' . $scope . '-' . $token; $val = $theme[$scope][$token] ?? ''; ?>
                <div class="th-row" data-token="<?= e($token) ?>" data-text="<?= e(mb_strtolower($name . ' ' . $token . ' ' . $group)) ?>"<?= $val !== '' ? ' data-set' : '' ?>>
                  <button type="button" class="th-row__pick" data-pick aria-describedby="<?= $id ?>-val">
                    <span class="th-sw" aria-hidden="true"><i></i></span>
                    <span class="th-row__name"><?= e($name) ?></span>
                    <span class="th-row__val" id="<?= $id ?>-val"><?= e($val !== '' ? $val : 'ຕາມ ' . (is_array($default) ? $default[0] . ' ' . $default[1] . '%' : $default)) ?></span>
                  </button>
                  <label class="sr" for="<?= $id ?>"><?= e($name) ?> (HEX)</label>
                  <input class="th-row__hex" id="<?= $id ?>" name="<?= $scope ?>[<?= e($token) ?>]" value="<?= e($val) ?>" placeholder="ຕາມຄ່າເດີມ" maxlength="9" pattern="#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?" spellcheck="false" autocomplete="off">
                </div>
              <?php endforeach; ?>
            </fieldset>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="th-side">
      <section class="card th-ed" aria-labelledby="th-ed-title" data-ed hidden>
        <div class="th-ed__head">
          <span class="th-sw th-sw--lg" aria-hidden="true"><i data-ed-sw></i></span>
          <div>
            <h2 id="th-ed-title" data-ed-label>ເລືອກສີ</h2>
            <p class="hint"><code data-ed-token></code></p>
          </div>
        </div>
        <label class="th-follow"><input type="checkbox" data-ed-follow> <span data-ed-follow-text>ຕາມສີຫຼັກ</span></label>
        <?php foreach ($channels as $ch => [$short, $label, $max]): ?>
          <div class="th-sl" data-ch="<?= $ch ?>">
            <label for="th-<?= $ch ?>" title="<?= e($label) ?>"><?= $short ?></label>
            <input type="range" id="th-<?= $ch ?>" min="0" max="<?= $max ?>" step="1" data-ed-range="<?= $ch ?>" aria-label="<?= e($label) ?>">
            <input type="number" min="0" max="<?= $max ?>" step="1" inputmode="numeric" data-ed-num="<?= $ch ?>" aria-label="<?= e($label) ?> ຕົວເລກ">
          </div>
        <?php endforeach; ?>
        <div class="th-ed__codes">
          <div class="field"><label for="th-hex">HEX</label><input id="th-hex" data-ed-hex maxlength="9" spellcheck="false" autocomplete="off"></div>
          <div class="field"><label for="th-rgb">RGB</label><input id="th-rgb" data-ed-rgb spellcheck="false" autocomplete="off" placeholder="rgb(47, 54, 142)"></div>
        </div>
        <div class="th-ed__tools">
          <button type="button" class="btn btn--ghost btn--sm" data-ed-drop hidden>ດູດສີຈາກໜ້າຈໍ</button>
          <button type="button" class="btn btn--ghost btn--sm" data-ed-scale hidden>ສ້າງລະດັບສີແບຣນຈາກສີນີ້</button>
        </div>
        <div class="th-recent" data-ed-recent-box hidden>
          <span class="hint">ສີທີ່ໃຊ້ຫຼ້າສຸດ</span>
          <div data-ed-recent></div>
        </div>
        <ul class="th-pairs" data-ed-pairs></ul>
      </section>

      <section class="card th-check" aria-labelledby="th-check-title">
        <h2 id="th-check-title">ຄວາມອ່ານງ່າຍ</h2>
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

      <section class="card th-prev" aria-label="ຕົວຢ່າງໜ້າເວັບ" data-prev hidden>
        <div class="th-prev__bar">
          <label class="sr" for="th-page">ໜ້າທີ່ຈະເບິ່ງ</label>
          <select id="th-page" data-prev-page>
            <?php foreach ($pages as $slug => $label): ?><option value="<?= $slug ?>"><?= e($label) ?></option><?php endforeach; ?>
          </select>
          <div class="seg" role="group" aria-label="ຂະໜາດຈໍ">
            <button type="button" data-prev-size="desktop" aria-pressed="true">ຄອມ</button>
            <button type="button" data-prev-size="mobile" aria-pressed="false">ມືຖື</button>
          </div>
        </div>
        <div class="th-prev__frame" data-prev-box>
          <iframe title="ຕົວຢ່າງໜ້າເວັບ" data-prev-frame loading="lazy"></iframe>
        </div>
        <p class="hint">ຕົວຢ່າງປ່ຽນສີທັນທີ ກ່ອນບັນທຶກ.</p>
      </section>

      <section class="card th-admin-note" data-admin-note hidden>
        <h2>ຕົວຢ່າງ admin</h2>
        <p class="hint">ໜ້ານີ້ທັງໝົດປ່ຽນເປັນສີ admin ທີ່ກຳລັງປັບ.</p>
        <div class="btns"><span class="btn btn--sm">ປຸ່ມຫຼັກ</span><span class="btn btn--ghost btn--sm">ປຸ່ມຂອບ</span></div>
        <p class="flash flash--ok">ບັນທຶກແລ້ວ.</p>
        <p class="flash flash--error">ມີຂໍ້ຜິດພາດ.</p>
        <p><span class="tag tag--done">ສຳເລັດ</span> <span class="tag tag--warn">ລໍຖ້າ</span></p>
      </section>
    </div>
  </div>

  <div class="th-save">
    <div class="field">
      <label for="th-name">ບັນທຶກເປັນຕີມ (ບໍ່ບັງຄັບ)</label>
      <input id="th-name" name="save_as" maxlength="60" placeholder="ຊື່ຕີມ ເຊັ່ນ ສີບຸນປີໃໝ່">
    </div>
    <button type="submit" class="btn">ບັນທຶກສີ</button>
  </div>
</form>

<?php if ($saved !== []): ?>
  <section class="card th-saved" aria-labelledby="th-saved-title">
    <h2 id="th-saved-title">ຕີມທີ່ບັນທຶກໄວ້</h2>
    <p class="hint">ໂຫຼດຕີມຈາກເມນູ "ໂຫຼດຕີມ…" ດ້ານເທິງ ແລ້ວກົດ "ບັນທຶກສີ".</p>
    <ul class="th-saved__list">
      <?php foreach ($saved as $s): ?>
        <li>
          <span><b><?= e((string) ($s['name'] ?? '')) ?></b> <small class="muted"><?= e((string) ($s['at'] ?? '')) ?></small></span>
          <form method="post" action="/admin/theme/delete" data-confirm="ລຶບຕີມນີ້?">
            <?= Csrf::field() ?>
            <input type="hidden" name="name" value="<?= e((string) ($s['name'] ?? '')) ?>">
            <button type="submit" class="btn btn--ghost btn--sm">ລຶບ</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
<?php endif; ?>
<script src="/admin/assets/theme.js?v=<?= substr((string) @filemtime(dirname(__DIR__, 4) . '/public/admin/assets/theme.js'), -6) ?>" defer></script>
