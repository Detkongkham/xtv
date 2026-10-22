<?php
declare(strict_types=1);

namespace App\Tests;

use App\Core\Validator;
use App\Service\ContactService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private function valid(): array
    {
        return [
            'name' => 'ສົມໃຈ ພົມມະຈັນ',
            'org' => 'ໂຮງໝໍມະໂຫສົດ',
            'tel' => '020 5589 2929',
            'email' => 'somchai@example.la',
            'subject' => 'sales',
            'message' => 'ຂໍສອບຖາມລາຄາເຄື່ອງ CT scan ແດ່.',
            'consent' => 'on',
        ];
    }

    public function testValidContactPasses(): void
    {
        $this->assertSame([], Validator::check($this->valid(), ContactService::RULES));
    }

    public function testOptionalFieldsMayBeEmpty(): void
    {
        $data = ['org' => '', 'subject' => ''] + $this->valid();
        $this->assertSame([], Validator::check($data, ContactService::RULES));
    }

    public function testRequiredFieldsReportRequired(): void
    {
        $errors = Validator::check([], ContactService::RULES);
        $this->assertSame(
            ['name' => 'required', 'tel' => 'required', 'email' => 'required', 'message' => 'required', 'consent' => 'required'],
            $errors
        );
    }

    public function testLengthCountsCharactersNotBytes(): void
    {
        // 10 Lao characters are 30 bytes; must count as 10 and pass the 10-char minimum
        $data = ['message' => str_repeat('ກ', 10)] + $this->valid();
        $this->assertArrayNotHasKey('message', Validator::check($data, ContactService::RULES));

        $data = ['message' => str_repeat('ກ', 9)] + $this->valid();
        $this->assertSame('too_short', Validator::check($data, ContactService::RULES)['message']);

        $data = ['message' => str_repeat('a', 5001)] + $this->valid();
        $this->assertSame('too_long', Validator::check($data, ContactService::RULES)['message']);

        $data = ['name' => 'A'] + $this->valid();
        $this->assertSame('too_short', Validator::check($data, ContactService::RULES)['name']);
    }

    public function testSubjectMustBeKnownCode(): void
    {
        $data = ['subject' => 'ຝ່າຍຂາຍ'] + $this->valid();
        $this->assertSame('invalid', Validator::check($data, ContactService::RULES)['subject']);
    }

    public function testConsentMustBeTicked(): void
    {
        $data = ['consent' => 'no'] + $this->valid();
        $this->assertSame('required', Validator::check($data, ContactService::RULES)['consent']);
    }

    #[DataProvider('phones')]
    public function testPhone(string $phone, bool $ok): void
    {
        $this->assertSame($ok, Validator::isPhone($phone), $phone);
    }

    public static function phones(): array
    {
        return [
            ['020 5589 2929', true],
            ['+856 20 5589 2929', true],
            ['(856-21) 563121', true],
            ['021.563.121', true],
            ['12345', false],             // too short
            ['call me', false],
            ['020 5589 2929 ext', false],
            ['+-()  ', false],            // no digits
            [str_repeat('1', 31), false], // too long
        ];
    }

    #[DataProvider('emails')]
    public function testEmail(string $email, bool $ok): void
    {
        $this->assertSame($ok, Validator::isEmail($email), $email);
    }

    public static function emails(): array
    {
        return [
            ['a@b.la', true],
            ['first.last+tag@xtklaos.com', true],
            ['no-at-sign', false],
            ['a@b', false],
            ["a@b.la\r\nBcc: x@y.z", false], // header injection
            [str_repeat('a', 180) . '@example.com', false], // over 190 chars
        ];
    }

    public function testCleanSingleLine(): void
    {
        $this->assertSame('ສົມ ໃຈ', Validator::clean("  ສົມ \t\n ໃຈ\x00 "));
    }

    public function testCleanMultilineKeepsLineBreaks(): void
    {
        $this->assertSame("line 1\nline 2\n\nline 3", Validator::clean("line 1\r\nline 2\n\n\n\n\nline 3\x07", true));
    }

    public function testCleanKeepsZeroWidthMarks(): void
    {
        // zero-width space separates Lao/Thai words; it is a format character, not a control character
        $this->assertSame("ສະບາຍ\u{200B}ດີ", Validator::clean("ສະບາຍ\u{200B}ດີ"));
    }
}
