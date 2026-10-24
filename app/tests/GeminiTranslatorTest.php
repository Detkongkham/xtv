<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\App;
use App\Http\Admin\TranslateController;
use App\Service\GeminiTranslator;
use PHPUnit\Framework\TestCase;

/** "ແປເປັນ 3 ພາສາ" in the admin forms: off without a key, the Gemini request, reading the answer, the posted fields. */
final class GeminiTranslatorTest extends TestCase
{
    private const FIELDS = ['title' => ['text' => 'ຂ່າວໃໝ່', 'html' => false], 'body' => ['text' => '<p>ສະບາຍດີ</p>', 'html' => true]];

    protected function tearDown(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
    }

    /** A Gemini answer whose text part is $answer as JSON. */
    private static function reply(array $answer): string
    {
        return json_encode(['candidates' => [['content' => ['parts' => [['text' => json_encode($answer, JSON_UNESCAPED_UNICODE)]]]]]]);
    }

    private static function good(): array
    {
        return [
            'en' => ['title' => 'New news', 'body' => '<p>Hello</p><script>alert(1)</script>'],
            'th' => ['title' => 'ข่าวใหม่', 'body' => '<p>สวัสดี</p>'],
            'zh' => ['title' => '新消息', 'body' => '<p>你好</p>'],
        ];
    }

    public function testOffUntilAKeyIsSet(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        self::assertNull(GeminiTranslator::fromConfig());
        self::assertFalse(GeminiTranslator::enabled());
        App::boot(['env' => 'test', 'translator' => ['key' => 'k', 'model' => '']], dirname(__DIR__));
        self::assertInstanceOf(GeminiTranslator::class, GeminiTranslator::fromConfig());
        self::assertTrue(GeminiTranslator::enabled());
    }

    public function testRequestAndAnswer(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        $sent = [];
        $post = static function (string $url, array $headers, string $json) use (&$sent): array {
            $sent = [$url, $headers, json_decode($json, true)];
            return [200, self::reply(self::good())];
        };
        [$out, $error] = (new GeminiTranslator('k', 'm', ['XTK'], $post))->translate(self::FIELDS);
        self::assertNull($error);
        self::assertSame('https://generativelanguage.googleapis.com/v1beta/models/m:generateContent', $sent[0]);
        self::assertSame(['x-goog-api-key' => 'k'], $sent[1]);   // the key goes in a header, never in the URL
        $req = $sent[2];
        self::assertStringContainsString('XTK', $req['systemInstruction']['parts'][0]['text']);
        self::assertSame(['title' => ['text' => 'ຂ່າວໃໝ່'], 'body' => ['html' => '<p>ສະບາຍດີ</p>']], json_decode($req['contents'][0]['parts'][0]['text'], true));
        self::assertSame(['en', 'th', 'zh'], $req['generationConfig']['responseSchema']['required']);
        self::assertSame(['title', 'body'], $req['generationConfig']['responseSchema']['properties']['th']['required']);
        self::assertSame('New news', $out['en']['title']);
        self::assertSame('<p>Hello</p>', $out['en']['body']);   // HTML is cleaned before it reaches the editor
        self::assertSame('ข่าวใหม่', $out['th']['title']);
        self::assertSame('<p>你好</p>', $out['zh']['body']);
    }

    public function testErrors(): void
    {
        App::boot(['env' => 'test'], dirname(__DIR__));
        $with = static fn (int $status, ?string $body) => new GeminiTranslator('k', 'm', [], static fn () => [$status, $body]);
        self::assertStringContainsString('ໂຄຕາ', $with(429, '{}')->translate(self::FIELDS)[1]);
        self::assertStringContainsString('API key', $with(400, '{"error":{"details":[{"reason":"API_KEY_INVALID"}]}}')->translate(self::FIELDS)[1]);
        self::assertStringContainsString('model', $with(404, '{}')->translate(self::FIELDS)[1]);
        self::assertStringContainsString('ຕິດຕໍ່', $with(0, null)->translate(self::FIELDS)[1]);
        $missing = self::good();
        unset($missing['zh']['body']);
        self::assertNull($with(200, self::reply($missing))->translate(self::FIELDS)[0]);
        self::assertNull($with(200, '<html>')->translate(self::FIELDS)[0]);
        $called = false;
        $t = new GeminiTranslator('k', 'm', [], static function () use (&$called): array { $called = true; return [200, '']; });
        self::assertNotNull($t->translate([])[1]);
        self::assertNotNull($t->translate(['body' => ['text' => str_repeat('ກ', GeminiTranslator::MAX_CHARS + 1), 'html' => false]])[1]);
        self::assertFalse($called);   // nothing to send / too long: Gemini is not asked
    }

    public function testPostedFields(): void
    {
        self::assertSame(['title' => ['text' => 'ກ', 'html' => false], 'body' => ['text' => "<p>ຂ</p>\nຄ", 'html' => true]],
            TranslateController::fields('{"title":{"text":" ກ "},"summary":{"text":""},"body":{"text":"<p>ຂ</p>\r\nຄ","html":true}}'));
        self::assertNull(TranslateController::fields('nope'));
        self::assertNull(TranslateController::fields('{"Bad Key":{"text":"ກ"}}'));
        self::assertNull(TranslateController::fields('{"title":{"text":1}}'));
        self::assertNull(TranslateController::fields('["ກ"]'));
    }
}
