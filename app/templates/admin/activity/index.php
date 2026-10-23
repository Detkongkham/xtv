<?php
/** @var array $rows @var array $filters @var array<int, string> $users @var int $total @var int $page @var int $pages */
use App\Repository\ActivityRepository as AR;

$link = static fn (array $extra): string => '/admin/activity?' . http_build_query(array_filter($extra + $filters, static fn ($v) => $v !== '' && $v !== null));
$yes = static fn ($v): string => $v ? 'ເປີດ' : 'ປິດ';
// details as short Lao text; the name is shown in its own column
$summary = static function (array $d) use ($yes): string {
    $out = [];
    foreach ($d as $k => $v) {
        $out[] = match ($k) {
            'visible' => $v ? 'ສະແດງ' : 'ເຊື່ອງ',
            'featured' => 'ແນະນຳ: ' . $yes($v),
            'status' => ['success' => 'ສຳເລັດ', 'failed' => 'ບໍ່ສຳເລັດ', 'draft' => 'ສະບັບຮ່າງ', 'published' => 'ເຜີຍແຜ່'][$v] ?? $v,
            'keys' => 'ຊ່ອງ: ' . implode(', ', (array) $v),
            'files' => $v . ' ໄຟລ໌',
            'rows' => $v . ' ແຖວ',
            'pages' => $v === null ? '' : $v . ' ໜ້າ',
            'build' => 'ຊຸດ ' . $v,
            'copy_id' => 'ສຳເນົາເປັນ #' . $v,
            'products' => count((array) $v) . ' ສິນຄ້າ',
            'product' => 'ສິນຄ້າ #' . $v,
            'role' => 'ສິດ: ' . $v,
            'active' => 'ບັນຊີ: ' . $yes($v),
            'password' => $v ? 'ປ່ຽນລະຫັດຜ່ານ' : '',
            default => '',
        };
    }
    return implode(' · ', array_filter($out));
};
?>
<form class="card filters" method="get" action="/admin/activity" role="search">
  <div class="field">
    <label for="user">ຜູ້ໃຊ້</label>
    <select id="user" name="user">
      <option value="">ທັງໝົດ</option>
      <?php foreach ($users as $id => $name): ?>
        <option value="<?= e($id) ?>"<?= $filters['user'] === (string) $id ? ' selected' : '' ?>><?= e($name) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="entity">ປະເພດ</label>
    <select id="entity" name="entity">
      <option value="">ທັງໝົດ</option>
      <?php foreach (AR::ENTITIES as $k => [$label]): ?>
        <option value="<?= e($k) ?>"<?= $filters['entity'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filters__btns">
    <button class="btn" type="submit">ກອງ</button>
    <?php if (array_filter($filters) !== []): ?><a class="btn btn--ghost" href="/admin/activity">ລ້າງ</a><?php endif; ?>
  </div>
</form>
<?php if ($rows === []): ?>
  <div class="card empty"><p>ຍັງບໍ່ມີລາຍການ.</p></div>
<?php else: ?>
  <p class="hint"><b><?= e($total) ?></b> ລາຍການ, ໃໝ່ສຸດຢູ່ເທິງ. ເກັບໄວ້ 365 ວັນ.</p>
  <div class="card table-wrap">
    <table class="table sup-table">
      <thead><tr><th scope="col">ເວລາ</th><th scope="col">ຜູ້ໃຊ້</th><th scope="col">ການກະທຳ</th><th scope="col">ລາຍການ</th><th scope="col">IP</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r):
            $type = AR::ENTITIES[(string) $r['entity']][0] ?? '';
            $name = $r['item_name'] ?? $r['logged_name'];
            $href = $r['item_name'] !== null ? '/admin/' . $r['entity'] . '/' . $r['entity_id']
                : ($r['entity'] === 'seo' && isset($r['details']['page']) ? '/admin/seo/' . $r['details']['page'] : null);
            $label = $name ?? ($r['entity_id'] !== null ? '#' . $r['entity_id'] : (isset($r['details']['page']) ? $r['details']['page'] . '.html' : ''));
            $ip = $r['ip'] === null ? '' : (string) @inet_ntop($r['ip']);
            $more = $summary($r['details']) ?>
          <tr>
            <td data-label="ເວລາ"><time datetime="<?= e(str_replace(' ', 'T', $r['created_at'])) ?>"><?= e(date('d/m/Y H:i', (int) strtotime($r['created_at']))) ?></time></td>
            <td data-label="ຜູ້ໃຊ້"><?= e($r['user_name'] ?? 'ຜູ້ໃຊ້ທີ່ຖືກລຶບ') ?></td>
            <td data-label="ການກະທຳ"><span><b><?= e(AR::ACTIONS[$r['action']] ?? $r['action']) ?></b><?= $type !== '' ? ' ' . e($type) : '' ?></span></td>
            <td data-label="ລາຍການ"><span>
              <?php if ($href !== null): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><?= e($label) ?><?= $name !== null && $r['item_name'] === null ? ' <small>(ຖືກລຶບແລ້ວ)</small>' : '' ?><?php endif; ?>
              <?php if ($more !== ''): ?><?= $label !== '' ? '<br>' : '' ?><small><?= e($more) ?></small><?php endif; ?>
            </span></td>
            <td data-label="IP"><small><?= e($ip) ?></small></td>
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
