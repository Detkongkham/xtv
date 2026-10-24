<?php
/**
 * "ແປເປັນ 3 ພາສາ" (admin.js [data-translate], POST /admin/translate): fills the empty English, Thai and Chinese
 * fields of the closest [data-translate-box] from its Lao ones (name ending in _lo, or _lo] as in specs[3][label_lo]).
 * Must sit inside the form (it reads _csrf). Nothing is shown without a Gemini key in config.php.
 * @var string $hint (optional) what the button fills on this form
 */
use App\Service\GeminiTranslator;

if (!GeminiTranslator::enabled()) {
    return;
}
?>
<div class="lang-translate" data-translate hidden>
  <button class="btn btn--ghost btn--sm" type="button">ແປເປັນ 3 ພາສາ</button>
  <span class="hint" data-translate-status role="status" aria-atomic="true"><?= e($hint ?? 'ພິມພາສາລາວ ແລ້ວກົດປຸ່ມ: ຄຳແປຈະໃສ່ສະເພາະຊ່ອງ ອັງກິດ / ໄທ / ຈີນ ທີ່ຍັງຫວ່າງ.') ?></span>
</div>
