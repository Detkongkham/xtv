<?php
/** @var array $rows page => seo_pages row @var array $defaults page => title_<lang>/desc_<lang> */
use App\Http\Admin\SeoController as SC;
?>
<p class="hint">ຫົວຂໍ້ ແລະ ຄຳອະທິບາຍທີ່ Google ສະແດງໃນຜົນການຄົ້ນຫາ. ຫວ່າງ = ໜ້ານັ້ນໃຊ້ຫົວຂໍ້ຂອງມັນເອງ. ສິນຄ້າແກ້ໃນ <a href="/admin/products">ຟອມສິນຄ້າ</a>; ຂ່າວໃຊ້ຫົວຂໍ້ ແລະ ສະຫຼຸບຂອງຂ່າວ.</p>
<div class="card table-wrap">
  <table class="table sup-table">
    <thead><tr><th scope="col">ໜ້າ</th><th scope="col">ຫົວຂໍ້ ແລະ ຄຳອະທິບາຍ (ລາວ)</th><th scope="col">ສະຖານະ</th></tr></thead>
    <tbody>
      <?php foreach (SC::PAGES as $page => $name): $row = $rows[$page] ?? null; $d = $defaults[$page] ?>
        <tr id="seo-<?= e($page) ?>">
          <td data-label="ໜ້າ"><a href="/admin/seo/<?= e($page) ?>"><b><?= e($name) ?></b></a><br><small><?= e($page) ?>.html</small></td>
          <td data-label="ຫົວຂໍ້ ແລະ ຄຳອະທິບາຍ (ລາວ)"><?= e(($row['title_lo'] ?? '') ?: $d['title_lo']) ?><br><small><?= e(($row['desc_lo'] ?? '') ?: $d['desc_lo']) ?></small></td>
          <td data-label="ສະຖານະ"><?= $row === null ? '<span class="tag tag--spam">ປົກກະຕິ</span>' : '<span class="tag tag--done">ກຳນົດເອງ</span>' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="hint">ການປ່ຽນແປງຂຶ້ນໜ້າເວັບເມື່ອກົດ <a href="/admin/publish">ເຜີຍແຜ່</a>.</p>
