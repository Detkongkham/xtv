<?php
/**
 * @var array $s @var array $errors @var array $countries @var array $products @var array $unlinked @var bool $published
 */
use App\Core\Csrf;
use App\Http\Admin\SupplierController as SC;
use App\Repository\SupplierRepository;
use App\Service\MediaService;

$isNew = (int) $s['id'] === 0;
$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
$byDivision = [];
foreach ($unlinked as $p) {
    $byDivision[$p['division_name']][] = $p;
}
?>
<p class="crumb"><a href="/admin/suppliers">← ຜູ້ສະໜອງ</a></p>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ.</p>
<?php endif; ?>

<form class="grid2" method="post" enctype="multipart/form-data" action="<?= $isNew ? '/admin/suppliers/new' : '/admin/suppliers/' . e($s['id']) ?>">
  <?= Csrf::field() ?>
  <div class="card stack">
    <div class="field">
      <label for="name">ຊື່ຜູ້ສະໜອງ <em>*</em></label>
      <input id="name" name="name" required maxlength="160" value="<?= e($s['name']) ?>"<?= $inv('name') ?>><?= $err('name') ?>
      <span class="hint">ຊື່ຍີ່ຫໍ້ ຄືກັນທຸກພາສາ.</span>
    </div>
    <div class="field">
      <label for="slug">ຊື່ໃນ URL</label>
      <input id="slug" name="slug" maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= e($s['slug']) ?>"<?= $inv('slug') ?>><?= $err('slug') ?>
      <span class="hint">ໜ້າເວັບ: supplier-<b><?= e($s['slug'] ?: '…') ?></b>.html. ປ່ອຍຫວ່າງ = ສ້າງຈາກຊື່.<?= $published ? ' ຖ້າປ່ຽນ, URL ເກົ່າຈະພາໄປ URL ໃໝ່ເອງ.' : '' ?></span>
    </div>
    <div class="field">
      <label for="country_code">ປະເທດ <em>*</em></label>
      <select id="country_code" name="country_code" required<?= $inv('country_code') ?>>
        <option value="">— ເລືອກ —</option>
        <?php foreach ($countries as $code => $label): ?>
          <option value="<?= e($code) ?>"<?= $s['country_code'] === $code ? ' selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select><?= $err('country_code') ?>
    </div>
    <div class="field">
      <label for="website_url">ເວັບໄຊຂອງຜູ້ສະໜອງ</label>
      <input id="website_url" name="website_url" type="url" maxlength="255" placeholder="https://" value="<?= e($s['website_url'] ?? '') ?>"<?= $inv('website_url') ?>><?= $err('website_url') ?>
    </div>
    <fieldset class="stack">
      <legend>ກ່ຽວກັບຜູ້ສະໜອງ</legend>
      <p class="hint">ສະແດງໃນກາດຜູ້ສະໜອງຫຼັກ ແລະ ໜ້າລາຍລະອຽດ. ພາສາໄທ ຫຼື ຈີນ ທີ່ຫວ່າງ ຈະໃຊ້ພາສາອັງກິດແທນ.</p>
      <?php foreach (SupplierRepository::LANGS as $l): ?>
        <div class="field">
          <label for="about_<?= $l ?>"><?= e(SC::LANG_NAMES[$l]) ?></label>
          <textarea id="about_<?= $l ?>" name="about_<?= $l ?>" rows="3" maxlength="3000" lang="<?= $l ?>"<?= $inv('about_' . $l) ?>><?= e($s['about_' . $l] ?? '') ?></textarea><?= $err('about_' . $l) ?>
        </div>
      <?php endforeach; ?>
    </fieldset>
  </div>

  <div class="stack">
    <div class="card stack">
      <h2>ໂລໂກ້</h2>
      <?php if ($s['logo_path']): ?>
        <div class="logo-preview">
          <span class="logo-box logo-box--lg"><img src="/<?= e($s['logo_path']) ?>" alt="<?= e($s['name']) ?> (ພື້ນຂາວ)"></span>
          <span class="logo-box logo-box--lg logo-box--dark"><img src="/<?= e($s['logo_path']) ?>" alt="<?= e($s['name']) ?> (ພື້ນເຂັ້ມ)"></span>
        </div>
        <label class="check"><input type="checkbox" name="remove_logo" value="1"><span>ເອົາໂລໂກ້ອອກ</span></label>
      <?php endif; ?>
      <div class="field">
        <label for="logo"><?= $s['logo_path'] ? 'ປ່ຽນໂລໂກ້' : 'ອັບໂລໂກ້' ?></label>
        <input id="logo" name="logo" type="file" accept="image/png,image/webp"<?= $inv('logo') ?>><?= $err('logo') ?>
        <span class="hint">PNG ຫຼື WebP ພື້ນໂປ່ງໃສ, ກວ້າງຢ່າງໜ້ອຍ <?= MediaService::LOGO_MIN_WIDTH ?> px. ບໍ່ມີໂລໂກ້ = ສະແດງຊື່ແທນ.</span>
      </div>
      <label class="check"><input type="checkbox" name="logo_on_dark" value="1"<?= (int) $s['logo_on_dark'] ? ' checked' : '' ?>><span>ໂລໂກ້ສີຂາວ: ສະແດງເທິງພື້ນເຂັ້ມ</span></label>
    </div>

    <div class="card stack">
      <h2>ການສະແດງ</h2>
      <label class="check"><input type="checkbox" name="is_visible" value="1"<?= (int) $s['is_visible'] ? ' checked' : '' ?>><span>ສະແດງໃນໜ້າເວັບ</span></label>
      <div class="field">
        <label class="check"><input type="checkbox" name="is_featured" value="1"<?= (int) $s['is_featured'] ? ' checked' : '' ?><?= $inv('is_featured') ?>><span>ຜູ້ສະໜອງຫຼັກ (ສະແດງໃນ mega menu ແລະ ກາດໃຫຍ່, ສູງສຸດ <?= SupplierRepository::MAX_FEATURED ?>)</span></label><?= $err('is_featured') ?>
      </div>
    </div>

    <div class="card stack">
      <h2>ຂໍ້ມູນທາງເລືອກ</h2>
      <p class="hint">ໃສ່ສະເພາະເມື່ອລູກຄ້າຢືນຢັນແລ້ວ. ຫວ່າງ = ບໍ່ສະແດງ.</p>
      <div class="field">
        <label for="partnership">ປະເພດການເປັນຕົວແທນ</label>
        <select id="partnership" name="partnership"<?= $inv('partnership') ?>>
          <option value="">— ບໍ່ສະແດງ —</option>
          <?php foreach (SC::PARTNERSHIP_NAMES as $k => $label): ?>
            <option value="<?= e($k) ?>"<?= ($s['partnership'] ?? '') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select><?= $err('partnership') ?>
      </div>
      <div class="field">
        <label for="since_year">ຮ່ວມງານຕັ້ງແຕ່ປີ</label>
        <input id="since_year" name="since_year" type="number" min="1950" max="<?= date('Y') ?>" inputmode="numeric" value="<?= e($s['since_year'] ?? '') ?>"<?= $inv('since_year') ?>><?= $err('since_year') ?>
      </div>
    </div>

    <div class="btns"><button class="btn" type="submit"><?= $isNew ? 'ເພີ່ມຜູ້ສະໜອງ' : 'ບັນທຶກ' ?></button><a class="btn btn--ghost" href="/admin/suppliers">ຍົກເລີກ</a></div>
  </div>
</form>

<?php if (!$isNew): ?>
  <section class="card" id="products">
    <div class="card__head"><h2>ສິນຄ້າທີ່ XTK ຂາຍຈາກ <?= e($s['name']) ?> (<?= e(count($products)) ?>)</h2></div>
    <?php if ($products === []): ?>
      <p class="empty">ຍັງບໍ່ໄດ້ເຊື່ອມສິນຄ້າ. ເລືອກສິນຄ້າຂ້າງລຸ່ມ.</p>
    <?php else: ?>
      <p class="hint">"ຫຼັກ" = ຜູ້ສະໜອງທີ່ສະແດງກ່ອນ ໃນໜ້າສິນຄ້າ ແລະ ກາດສິນຄ້າ.</p>
      <ul class="list">
        <?php foreach ($products as $p): ?>
          <li>
            <span class="list__text"><b><?= e($p['name']) ?></b>
              <small><?= e($p['division_name'] . ' › ' . $p['group_name']) ?><?= (int) $p['is_visible'] ? '' : ' · ເຊື່ອງຢູ່' ?><?= (int) $p['supplier_count'] > 1 ? ' · ' . e($p['supplier_count']) . ' ຜູ້ສະໜອງ' : '' ?></small></span>
            <span class="btns">
              <?php if ((int) $p['is_primary']): ?>
                <span class="tag tag--done">ຫຼັກ</span>
              <?php else: ?>
                <form method="post" action="/admin/suppliers/<?= e($s['id']) ?>/primary"><?= Csrf::field() ?><input type="hidden" name="product_id" value="<?= e($p['id']) ?>"><button class="btn btn--ghost btn--sm" type="submit">ຕັ້ງເປັນຫຼັກ</button></form>
              <?php endif; ?>
              <form method="post" action="/admin/suppliers/<?= e($s['id']) ?>/products/remove" data-confirm="ເອົາ <?= e($p['name']) ?> ອອກຈາກຜູ້ສະໜອງນີ້?"><?= Csrf::field() ?><input type="hidden" name="product_id" value="<?= e($p['id']) ?>"><button class="btn btn--ghost btn--sm" type="submit" aria-label="ເອົາ <?= e($p['name']) ?> ອອກ">ເອົາອອກ</button></form>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <form class="card stack" method="post" action="/admin/suppliers/<?= e($s['id']) ?>/products">
    <?= Csrf::field() ?>
    <h2>ເພີ່ມສິນຄ້າ</h2>
    <?php if ($unlinked === []): ?>
      <p class="empty">ເຊື່ອມສິນຄ້າຄົບທຸກລາຍການແລ້ວ.</p>
    <?php else: ?>
      <div class="field">
        <label for="pick-q">ຄົ້ນຫາສິນຄ້າ</label>
        <input id="pick-q" type="search" data-filter-for="pick" placeholder="ພິມຊື່ສິນຄ້າ ຫຼື ກຸ່ມ">
      </div>
      <div class="picker" id="pick">
        <?php foreach ($byDivision as $division => $items): ?>
          <fieldset>
            <legend><?= e($division) ?></legend>
            <?php foreach ($items as $p): ?>
              <label class="check" data-text="<?= e(mb_strtolower($p['name'] . ' ' . $p['group_name'])) ?>"><input type="checkbox" name="product_ids[]" value="<?= e($p['id']) ?>"><span><?= e($p['name']) ?> <small class="muted"><?= e($p['group_name']) ?></small></span></label>
            <?php endforeach; ?>
          </fieldset>
        <?php endforeach; ?>
      </div>
      <div class="btns"><button class="btn" type="submit">ເພີ່ມສິນຄ້າທີ່ເລືອກ</button></div>
    <?php endif; ?>
  </form>

  <form class="card danger" method="post" action="/admin/suppliers/<?= e($s['id']) ?>/delete" data-confirm="ລຶບຜູ້ສະໜອງ <?= e($s['name']) ?>? ສິນຄ້າຈະບໍ່ຖືກລຶບ.">
    <?= Csrf::field() ?>
    <h2>ລຶບຜູ້ສະໜອງ</h2>
    <p>ການເຊື່ອມກັບສິນຄ້າຈະຖືກລຶບ, ສິນຄ້າບໍ່ຖືກລຶບ.<?= $published ? ' ໜ້າເກົ່າຂອງຜູ້ສະໜອງນີ້ຈະພາໄປໜ້າລວມຜູ້ສະໜອງ.' : '' ?></p>
    <div class="btns"><button class="btn btn--danger" type="submit">ລຶບ</button></div>
  </form>
<?php endif; ?>
