<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Publish\PagePreview;
use App\Publish\SiteData;

/**
 * /admin/preview?page=product-ct-scan&lang=lo (docs/plan/03-backend.md, Preview): one page from the current data,
 * changes that are not published yet included, and draft news. Logged-in users only, never indexed or cached.
 */
final class PreviewController extends Controller
{
    public function show(): never
    {
        $this->requireLogin();
        $page = Request::query('page', 'index');
        $lang = Request::query('lang', 'lo');
        if (!in_array($lang, Lang::CODES, true)) {
            $lang = 'lo';
        }
        $data = SiteData::fromDb(App::db());
        $draft = str_starts_with($page, 'news-') && $data->draftNews(substr($page, 5)) !== null;
        $html = preg_match('/^[a-z0-9][a-z0-9-]*$/', $page)
            ? (new PagePreview($data, dirname(App::path()) . '/public'))->render($page, $lang, self::bar($page, $lang, $draft))
            : null;
        if ($html === null) {
            $this->page('preview/missing', 'ບໍ່ມີໜ້ານີ້', ['page' => $page], 404);
        }
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store');
        Response::html($html);
    }

    /** The note fixed at the bottom of the page: what this is, other languages, back to the admin. */
    private static function bar(string $page, string $lang, bool $draft): string
    {
        $langs = '';
        foreach (Lang::SHORT as $code => $short) {
            $langs .= $code === $lang ? ' <b>' . e($short) . '</b>'
                : ' <a href="' . PagePreview::url($page, $code) . '" lang="' . Lang::HTML[$code] . '">' . e($short) . '</a>';
        }
        $what = $draft ? 'ຕົວຢ່າງຂ່າວສະບັບຮ່າງ: ຈະຂຶ້ນເວັບເມື່ອຕັ້ງສະຖານະເປັນ "ເຜີຍແຜ່" ແລ້ວກົດເຜີຍແຜ່.'
            : 'ຕົວຢ່າງຈາກຂໍ້ມູນປັດຈຸບັນ, ລວມທັງການແກ້ໄຂທີ່ຍັງບໍ່ເຜີຍແຜ່.';
        return '<p class="preview-bar" role="note" lang="lo">'
            . e($what) . '<br>' . trim($langs) . ' · <a href="/admin/publish">ເຜີຍແຜ່</a> · <a href="/admin">ກັບໄປ admin</a></p>';
    }
}
