<?php

declare(strict_types=1);

use Dbp\Relay\EsignBundle\Configuration\AdvancedProfile;
use Dbp\Relay\EsignBundle\Service\UserDefinedText;
use Dbp\Relay\EsignBundle\Service\UserText;

class UserTextTest extends PHPUnit\Framework\TestCase
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
}
