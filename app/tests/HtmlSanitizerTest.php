<?php
declare(strict_types=1);

namespace App\Tests;

use App\Service\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    public static function dangerous(): array
    {
        return [
            'script' => ['<p>hi</p><script>alert(1)</script>', '<p>hi</p>'],
            'script inside p' => ['<p>a<script>alert(1)</script>b</p>', '<p>ab</p>'],
            'onerror' => ['<img src="../uploads/2026/10/a-960.jpg" onerror="alert(1)">', '<p><img src="../uploads/2026/10/a-960.jpg" alt="" loading="lazy"></p>'],
            'img from elsewhere' => ['<p><img src="https://evil.example/x.jpg">x</p>', '<p>x</p>'],
            'img with javascript' => ['<img src="javascript:alert(1)">', ''],
            'javascript link' => ['<p><a href="javascript:alert(1)">x</a></p>', '<p>x</p>'],
            'javascript link with tab' => ["<p><a href=\"java\tscript:alert(1)\">x</a></p>", '<p>x</p>'],
            'javascript link as entities' => ['<p><a href="&#106;avascript:alert(1)">x</a></p>', '<p>x</p>'],
            'data link' => ['<p><a href="data:text/html,<b>x</b>">x</a></p>', '<p>x</p>'],
            'onclick and style' => ['<p onclick="x()" style="color:red" class="c">t</p>', '<p>t</p>'],
            'iframe' => ['<iframe src="https://evil.example"></iframe><p>ok</p>', '<p>ok</p>'],
            'svg' => ['<svg onload="alert(1)"><circle/></svg><p>ok</p>', '<p>ok</p>'],
            'style tag' => ['<style>body{display:none}</style><p>ok</p>', '<p>ok</p>'],
            'comment' => ['<p>a<!-- <script>x</script> -->b</p>', '<p>ab</p>'],
            'form' => ['<form action="https://evil.example"><input name="pw"></form><p>ok</p>', '<p>ok</p>'],
            'nested unknown' => ['<div><span style="x"><p>t</p></span></div>', '<p>t</p>'],
        ];
    }

    #[DataProvider('dangerous')]
    public function testRemovesDangerousHtml(string $in, string $out): void
    {
        self::assertSame($out, HtmlSanitizer::clean($in));
    }

    public function testKeepsAllowedFormatting(): void
    {
        $html = '<h2>ຫົວຂໍ້</h2><p>ຂໍ້ຄວາມ <strong>ໜາ</strong> ແລະ <em>ອຽງ</em><br>ແຖວໃໝ່</p><ul><li>ໜຶ່ງ</li></ul><ol><li>1</li></ol>'
            . '<blockquote><p>q</p></blockquote><figure><img src="../uploads/2026/10/ab-960.jpg" alt="x" width="960" height="540" loading="lazy"><figcaption>c</figcaption></figure>';
        self::assertSame($html, HtmlSanitizer::clean($html));
    }

    public function testLinks(): void
    {
        self::assertSame('<p><a href="https://example.com/a?b=1&amp;c=2" target="_blank" rel="noopener">x</a></p>', HtmlSanitizer::clean('<a href="https://example.com/a?b=1&c=2" target="_self">x</a>'));
        self::assertSame('<p><a href="product-ct-scan.html">x</a></p>', HtmlSanitizer::clean('<a href="product-ct-scan.html">x</a>'));
        self::assertSame('<p><a href="healthcare.html#g_imaging">x</a></p>', HtmlSanitizer::clean('<a href="healthcare.html#g_imaging">x</a>'));
        self::assertSame('<p><a href="mailto:a@b.la">m</a> <a href="tel:+85621563121">t</a></p>', HtmlSanitizer::clean('<a href="mailto:a@b.la">m</a> <a href="tel:+85621563121">t</a>'));
        self::assertSame('<p>x</p>', HtmlSanitizer::clean('<a href="../../etc/passwd">x</a>'));
    }

    public function testRenamesAndWrapsLooseText(): void
    {
        self::assertSame('<p><strong>b</strong> and <em>i</em></p><h2>t</h2>', HtmlSanitizer::clean('<b>b</b> and <i>i</i><h1>t</h1>'));
        self::assertSame('<p>plain text</p>', HtmlSanitizer::clean('plain text'));
        self::assertSame('<p>a</p>', HtmlSanitizer::clean('<p>a</p><p>&nbsp;</p><p><br></p>'));
        self::assertSame('', HtmlSanitizer::clean('  '));
        self::assertSame('<ul><li><strong>a</strong> b</li></ul><h2>c</h2>', HtmlSanitizer::clean('<p><ul><li><b>a</b> b</li></ul></p><h2>c</h2>'));
        self::assertSame('<figure><img src="../uploads/a-960.jpg" alt="" loading="lazy"></figure>', HtmlSanitizer::clean('<h2><figure><img src="../uploads/a-960.jpg"></figure></h2>'));
    }

    public function testLaoTextSurvives(): void
    {
        self::assertSame('<p>ບໍລິສັດ ຊັບທະວີຄູນ — 中文 ไทย</p>', HtmlSanitizer::clean('<p>ບໍລິສັດ ຊັບທະວີຄູນ — 中文 ไทย</p>'));
    }
}
