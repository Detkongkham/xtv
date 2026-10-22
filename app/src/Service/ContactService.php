<?php
declare(strict_types=1);

namespace App\Service;

use App\Core\App;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Validator;
use App\Core\View;
use App\Repository\MessageRepository;
use App\Repository\SettingRepository;

/**
 * Contact form handling (docs/plan/03-backend.md §3). Returns a result the controller turns into
 * JSON (fetch) or a redirect / HTML page (plain form post without JS).
 */
final class ContactService
{
    public const LANGS = ['lo', 'en', 'th', 'zh'];

    public const RULES = [
        'name' => ['required', 'len:2,120'],
        'org' => ['len:0,160'],
        'tel' => ['required', 'phone'],
        'email' => ['required', 'email', 'len:0,190'],
        'subject' => ['in:general,sales,service,other'],
        'message' => ['required', 'len:10,5000'],
        'consent' => ['accepted'],
    ];

    public function __construct(
        private MessageRepository $messages,
        private SettingRepository $settings,
        private RateLimiter $limiter,
        private TimeToken $token,
        private Mailer $mailer,
    ) {
    }

    /**
     * @param array<string, mixed> $post  raw $_POST
     * @param array{ip:string, user_agent:string, referer:string} $meta
     * @return array{status:int, body:array<string,mixed>, id?:int}
     */
    public function submit(array $post, array $meta): array
    {
        $str = static fn (string $k): string => is_string($post[$k] ?? null) ? $post[$k] : '';

        // honeypot: pretend success so bots learn nothing
        if (trim($str('website')) !== '') {
            App::logger()->info('contact: honeypot filled', ['ip' => inet_ntop($meta['ip'])]);
            return ['status' => 200, 'body' => ['ok' => true]];
        }

        $ts = trim($str('ts'));
        $hasToken = $ts !== '';
        if ($hasToken) {
            $check = $this->token->verify($ts);
            if ($check !== 'ok') {
                return ['status' => 422, 'body' => ['ok' => false, 'error' => $check === 'expired' ? 'expired' : 'invalid_token']];
            }
        }

        $limits = [['contact', 20, 86400], ['contact', 5, 600]];
        if (!$hasToken) {
            $limits[] = ['contact_nojs', 2, 600];
        }
        if (!$this->limiter->allows($meta['ip'], $limits)) {
            return ['status' => 429, 'body' => ['ok' => false, 'error' => 'rate_limited']];
        }

        $data = [
            'name' => Validator::clean($str('name')),
            'org' => Validator::clean($str('org')),
            'tel' => Validator::clean($str('tel')),
            'email' => Validator::clean($str('email')),
            'subject' => Validator::clean($str('subject')),
            'message' => Validator::clean($str('message'), true),
            'consent' => Validator::clean($str('consent')),
        ];
        $lang = in_array($str('lang'), self::LANGS, true) ? $str('lang') : 'lo';

        $errors = Validator::check($data, self::RULES);
        if ($errors !== []) {
            return ['status' => 422, 'body' => ['ok' => false, 'errors' => $errors]];
        }

        $this->limiter->hit('contact', $meta['ip']);
        if (!$hasToken) {
            $this->limiter->hit('contact_nojs', $meta['ip']);
        }

        $id = $this->messages->create([
            'name' => $data['name'],
            'org' => $data['org'] === '' ? null : $data['org'],
            'phone' => $data['tel'],
            'email' => $data['email'],
            'subject' => $data['subject'] === '' ? 'general' : $data['subject'],
            'body' => $data['message'],
            'lang' => $lang,
            'page_url' => self::cut($meta['referer'], 255),
            'ip' => $meta['ip'],
            'user_agent' => self::cut($meta['user_agent'], 255),
        ]);

        $this->sendMails($id);
        return ['status' => 200, 'body' => ['ok' => true], 'id' => $id];
    }

    /** Notify the team, then thank the customer. A failure is stored in mail_error; the message stays saved. */
    private function sendMails(int $id): void
    {
        $m = $this->messages->find($id);
        $contact = (array) $this->settings->get('contact', []);
        $texts = self::texts($m['lang']);
        $failures = [];

        $notify = array_values(array_filter((array) $this->settings->get('notify_emails', []), [Validator::class, 'isEmail']));
        try {
            if ($notify === []) {
                throw new \RuntimeException('notify_emails is empty');
            }
            $subjectLo = self::texts('lo')['subjects'][$m['subject']] ?? $m['subject'];
            $this->mailer->send(
                $notify,
                sprintf('[XTK ເວັບ] %s — %s', $subjectLo, $m['name']),
                View::render('email/contact-notify', ['m' => $m, 'subjectName' => $subjectLo, 'adminUrl' => rtrim((string) App::config('base_url'), '/') . '/admin/messages/' . $id]),
                $m['email'],
                $m['name']
            );
        } catch (\Throwable $e) {
            $failures[] = 'notify: ' . $e->getMessage();
        }

        try {
            $this->mailer->send(
                [$m['email']],
                $texts['reply_subject'],
                View::render('email/contact-autoreply', ['m' => $m, 't' => $texts, 'contact' => $contact])
            );
        } catch (\Throwable $e) {
            $failures[] = 'reply: ' . $e->getMessage();
        }

        if ($failures !== []) {
            $error = implode(' | ', $failures);
            $this->messages->setMailError($id, $error);
            App::logger()->error('contact: mail failed', ['id' => $id, 'error' => $error]);
        }
    }

    public static function texts(string $lang): array
    {
        static $all = null;
        $all ??= require App::path('lang/contact.php');
        return $all[$lang] ?? $all['lo'];
    }

    private static function cut(string $s, int $max): ?string
    {
        $s = trim($s);
        return $s === '' ? null : mb_substr($s, 0, $max);
    }
}
