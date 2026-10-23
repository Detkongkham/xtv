<?php
/**
 * "Preview" button next to Save: the page as it is in the database now (save first), in a new tab.
 * @var string $page page name without .html
 */
use App\Publish\PagePreview;
?>
<a class="btn btn--ghost" href="<?= PagePreview::url($page) ?>" target="_blank" rel="noopener">ເບິ່ງຕົວຢ່າງ<span class="sr"> (ແທັບໃໝ່)</span></a>
