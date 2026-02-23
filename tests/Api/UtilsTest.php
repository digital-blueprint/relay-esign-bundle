<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Api\Utils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testUserText()
    {
        $res = Utils::parseUserText('[]');
        $this->assertSame($res, []);
        $res = Utils::parseUserText('[{"description": "desc", "value": "val"}]');
        $this->assertCount(1, $res);
        $this->assertSame($res[0]->getDescription(), 'desc');
        $this->assertSame($res[0]->getValue(), 'val');
    }

    public static function invalidUserText()
    {
        return [['nope'], [''], ['[{}]'], ['[0]']];
    }

    #[DataProvider('invalidUserText')]
    public function testUserTextInvalid(string $input)
    {
        $this->expectException(ApiError::class);
        Utils::parseUserText($input);
    }
}
