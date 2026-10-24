<?php
declare(strict_types=1);

namespace App\Service;

use App\Core\App;

/**
 * Lao → English, Thai and Chinese with the Gemini API, for the "ແປເປັນ 3 ພາສາ" button in the admin forms
 * (partials/lang-fields.php → POST /admin/translate). Off until config.php has 'translator' => ['key' => ...]
 * (a free key from aistudio.google.com, no card). The text only fills the form: a person reads it and saves.
 * Free tier: requests per minute and per day are limited and reset at midnight Pacific time (14:00–15:00 in Laos).
 */
final class GeminiTranslator
{
    public const URL = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';
    public const DEFAULT_MODEL = 'gemini-flash-lite-latest';
    /** language code in the form => name given to the model */
    public const TARGETS = ['en' => 'English', 'th' => 'Thai', 'zh' => 'Simplified Chinese (Mainland China)'];
    /** Lao characters in one request, all fields together */
    public const MAX_CHARS = 20000;

    /** @var callable(string, array<string, string>, string): array{0: int, 1: ?string} POST JSON: [HTTP status, body] (0, null = network error) */
    private $post;

    /**
     * @param list<string> $keep words that stay as they are (company, brand and product names)
     */
    public function __construct(private string $key, private string $model = self::DEFAULT_MODEL, private array $keep = [], ?callable $post = null)
    {
        $this->post = $post ?? self::curlPost(...);
    }

    /** null when translation is off (no key in config.php). */
    public static function fromConfig(): ?self
    {
        $key = (string) App::config('translator.key', '');
        if ($key === '') {
            return null;
        }
        $model = (string) App::config('translator.model', '');
        return new self($key, $model !== '' ? $model : self::DEFAULT_MODEL, array_values(array_filter((array) App::config('translator.keep', []), 'is_string')));
    }

    public static function enabled(): bool
    {
        return (string) App::config('translator.key', '') !== '';
    }

    /**
     * @param array<string, array{text: string, html: bool}> $fields column prefix => Lao text (html: from the editor)
     * @return array{0: ?array<string, array<string, string>>, 1: ?string} [lang => prefix => text, null] or [null, error in Lao]
     */
    public function translate(array $fields): array
    {
        if ($fields === []) {
            return [null, 'ບໍ່ມີຂໍ້ຄວາມພາສາລາວໃຫ້ແປ.'];
        }
        $chars = array_sum(array_map(static fn ($f) => mb_strlen($f['text']), $fields));
        if ($chars > self::MAX_CHARS) {
            return [null, 'ຂໍ້ຄວາມຍາວເກີນ ' . number_format(self::MAX_CHARS) . ' ຕົວອັກສອນ. ກະລຸນາແປເປັນສ່ວນໆ.'];
        }

        [$status, $body] = ($this->post)(sprintf(self::URL, rawurlencode($this->model)), ['x-goog-api-key' => $this->key], json_encode($this->request($fields), JSON_UNESCAPED_UNICODE));
        if ($status === 0 || $body === null) {
            App::logger()->warning('translate: Gemini could not be reached');
            return [null, 'ຕິດຕໍ່ບໍລິການແປບໍ່ໄດ້. ກວດອິນເຕີເນັດແລ້ວລອງໃໝ່.'];
        }
        if ($status !== 200) {
            App::logger()->warning('translate: Gemini error', ['status' => $status, 'body' => substr($body, 0, 300)]);
            return [null, match (true) {
                $status === 429 => 'ໂຄຕາການແປຟຣີໝົດແລ້ວ (ຕໍ່ນາທີ ຫຼື ຕໍ່ມື້). ລໍຖ້າບຶດໜຶ່ງ ຫຼື ລອງໃໝ່ມື້ອື່ນ (ໂຄຕາລາຍວັນເລີ່ມໃໝ່ປະມານບ່າຍ 2–3 ໂມງ).',
                $status === 400 && str_contains($body, 'API_KEY') || $status === 401 || $status === 403 => 'API key ຂອງ Gemini ບໍ່ຖືກຕ້ອງ ຫຼື ຖືກປິດ. ກວດ translator.key ໃນ config.php.',
                $status === 404 => 'ບໍ່ພົບ model "' . $this->model . '". ກວດ translator.model ໃນ config.php.',
                default => 'ບໍລິການແປຂັດຂ້ອງ (' . $status . '). ລອງໃໝ່ພາຍຫຼັງ.',
            }];
        }

        $out = self::parse($body, $fields);
        if ($out === null) {
            App::logger()->warning('translate: unexpected answer', ['body' => substr($body, 0, 300)]);
            return [null, 'ຄຳແປທີ່ໄດ້ຮັບບໍ່ຄົບ. ກະລຸນາລອງໃໝ່.'];
        }
        return [$out, null];
    }

    /** Body of the generateContent call: rules, the fields as JSON and a schema that makes the answer {en: {...}, th: {...}, zh: {...}}. */
    public function request(array $fields): array
    {
        $rules = [
            'You translate the Lao text of XTK, a company in Laos, for its public website.',
            'Translate each field into ' . implode(', ', array_map(static fn ($c, $n) => "\"{$c}\" = {$n}", array_keys(self::TARGETS), self::TARGETS)) . '.',
            'Keep the meaning exactly: add nothing, leave nothing out, no notes. Use a clear, formal business tone.',
            'Fields marked html contain HTML: keep every tag and attribute exactly as it is and translate only the text between tags.',
            'Do not translate company, brand, product or model names, numbers, units, e-mail addresses or links.',
        ];
        if ($this->keep !== []) {
            $rules[] = 'Always keep these words exactly as written: ' . implode(', ', $this->keep) . '.';
        }
        $input = [];
        foreach ($fields as $prefix => $f) {
            $input[$prefix] = $f['html'] ? ['html' => $f['text']] : ['text' => $f['text']];
        }
        $perLang = ['type' => 'OBJECT', 'properties' => array_map(static fn () => ['type' => 'STRING'], $fields), 'required' => array_map('strval', array_keys($fields))];
        return [
            'systemInstruction' => ['parts' => [['text' => implode("\n", $rules)]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]]]],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => array_map(static fn () => $perLang, self::TARGETS),
                    'required' => array_keys(self::TARGETS),
                ],
            ],
        ];
    }

    /**
     * Every language with every field as text, else null. HTML fields go through HtmlSanitizer here already,
     * because admin.js puts them into the editor (innerHTML); saving cleans them again.
     * @return ?array<string, array<string, string>>
     */
    public static function parse(string $body, array $fields): ?array
    {
        $data = json_decode($body, true);
        $parts = $data['candidates'][0]['content']['parts'] ?? null;
        if (!is_array($parts)) {
            return null;
        }
        $text = implode('', array_map(static fn ($p) => is_array($p) && empty($p['thought']) && is_string($p['text'] ?? null) ? $p['text'] : '', $parts));
        $answer = json_decode($text, true);
        if (!is_array($answer)) {
            return null;
        }
        $out = [];
        foreach (self::TARGETS as $l => $_) {
            foreach ($fields as $prefix => $f) {
                $t = $answer[$l][$prefix] ?? null;
                if (!is_string($t) || trim($t) === '') {
                    return null;
                }
                $out[$l][$prefix] = $f['html'] ? HtmlSanitizer::clean($t) : trim($t);
            }
        }
        return $out;
    }

    private static function curlPost(string $url, array $headers, string $json): array
    {
        $ch = curl_init($url);
        $lines = ['Content-Type: application/json'];
        foreach ($headers as $k => $v) {
            $lines[] = $k . ': ' . $v;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $lines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 60,
        ]);
        $body = curl_exec($ch);
        return is_string($body) ? [(int) curl_getinfo($ch, CURLINFO_HTTP_CODE), $body] : [0, null];
    }
}
