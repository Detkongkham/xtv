<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

/** Sends mail over SMTP (Mailpit locally, cPanel SMTP on the server). Throws on failure. */
final class Mailer
{
    public function __construct(private array $config)
    {
    }

    public static function fromConfig(): self
    {
        return new self((array) App::config('mail', []));
    }

    /**
     * @param string[] $to
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send(array $to, string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): void
    {
        $c = $this->config;
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host = (string) $c['host'];
        $m->Port = (int) $c['port'];
        $m->Timeout = (int) ($c['timeout'] ?? 10);
        $m->SMTPAutoTLS = false;
        if (($c['secure'] ?? '') !== '') {
            $m->SMTPSecure = $c['secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        }
        if (($c['user'] ?? '') !== '') {
            $m->SMTPAuth = true;
            $m->Username = (string) $c['user'];
            $m->Password = (string) $c['pass'];
        }
        $m->CharSet = PHPMailer::CHARSET_UTF8;
        $m->Encoding = PHPMailer::ENCODING_BASE64;
        $m->setFrom((string) $c['from'], (string) ($c['from_name'] ?? ''));
        foreach ($to as $address) {
            $m->addAddress($address);
        }
        if ($replyTo !== null) {
            $m->addReplyTo($replyTo, $replyName ?? '');
        }
        $m->Subject = $subject;
        $m->isHTML(true);
        $m->Body = $html;
        $m->AltBody = self::toText($html);
        $m->send();
    }

    /** Plain-text version of an HTML email. */
    public static function toText(string $html): string
    {
        $text = preg_replace(['#<br\s*/?>#i', '#</(p|div|h[1-6]|tr|li)>#i'], "\n", $html);
        $text = preg_replace('#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ($1)', (string) $text);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n\s*\n\s*\n+/", "\n\n", (string) $text);
        return trim((string) $text);
    }
}
