<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DbRateStore;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Service\GeminiTranslator;

/**
 * POST /admin/translate: the "ແປເປັນ 3 ພາສາ" button in partials/lang-fields.php. Answers JSON only, nothing is saved:
 * admin.js puts the text into the empty English, Thai and Chinese fields and the person saves the form.
 * Body: _csrf, fields = JSON {prefix: {text: Lao text, html: bool}}. Answer: {result: {en|th|zh: {prefix: text}}} or {error}.
 */
final class TranslateController extends Controller
{
    /** per IP: [bucket, max, seconds]; keeps a stuck button or a script from spending the free daily quota */
    private const LIMITS = [['translate', 10, 60], ['translate', 60, 3600]];

    public function translate(): never
    {
        if (Auth::user() === null) {
            Response::json(['error' => 'ໝົດເວລາເຂົ້າລະບົບ. ກະລຸນາເຂົ້າລະບົບໃໝ່.'], 401);
        }
        if (!Csrf::valid(Request::post('_csrf'))) {
            Response::json(['error' => 'ໝົດເວລາຂອງຟອມ. ກະລຸນາໂຫຼດໜ້າໃໝ່.'], 403);
        }
        $translator = GeminiTranslator::fromConfig();
        if ($translator === null) {
            Response::json(['error' => 'ຍັງບໍ່ໄດ້ຕັ້ງຄ່າການແປ (translator.key ໃນ config.php).'], 503);
        }
        $fields = self::fields(Request::post('fields'));
        if ($fields === null) {
            Response::json(['error' => 'ຂໍ້ມູນທີ່ສົ່ງມາບໍ່ຖືກຕ້ອງ.'], 400);
        }
        $limiter = new RateLimiter(new DbRateStore(App::db()));
        if (!$limiter->allows(Request::ipBinary(), self::LIMITS)) {
            Response::json(['error' => 'ກົດແປຖີ່ເກີນໄປ. ລໍຖ້າບຶດໜຶ່ງແລ້ວລອງໃໝ່.'], 429);
        }
        $limiter->hit('translate', Request::ipBinary());

        [$result, $error] = $translator->translate($fields);
        App::logger()->info('translate', ['user' => Auth::id(), 'fields' => array_keys($fields), 'chars' => array_sum(array_map(static fn ($f) => mb_strlen($f['text']), $fields)), 'ok' => $error === null]);
        if ($error !== null) {
            Response::json(['error' => $error], 502);
        }
        Response::json(['result' => $result]);
    }

    /**
     * The posted fields, or null when the shape is wrong. Empty texts are left out.
     * @return ?array<string, array{text: string, html: bool}>
     */
    public static function fields(string $json): ?array
    {
        $data = json_decode($json, true);
        if (!is_array($data) || count($data) > 80) {
            return null;
        }
        $out = [];
        foreach ($data as $prefix => $f) {
            if (!is_string($prefix) || !preg_match('/^[a-z][a-z0-9_]{0,40}$/', $prefix) || !is_array($f) || !is_string($f['text'] ?? null)) {
                return null;
            }
            $text = trim(str_replace("\r\n", "\n", $f['text']));
            if ($text !== '') {
                $out[$prefix] = ['text' => $text, 'html' => ($f['html'] ?? false) === true];
            }
        }
        return $out;
    }
}
