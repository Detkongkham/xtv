<?php
declare(strict_types=1);

namespace App\Http;

use App\Core\App;
use App\Core\DbRateStore;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repository\MessageRepository;
use App\Repository\SettingRepository;
use App\Service\ContactService;
use App\Service\TimeToken;
use App\Service\Turnstile;

/** /api/contact.php — GET ?token=1 issues a time token, POST receives the form. */
final class ContactController
{
    public function handle(): never
    {
        header('X-Robots-Tag: noindex');
        $token = new TimeToken((string) App::config('hmac_secret', ''));

        if (Request::method() === 'GET' && Request::query('token') === '1') {
            Response::json(['ts' => $token->issue()]);
        }
        if (!Request::isPost()) {
            header('Allow: POST');
            Response::json(['ok' => false, 'error' => 'method'], 405);
        }
        $json = Request::wantsJson();
        $lang = in_array(Request::post('lang'), ContactService::LANGS, true) ? Request::post('lang') : 'lo';

        if (!$this->sameOrigin()) {
            $this->respond($json, $lang, ['status' => 403, 'body' => ['ok' => false, 'error' => 'origin']]);
        }

        $db = App::db();
        $settings = new SettingRepository($db);
        $service = new ContactService(new MessageRepository($db), $settings, new RateLimiter(new DbRateStore($db)), $token, Mailer::fromConfig(), Turnstile::fromConfig());
        $result = $service->submit($_POST, [
            'ip' => Request::ipBinary(),
            'user_agent' => Request::header('User-Agent'),
            'referer' => Request::header('Referer'),
        ]);
        $this->respond($json, $lang, $result, $settings);
    }

    /** Origin (or Referer when Origin is missing) must be this host or one listed in allowed_origins. */
    private function sameOrigin(): bool
    {
        $source = Request::header('Origin') ?: Request::header('Referer');
        if ($source === '') {
            return true; // some privacy tools strip both; the other checks still apply
        }
        $p = parse_url($source);
        if (!is_array($p) || !isset($p['host'])) {
            return false;
        }
        $origin = strtolower($p['host'] . (isset($p['port']) ? ':' . $p['port'] : ''));
        if ($origin === strtolower(Request::header('Host'))) {
            return true;
        }
        foreach ((array) App::config('allowed_origins', []) as $allowed) {
            $a = parse_url((string) $allowed);
            if (is_array($a) && isset($a['host']) && strtolower($a['host'] . (isset($a['port']) ? ':' . $a['port'] : '')) === $origin) {
                return true;
            }
        }
        return false;
    }

    private function respond(bool $json, string $lang, array $result, ?SettingRepository $settings = null): never
    {
        if ($json) {
            Response::json($result['body'], $result['status']);
        }
        if ($result['status'] === 200) {
            Response::redirect('/' . $lang . '/contact.html?sent=1#sent', 303);
        }
        // plain form post without JS: show a small page in the visitor's language
        $contact = [];
        try {
            $contact = (array) ($settings?->get('contact', []) ?? []);
        } catch (\Throwable) {
        }
        Response::html(View::render('contact-result', [
            'lang' => $lang,
            't' => ContactService::texts($lang),
            'result' => $result,
            'contact' => $contact,
        ]), $result['status']);
    }
}
