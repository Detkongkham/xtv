<?php
/** @var array $s settings in the stored shape @var array $errors @var int $divisionCount @var int $productCount */
use App\Core\Csrf;
use App\Core\View;
use App\Http\Admin\SettingController as SC;

$err = static fn (string $f): string => isset($errors[$f]) ? '<span class="field__err" id="' . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$inv = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-err"' : '';
$address = [];
foreach ($s['address'] as $l => $text) {
    $address['address_' . $l] = $text;
}
?>
<?php if ($errors !== []): ?>
  <p class="flash flash--error" role="alert">ກະລຸນາແກ້ໄຂຊ່ອງທີ່ໝາຍໄວ້ຂ້າງລຸ່ມ (ເບິ່ງທຸກແທັບພາສາຂອງທີ່ຢູ່).</p>
<?php endif; ?>
<form class="grid2" method="post" action="/admin/settings">
  <?= Csrf::field() ?>
  <div class="stack">
    <div class="card stack">
      <h2>ຂໍ້ມູນຕິດຕໍ່</h2>
      <p class="hint">ຂຶ້ນທຸກໜ້າ: ຫົວເວັບ, ທ້າຍເວັບ, ປຸ່ມລອຍ, ໜ້າຕິດຕໍ່ ແລະ ອີເມວຕອບກັບອັດຕະໂນມັດ.</p>
      <div class="field">
        <label for="tel">ເບີໂທ (ສຳນັກງານ) <em>*</em></label>
        <input id="tel" name="tel" type="tel" required maxlength="30" value="<?= e($s['contact']['tel']) ?>"<?= $inv('tel') ?>><?= $err('tel') ?>
      </div>
      <div class="field">
        <label for="mobile">ມືຖື / ສາຍດ່ວນ <em>*</em></label>
        <input id="mobile" name="mobile" type="tel" required maxlength="30" value="<?= e($s['contact']['mobile']) ?>"<?= $inv('mobile') ?>><?= $err('mobile') ?>
      </div>
      <div class="field">
        <label for="fax">ແຟັກ</label>
        <input id="fax" name="fax" type="tel" maxlength="30" value="<?= e($s['contact']['fax']) ?>"<?= $inv('fax') ?>><?= $err('fax') ?>
        <span class="hint">ຫວ່າງ = ບໍ່ສະແດງ.</span>
      </div>
      <div class="field">
        <label for="email">ອີເມວ <em>*</em></label>
        <input id="email" name="email" type="email" required maxlength="190" value="<?= e($s['contact']['email']) ?>"<?= $inv('email') ?>><?= $err('email') ?>
      </div>
      <div class="field">
        <label for="whatsapp">ເບີ WhatsApp <em>*</em></label>
        <input id="whatsapp" name="whatsapp" inputmode="numeric" required maxlength="20" value="<?= e($s['contact']['whatsapp']) ?>"<?= $inv('whatsapp') ?>><?= $err('whatsapp') ?>
        <span class="hint">ລະຫັດປະເທດ + ເບີ, ບໍ່ມີ 0 ຂ້າງໜ້າ ແລະ ບໍ່ມີຍະຫວ່າງ: 8562055892929.</span>
      </div>
      <div class="field">
        <label for="facebook">ລິ້ງເພຈ Facebook</label>
        <input id="facebook" name="facebook" type="url" maxlength="300" placeholder="https://www.facebook.com/..." value="<?= e($s['contact']['facebook']) ?>"<?= $inv('facebook') ?>><?= $err('facebook') ?>
        <span class="hint">ຫວ່າງ = ບໍ່ສະແດງປຸ່ມ Facebook.</span>
      </div>
    </div>
    <div class="card stack">
      <h2>ທີ່ຢູ່ສຳນັກງານໃຫຍ່</h2>
      <?= View::render('admin/partials/lang-fields', ['fields' => SC::ADDRESS, 'row' => $address, 'errors' => $errors]) ?>
      <div class="field">
        <label for="map_query">ສະຖານທີ່ໃນ Google Maps <em>*</em></label>
        <input id="map_query" name="map_query" required maxlength="200" value="<?= e($s['map']['query']) ?>"<?= $inv('map_query') ?>><?= $err('map_query') ?>
        <span class="hint">ຄຳທີ່ພິມຄົ້ນຫາໃນ Google Maps ແລ້ວເຫັນສຳນັກງານ, ເຊັ່ນ ທີ່ຢູ່ພາສາອັງກິດ ຫຼື ພິກັດ 17.9757,102.6331.</span>
      </div>
    </div>
  </div>
  <div class="stack">
    <div class="card stack">
      <h2>ສະຖິຕິໜ້າຫຼັກ</h2>
      <div class="field">
        <label for="founded">ປີກໍ່ຕັ້ງ <em>*</em></label>
        <input id="founded" name="founded" inputmode="numeric" required maxlength="4" value="<?= e($s['stats']['founded']) ?>"<?= $inv('founded') ?>><?= $err('founded') ?>
      </div>
      <div class="field">
        <label for="experience">ປະສົບການ (ປີ) <em>*</em></label>
        <input id="experience" name="experience" inputmode="numeric" required maxlength="3" value="<?= e($s['stats']['experience']) ?>"<?= $inv('experience') ?>><?= $err('experience') ?>
        <span class="hint">ໜ້າເວັບສະແດງເປັນ "<?= e($s['stats']['experience']) ?>+".</span>
      </div>
      <p class="hint">ຂະແໜງ <b><?= e($divisionCount) ?></b> ແລະ ສິນຄ້າ <b><?= e($productCount) ?></b>: ນັບເອງຈາກສິ່ງທີ່ສະແດງໃນເວັບ.</p>
    </div>
    <div class="card stack">
      <h2>ອີເມວແຈ້ງເຕືອນ</h2>
      <div class="field">
        <label for="notify_emails">ຮັບແຈ້ງເຕືອນເມື່ອມີຂໍ້ຄວາມຈາກຟອມ <em>*</em></label>
        <textarea id="notify_emails" name="notify_emails" rows="3" required<?= $inv('notify_emails') ?>><?= e(implode("\n", $s['notify_emails'])) ?></textarea><?= $err('notify_emails') ?>
        <span class="hint">ແຖວລະ 1 ອີເມວ. ປ່ຽນແລ້ວໃຊ້ໄດ້ທັນທີ, ບໍ່ຕ້ອງເຜີຍແຜ່.</span>
      </div>
    </div>
    <div class="card stack">
      <h2>ອື່ນໆ</h2>
      <div class="field">
        <label for="analytics_id">Google Analytics ID</label>
        <input id="analytics_id" name="analytics_id" maxlength="20" placeholder="G-XXXXXXXXXX" value="<?= e($s['analytics_id']) ?>"<?= $inv('analytics_id') ?>><?= $err('analytics_id') ?>
        <span class="hint">ຫວ່າງ = ບໍ່ໃຊ້ Analytics.</span>
      </div>
      <label class="check"><input type="checkbox" name="noindex" value="1"<?= $s['noindex'] ? ' checked' : '' ?>><span>ບໍ່ໃຫ້ Google ເກັບເວັບ (noindex)</span></label>
      <p class="hint">ເປີດໄວ້ຈົນກວ່າຈະ go-live ຢູ່ <code>xtklaos.com</code>. ປິດແລ້ວ Google ເລີ່ມເກັບໜ້າເວັບຫຼັງເຜີຍແຜ່.</p>
    </div>
    <div class="btns"><button class="btn" type="submit">ບັນທຶກ</button></div>
  </div>
</form>
