<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\AdvancedProfile;
use Dbp\Relay\EsignBundle\Configuration\QualifiedProfile;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SignatureReason;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningRequest;
use Dbp\Relay\EsignBundle\PdfAsApi\SystemDefinedText;
use Dbp\Relay\EsignBundle\PdfAsApi\UserDefinedText;
use PHPUnit\Framework\TestCase;

class SignatureReasonTest extends TestCase
{
    private static function normalizeReasonForPreview(string $reason): string
    {
        // Adobe turns CRLF into spaces and removes lone LF in the reason preview.
        return str_replace(["\r\n", "\r", "\n"], [' ', '', ''], $reason);
    }

    public function testInvisibleSignatureIncludesFormattedReason(): void
    {
        $profile = new AdvancedProfile([
            'profile_id' => 'MYPROFILE',
            'system_text' => ['target_table' => 'system', 'target_row' => 1],
            'user_text' => [
                'target_table' => 'user',
                'target_row' => 1,
                'separator' => true,
                'attach' => ['parent_table' => 'parent', 'child_table' => 'user', 'parent_row' => 2],
            ],
        ]);
        $request = new SigningRequest('pdf', 'profile', 'id',
            userText: [new UserDefinedText('Reference', '123'), new UserDefinedText('Logo', 'image data', UserDefinedText::$TYPE_IMAGE)],
            invisible: true,
            systemText: ['name' => new SystemDefinedText('Signer', "Jane Doe,\nDr.")],
        );

        $entries = PdfAsApi::buildConfigurationOverrides($profile, $request, 'Additional information', 'Date/Time-UTC')->getPropertyEntries();
        $reason = end($entries);
        $this->assertSame('sig_obj.MYPROFILE.adobeSignReasonValue', $reason->getKey());
        $this->assertSame("Signer: Jane Doe,\r\nDr.;\r\nAdditional information:\r\nReference: 123", $reason->getValue());
        $this->assertSame('Signer: Jane Doe, Dr.; Additional information: Reference: 123', self::normalizeReasonForPreview($reason->getValue()));
        $this->assertSame(
            ['sig_obj.MYPROFILE.isvisible', 'sig_obj.MYPROFILE.adobeSignReasonValue'],
            array_map(static fn ($entry) => $entry->getKey(), $entries));
    }

    public function testEmptyContentDoesNotOverrideProfileReason(): void
    {
        $profile = new AdvancedProfile(['profile_id' => 'MYPROFILE']);
        $request = new SigningRequest('pdf', 'profile', 'id', invisible: true);

        $entries = PdfAsApi::buildConfigurationOverrides($profile, $request)->getPropertyEntries();
        $this->assertNull(SignatureReason::build($profile, $request));
        $this->assertCount(1, $entries);
        $this->assertSame('sig_obj.MYPROFILE.isvisible', $entries[0]->getKey());
    }

    public function testInvisibleProfileIncludesTextWithoutLayouts(): void
    {
        $profile = new AdvancedProfile(['profile_id' => 'MYPROFILE', 'invisible' => true, 'include_username' => true]);
        $request = new SigningRequest('pdf', 'profile', 'id',
            userText: [new UserDefinedText('Reference', '123')],
            systemText: ['name' => new SystemDefinedText('Signer', 'Jane Doe')]);

        $entries = PdfAsApi::buildConfigurationOverrides($profile, $request)->getPropertyEntries();

        $this->assertCount(1, $entries);
        $this->assertSame('sig_obj.MYPROFILE.adobeSignReasonValue', $entries[0]->getKey());
        $this->assertSame("Signer: Jane Doe;\r\nReference: 123", $entries[0]->getValue());
    }

    public function testAdvancedSignatureIncludesUserTextWithoutSystemText(): void
    {
        $profile = new AdvancedProfile([
            'profile_id' => 'MYPROFILE',
            'user_text' => [
                'target_table' => 'user',
                'target_row' => 1,
                'separator' => true,
                'attach' => ['parent_table' => 'parent', 'child_table' => 'user', 'parent_row' => 2],
            ],
        ]);
        $request = new SigningRequest('pdf', 'profile', 'id', userText: [new UserDefinedText('Reference', '123')]);

        $entries = PdfAsApi::buildConfigurationOverrides($profile, $request, 'Additional information')->getPropertyEntries();
        $reason = end($entries);
        $this->assertSame('sig_obj.MYPROFILE.adobeSignReasonValue', $reason->getKey());
        $this->assertSame("Additional information:\r\nReference: 123", $reason->getValue());
        $this->assertSame('Additional information: Reference: 123', self::normalizeReasonForPreview($reason->getValue()));
    }

    public function testNameOnlyIsFormatted(): void
    {
        $profile = new AdvancedProfile(['profile_id' => 'MYPROFILE']);
        $request = new SigningRequest('pdf', 'profile', 'id', systemText: ['name' => new SystemDefinedText('Signer', 'Jane Doe')]);

        $this->assertSame('Signer: Jane Doe', SignatureReason::build($profile, $request));
    }

    public function testMultipleSystemFieldsAreSeparated(): void
    {
        $profile = new AdvancedProfile(['profile_id' => 'MYPROFILE']);
        $request = new SigningRequest('pdf', 'profile', 'id', systemText: [
            'name' => new SystemDefinedText('Signer', 'Jane Doe'),
            'department' => new SystemDefinedText('Department', 'Library'),
        ]);

        $reason = SignatureReason::build($profile, $request);
        $this->assertSame("Signer: Jane Doe;\r\nDepartment: Library", $reason);
        $this->assertSame('Signer: Jane Doe; Department: Library', self::normalizeReasonForPreview($reason));
    }

    public function testMultilineUserValueIsPreserved(): void
    {
        $profile = new AdvancedProfile(['profile_id' => 'MYPROFILE']);
        $request = new SigningRequest('pdf', 'profile', 'id', userText: [
            new UserDefinedText('Notes', "First line\n\nSecond line"),
            new UserDefinedText('Reference', '123'),
        ]);

        $reason = SignatureReason::build($profile, $request);
        $this->assertSame("Notes: First line\r\n\r\nSecond line;\r\nReference: 123", $reason);
        $this->assertSame('Notes: First line  Second line; Reference: 123', self::normalizeReasonForPreview($reason));
    }

    public function testWindowsNewlinesInUserValueAreNotDuplicated(): void
    {
        $profile = new AdvancedProfile(['profile_id' => 'MYPROFILE']);
        $request = new SigningRequest('pdf', 'profile', 'id', userText: [
            new UserDefinedText('Notes', "First  line\r\nSecond  line"),
        ]);

        $reason = SignatureReason::build($profile, $request);
        $this->assertSame("Notes: First  line\r\nSecond  line", $reason);
        $this->assertSame('Notes: First  line Second  line', self::normalizeReasonForPreview($reason));
    }

    public function testImageOnlyDoesNotOverrideProfileReason(): void
    {
        $profile = new AdvancedProfile([
            'profile_id' => 'MYPROFILE',
            'user_text' => ['target_table' => 'user', 'target_row' => 1],
        ]);
        $request = new SigningRequest('pdf', 'profile', 'id', userText: [new UserDefinedText('Logo', 'image data', UserDefinedText::$TYPE_IMAGE)]);

        $entries = PdfAsApi::buildConfigurationOverrides($profile, $request)->getPropertyEntries();
        $this->assertNull(SignatureReason::build($profile, $request));
        $this->assertNotContains('sig_obj.MYPROFILE.adobeSignReasonValue', array_map(static fn ($entry) => $entry->getKey(), $entries));
    }

    public function testQualifiedSignatureIncludesReason(): void
    {
        $profile = new QualifiedProfile([
            'profile_id' => 'MYPROFILE',
            'user_text' => ['target_table' => 'user', 'target_row' => 1],
        ]);
        $request = new SigningRequest('pdf', 'profile', 'id', userText: [new UserDefinedText('Reference', '123')]);

        $entries = PdfAsApi::buildConfigurationOverrides($profile, $request)->getPropertyEntries();
        $reason = end($entries);
        $this->assertSame('sig_obj.MYPROFILE.adobeSignReasonValue', $reason->getKey());
        $this->assertSame('Reference: 123', $reason->getValue());
    }

    public function testQualifiedBatchWithoutTextAllowsDifferentProfiles(): void
    {
        $request = new SigningRequest('pdf', 'profile', 'id');
        $first = PdfAsApi::buildConfigurationOverrides(new QualifiedProfile(['profile_id' => 'FIRST']), $request);
        $same = PdfAsApi::buildConfigurationOverrides(new QualifiedProfile(['profile_id' => 'FIRST']), $request);
        $second = PdfAsApi::buildConfigurationOverrides(new QualifiedProfile(['profile_id' => 'SECOND']), $request);

        $this->assertTrue(PdfAsApi::propertyMapIsEqual($first, $same));
        $this->assertTrue(PdfAsApi::propertyMapIsEqual($first, $second));
        $this->assertSame([], $first->getPropertyEntries());
    }
}
