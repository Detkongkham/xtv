<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Publish\SiteData;
use App\Repository\SettingRepository;

/** /admin/settings (docs/plan/03-backend.md §2.9): contact details, home page stats, notify emails, analytics, noindex. Admins only. */
final class SettingController extends Controller
{
    public const ADDRESS = [['address', 'ທີ່ຢູ່', 'textarea', 300]];

    /** Keys shown on the public pages: changing one means the site needs publishing. notify_emails is used by the contact API only. */
    private const ON_SITE = ['contact', 'address', 'map', 'stats', 'analytics_id', 'noindex'];

    private function repo(): SettingRepository
    {
        return new SettingRepository(App::db());
    }

    public function index(): never
    {
        $this->requireAdmin();
        $this->form($this->current(), []);
    }

    public function update(): never
    {
        $this->requireAdmin();
        $this->checkCsrf();
        [$values, $errors] = $this->input();
        if ($errors !== []) {
            $this->form($values, $errors, 422);
        }
        $repo = $this->repo();
        $changed = [];
        foreach ($values as $key => $value) {
            if ($repo->get($key) !== $value) {
                $repo->set($key, $value);
                $changed[] = $key;
            }
        }
        if ($changed === []) {
            $this->back('/admin/settings', 'ok', 'ບໍ່ມີຫຍັງປ່ຽນ.');
        }
        App::logger()->info('settings: updated', ['keys' => $changed, 'by' => Auth::id()]);
        if (array_intersect($changed, self::ON_SITE) === []) {
            $this->back('/admin/settings', 'ok', 'ບັນທຶກແລ້ວ.');
        }
        $this->contentChanged();
        $this->back('/admin/settings', 'ok', 'ບັນທຶກແລ້ວ. ກົດ "ເຜີຍແຜ່" ເພື່ອໃຫ້ຂຶ້ນໜ້າເວັບ.');
    }

    /** Stored values with defaults for keys that were never set. */
    private function current(): array
    {
        $r = $this->repo();
        return [
            'contact' => (array) $r->get('contact', []) + ['tel' => '', 'mobile' => '', 'fax' => '', 'email' => '', 'whatsapp' => ''],
            'address' => (array) $r->get('address', []) + ['lo' => '', 'en' => '', 'th' => '', 'zh' => ''],
            'map' => (array) $r->get('map', []) + ['query' => ''],
            'stats' => (array) $r->get('stats', []) + ['founded' => '', 'experience' => ''],
            'notify_emails' => (array) $r->get('notify_emails', []),
            'analytics_id' => (string) $r->get('analytics_id', ''),
            'noindex' => (bool) $r->get('noindex', true),
        ];
    }

    /** @return array{0: array, 1: array<string, string>} values in the stored shape, errors by field name */
    private function input(): array
    {
        $errors = [];
        $text = static fn (string $k): string => Validator::clean(Request::post($k));

        $contact = [];
        foreach (['tel', 'mobile', 'fax'] as $k) {
            $contact[$k] = $text($k);
            if ($contact[$k] !== '' && !Validator::isPhone($contact[$k])) {
                $errors[$k] = 'ເບີໂທບໍ່ຖືກຕ້ອງ (ຕົວເລກ, + - ( ) ແລະ ຍະຫວ່າງ).';
            }
        }
        foreach (['tel', 'mobile'] as $k) {
            if ($contact[$k] === '') {
                $errors[$k] = 'ກະລຸນາໃສ່ເບີໂທ.';
            }
        }
        $contact['email'] = mb_strtolower($text('email'));
        if (!Validator::isEmail($contact['email'])) {
            $errors['email'] = 'ອີເມວບໍ່ຖືກຕ້ອງ.';
        }
        // WhatsApp links need the number with the country code and nothing else: 8562055892929
        $contact['whatsapp'] = (string) preg_replace('/\D+/', '', $text('whatsapp'));
        if (!preg_match('/^[1-9]\d{7,14}$/', $contact['whatsapp'])) {
            $errors['whatsapp'] = 'ໃສ່ເບີພ້ອມລະຫັດປະເທດ ເຊັ່ນ 8562055892929.';
        }

        [$addr, $addrErrors] = $this->translatedInput(self::ADDRESS);
        $errors += $addrErrors;
        $address = [];
        foreach (['lo', 'en', 'th', 'zh'] as $l) {
            $address[$l] = (string) ($addr['address_' . $l] ?? '');
        }

        $map = ['query' => $text('map_query')];
        if ($map['query'] === '') {
            $errors['map_query'] = 'ກະລຸນາໃສ່ສະຖານທີ່ສຳລັບແຜນທີ່.';
        } elseif (mb_strlen($map['query']) > 200) {
            $errors['map_query'] = 'ຍາວເກີນ 200 ຕົວອັກສອນ.';
        }

        $founded = $text('founded');
        $experience = $text('experience');
        if (!preg_match('/^\d{4}$/', $founded) || (int) $founded < 1900 || (int) $founded > (int) date('Y')) {
            $errors['founded'] = 'ໃສ່ປີ ຄ.ສ. 4 ຕົວເລກ (1900–' . date('Y') . ').';
        }
        if (!preg_match('/^\d{1,3}$/', $experience)) {
            $errors['experience'] = 'ໃສ່ຈຳນວນປີເປັນຕົວເລກ.';
        }
        $stats = ['founded' => (int) $founded, 'experience' => (int) $experience];

        $notify = [];
        foreach (preg_split('/[\s,;]+/', mb_strtolower(Request::post('notify_emails'))) ?: [] as $email) {
            if ($email === '') {
                continue;
            }
            if (!Validator::isEmail($email)) {
                $errors['notify_emails'] = '"' . $email . '" ບໍ່ແມ່ນອີເມວທີ່ຖືກຕ້ອງ.';
            }
            $notify[$email] = $email;
        }
        if ($notify === []) {
            $errors['notify_emails'] ??= 'ໃສ່ຢ່າງໜ້ອຍ 1 ອີເມວ ເພື່ອບໍ່ໃຫ້ຂໍ້ຄວາມຈາກຟອມຕົກຫຼົ່ນ.';
        }

        $analytics = strtoupper($text('analytics_id'));
        if ($analytics !== '' && !preg_match('/^G-[A-Z0-9]{4,15}$/', $analytics)) {
            $errors['analytics_id'] = 'ໃຊ້ Measurement ID ຂອງ GA4 ເຊັ່ນ G-ABC123XYZ9, ຫຼື ປ່ອຍຫວ່າງ.';
        }

        return [[
            'contact' => $contact,
            'address' => $address,
            'map' => $map,
            // a wrong number is shown back as typed
            'stats' => isset($errors['founded']) || isset($errors['experience']) ? ['founded' => $founded, 'experience' => $experience] : $stats,
            'notify_emails' => array_values($notify),
            'analytics_id' => $analytics,
            'noindex' => Request::post('noindex') === '1',
        ], $errors];
    }

    private function form(array $s, array $errors, int $status = 200): never
    {
        // counted the way the home page counts them: only what the site shows
        $site = SiteData::fromDb(App::db());
        $this->page('settings/index', 'ການຕັ້ງຄ່າ', [
            's' => $s, 'errors' => $errors,
            'divisionCount' => count($site->divisions()),
            'productCount' => count($site->allProducts()),
        ], $status);
    }
}
