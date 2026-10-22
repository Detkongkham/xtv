<?php
/** @var array $m @var array $notes @var array $users */
use App\Core\Csrf;
use App\Http\Admin\MessageController as MC;

$telHref = preg_replace('/[^\d+]/', '', $m['phone']);
// WhatsApp needs the international number: Lao numbers written as 020… / 021… become 85620… / 85621…
$waNumber = preg_replace('/\D/', '', $m['phone']);
if (str_starts_with($waNumber, '0')) {
    $waNumber = '856' . substr($waNumber, 1);
}
$replySubject = 'Re: ' . (MC::SUBJECT_NAMES[$m['subject']] ?? $m['subject']) . ' — XTK';
?>
<p class="crumb"><a href="/admin/messages">← ກ່ອງຂໍ້ຄວາມ</a></p>

<?php if ($m['mail_error']): ?>
  <p class="flash flash--error" role="alert">ສົ່ງອີເມວບໍ່ສຳເລັດ: <?= e($m['mail_error']) ?></p>
<?php endif; ?>

<div class="grid2">
  <section class="card">
    <div class="card__head">
      <h2><?= e($m['name']) ?></h2>
      <span class="tag tag--<?= e($m['status']) ?>"><?= e(MC::STATUS_NAMES[$m['status']]) ?></span>
    </div>
    <dl class="dl">
      <dt>ອົງກອນ</dt><dd><?= e($m['org'] ?? '—') ?></dd>
      <dt>ເບີໂທ</dt><dd><a href="tel:<?= e($telHref) ?>"><?= e($m['phone']) ?></a></dd>
      <dt>ອີເມວ</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd>
      <dt>ຫົວຂໍ້</dt><dd><?= e(MC::SUBJECT_NAMES[$m['subject']] ?? $m['subject']) ?></dd>
      <dt>ພາສາ</dt><dd><?= e(MC::LANG_NAMES[$m['lang']] ?? $m['lang']) ?></dd>
      <dt>ວັນທີ</dt><dd><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></dd>
      <dt>ໜ້າທີ່ສົ່ງ</dt><dd class="break"><?= e($m['page_url'] ?? '—') ?></dd>
      <dt>IP</dt><dd><?= e($m['ip_text'] ?? '—') ?></dd>
    </dl>
    <h3 class="h3">ຂໍ້ຄວາມ</h3>
    <div class="body-text"><?= nl2br(e($m['body'])) ?></div>
    <div class="btns">
      <a class="btn" href="tel:<?= e($telHref) ?>">ໂທ</a>
      <a class="btn btn--ghost" href="mailto:<?= e($m['email']) ?>?subject=<?= e(rawurlencode($replySubject)) ?>">ຕອບທາງອີເມວ</a>
      <a class="btn btn--ghost" href="https://wa.me/<?= e($waNumber) ?>" target="_blank" rel="noopener">WhatsApp</a>
    </div>
  </section>

  <div class="stack">
    <section class="card">
      <h2 class="h3">ສະຖານະ ແລະ ຜູ້ຮັບຜິດຊອບ</h2>
      <form method="post" action="/admin/messages/<?= e($m['id']) ?>" class="stack">
        <?= Csrf::field() ?>
        <div class="field">
          <label for="status">ສະຖານະ</label>
          <select id="status" name="status">
            <?php foreach (MC::STATUS_NAMES as $k => $label): ?>
              <option value="<?= e($k) ?>"<?= $m['status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="assigned_to">ມອບໝາຍໃຫ້</label>
          <select id="assigned_to" name="assigned_to">
            <option value="0">— ບໍ່ມີ —</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= e($u['id']) ?>"<?= (int) $m['assigned_to'] === (int) $u['id'] ? ' selected' : '' ?>><?= e($u['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn" type="submit">ບັນທຶກ</button>
      </form>
    </section>

    <section class="card" id="notes">
      <h2 class="h3">ບັນທຶກພາຍໃນ</h2>
      <p class="muted">ລູກຄ້າບໍ່ເຫັນບັນທຶກເຫຼົ່ານີ້.</p>
      <?php if ($notes === []): ?>
        <p class="muted">ຍັງບໍ່ມີບັນທຶກ.</p>
      <?php else: ?>
        <ol class="notes">
          <?php foreach ($notes as $n): ?>
            <li><p><?= nl2br(e($n['note'])) ?></p><small><?= e($n['user_name'] ?? 'ຜູ້ໃຊ້ທີ່ຖືກລຶບ') ?> · <?= e(date('d/m/Y H:i', strtotime($n['created_at']))) ?></small></li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
      <form method="post" action="/admin/messages/<?= e($m['id']) ?>/notes" class="stack">
        <?= Csrf::field() ?>
        <div class="field">
          <label for="note">ເພີ່ມບັນທຶກ</label>
          <textarea id="note" name="note" rows="3" required maxlength="5000"></textarea>
        </div>
        <button class="btn btn--ghost" type="submit">ເພີ່ມບັນທຶກ</button>
      </form>
    </section>
  </div>
</div>
