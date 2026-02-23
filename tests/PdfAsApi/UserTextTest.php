<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\AdvancedProfile;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningException;
use Dbp\Relay\EsignBundle\PdfAsApi\UserDefinedText;
use Dbp\Relay\EsignBundle\PdfAsApi\UserText;
use PHPUnit\Framework\TestCase;

class UserTextTest extends TestCase
{
    public function testUserText()
    {
        $profileConfig = new AdvancedProfile([
            'profile_id' => 'someid',
            'user_text' => [
                'target_table' => 'target-table',
                'target_row' => 1,
            ],
        ]);

        $overrides = UserText::buildUserTextConfigOverride($profileConfig, [new UserDefinedText('foo', 'bar')]);

        $this->assertCount(3, $overrides);
        $this->assertSame('sig_obj.someid.key.SIG_USER_TEXT_target-table_1', $overrides[0]->getKey());
        $this->assertSame('foo', $overrides[0]->getValue());
        $this->assertSame('sig_obj.someid.value.SIG_USER_TEXT_target-table_1', $overrides[1]->getKey());
        $this->assertSame('bar', $overrides[1]->getValue());
        $this->assertSame('sig_obj.someid.table.target-table.1', $overrides[2]->getKey());
        $this->assertSame('SIG_USER_TEXT_target-table_1-cv', $overrides[2]->getValue());

        $overrides = UserText::buildUserTextConfigOverride($profileConfig, []);
        $this->assertCount(0, $overrides);
    }

    public function testUserTextAttach()
    {
        $profileConfig = new AdvancedProfile([
            'profile_id' => 'someid',
            'user_text' => [
                'target_table' => 'target-table',
                'target_row' => 1,
                'attach' => [
                    'parent_table' => 'parent-table',
                    'child_table' => 'child-table',
                    'parent_row' => 42,
                ],
            ],
        ]);

        $overrides = UserText::buildUserTextConfigOverride($profileConfig, [new UserDefinedText('foo', 'bar')]);

        $this->assertCount(4, $overrides);
        $this->assertSame('sig_obj.someid.key.SIG_USER_TEXT_target-table_1', $overrides[0]->getKey());
        $this->assertSame('foo', $overrides[0]->getValue());
        $this->assertSame('sig_obj.someid.value.SIG_USER_TEXT_target-table_1', $overrides[1]->getKey());
        $this->assertSame('bar', $overrides[1]->getValue());
        $this->assertSame('sig_obj.someid.table.target-table.1', $overrides[2]->getKey());
        $this->assertSame('SIG_USER_TEXT_target-table_1-cv', $overrides[2]->getValue());
        $this->assertSame('sig_obj.someid.table.parent-table.42', $overrides[3]->getKey());
        $this->assertSame('TABLE-child-table', $overrides[3]->getValue());

        $overrides = UserText::buildUserTextConfigOverride($profileConfig, []);
        $this->assertCount(0, $overrides);
    }

    public function testUserImage()
    {
        $profileConfig = new AdvancedProfile([
            'profile_id' => 'someid',
        ]);

        $png = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x00\x00\x00\x00:~\x9bU\x00\x00\x00\nIDAT\x08\x1dc\xf8\x0f\x00\x01\x01\x01\x006_g\x80\x00\x00\x00\x00IEND\xaeB`\x82";
        $entry = UserText::buildUserImageConfigOverride($profileConfig, $png);
        $this->assertSame('sig_obj.someid.value.SIG_LABEL', $entry->getKey());
        $this->assertSame(base64_encode($png), $entry->getValue());
    }

    public function testUserImageInvalid()
    {
        $profileConfig = new AdvancedProfile([
            'profile_id' => 'someid',
        ]);

        $png = 'this-is-not-a-png';
        $this->expectException(SigningException::class);
        UserText::buildUserImageConfigOverride($profileConfig, $png);
    }

    public function testUserIamgeTooLarge()
    {
        $profileConfig = new AdvancedProfile([
            'profile_id' => 'someid',
        ]);

        $png = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x00\x00\x00\x00:~\x9bU\x00\x00\x00\nIDAT\x08\x1dc\xf8\x0f\x00\x01\x01\x01\x006_g\x80\x00\x00\x00\x00IEND\xaeB`\x82";
        $png = str_repeat($png, 10000);
        $this->expectException(SigningException::class);
        UserText::buildUserImageConfigOverride($profileConfig, $png);
    }
}
