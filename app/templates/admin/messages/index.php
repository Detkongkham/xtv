<?php
/** @var array $filters @var array $rows @var int $total @var int $page @var int $pages @var array $query */
use App\Http\Admin\MessageController as MC;

$link = static fn (array $extra): string => '/admin/messages?' . http_build_query(array_merge($query, $extra));
$statusOptions = ['' => 'ທັງໝົດ (ບໍ່ລວມ spam)'] + MC::STATUS_NAMES + ['all' => 'ທັງໝົດ (ລວມ spam)'];
?>
<form class="card filters" method="get" action="/admin/messages" role="search">
  <div class="field">
    <label for="q">ຄົ້ນຫາ</label>
    <input id="q" name="q" type="search" value="<?= e($filters['q']) ?>" placeholder="ຊື່, ອີເມວ, ເບີໂທ, ອົງກອນ">
  </div>
  <div class="field">
    <label for="status">ສະຖານະ</label>
    <select id="status" name="status">
      <?php foreach ($statusOptions as $k => $label): ?>
        <option value="<?= e($k) ?>"<?= $filters['status'] === (string) $k ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="subject">ຫົວຂໍ້</label>
    <select id="subject" name="subject">
      <option value="">ທັງໝົດ</option>
      <?php foreach (MC::SUBJECT_NAMES as $k => $label): ?>
        <option value="<?= e($k) ?>"<?= $filters['subject'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="from">ຈາກວັນທີ</label>
    <input id="from" name="from" type="date" value="<?= e($filters['from']) ?>">
  </div>
  <div class="field">
    <label for="to">ຫາວັນທີ</label>
    <input id="to" name="to" type="date" value="<?= e($filters['to']) ?>">
  </div>
  <div class="filters__btns">
    <button class="btn" type="submit">ກອງ</button>
    <?php if ($query !== []): ?><a class="btn btn--ghost" href="/admin/messages">ລ້າງ</a><?php endif; ?>
  </div>
</form>

<div class="bar">
  <p><b><?= e($total) ?></b> ຂໍ້ຄວາມ</p>
  <a class="btn btn--ghost btn--sm" href="<?= e('/admin/messages/export?' . http_build_query($query)) ?>">ດາວໂຫຼດ CSV</a>
</div>

<?php if ($rows === []): ?>
  <div class="card empty"><p>ບໍ່ພົບຂໍ້ຄວາມຕາມເງື່ອນໄຂນີ້.</p></div>
<?php else: ?>
  <div class="card table-wrap">
    <table class="table">
      <thead>
        <tr><th scope="col">ວັນທີ</th><th scope="col">ຊື່</th><th scope="col">ອົງກອນ</th><th scope="col">ຫົວຂໍ້</th><th scope="col">ພາສາ</th><th scope="col">ສະຖານະ</th><th scope="col">ຜູ້ຮັບຜິດຊອບ</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
            <td data-label="ວັນທີ"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
            <td data-label="ຊື່"><a href="/admin/messages/<?= e($r['id']) ?>"><?= e($r['name']) ?></a>
              <?php if ($r['mail_error']): ?><span class="tag tag--error" title="<?= e($r['mail_error']) ?>">ອີເມວບໍ່ສຳເລັດ</span><?php endif; ?>
            </td>
            <td data-label="ອົງກອນ"><?= e($r['org'] ?? '—') ?></td>
            <td data-label="ຫົວຂໍ້"><?= e(MC::SUBJECT_NAMES[$r['subject']] ?? $r['subject']) ?></td>
            <td data-label="ພາສາ"><?= e(MC::LANG_NAMES[$r['lang']] ?? $r['lang']) ?></td>
            <td data-label="ສະຖານະ"><span class="tag tag--<?= e($r['status']) ?>"><?= e(MC::STATUS_NAMES[$r['status']] ?? $r['status']) ?></span></td>
            <td data-label="ຜູ້ຮັບຜິດຊອບ"><?= e($r['assigned_name'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="ແບ່ງໜ້າ">
      <?php if ($page > 1): ?><a href="<?= e($link(['page' => $page - 1])) ?>" rel="prev">← ກ່ອນໜ້າ</a><?php endif; ?>
      <span>ໜ້າ <?= e($page) ?> / <?= e($pages) ?></span>
      <?php if ($page < $pages): ?><a href="<?= e($link(['page' => $page + 1])) ?>" rel="next">ຕໍ່ໄປ →</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
