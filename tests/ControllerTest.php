<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\Controller\BaseSigningController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ControllerTest extends TestCase
{
    public function testUserText()
    {
        $res = BaseSigningController::parseUserText('[]');
        $this->assertSame($res, []);
        $res = BaseSigningController::parseUserText('[{"description": "desc", "value": "val"}]');
        $this->assertCount(1, $res);
        $this->assertSame($res[0]->getDescription(), 'desc');
        $this->assertSame($res[0]->getValue(), 'val');
    }

    public static function invalidUserText()
    {
        return [['nope'], [''], ['[{}]'], ['[0]']];
    }

    /**
     * @dataProvider invalidUserText
     */
    public function testUserTextInvalid(string $input)
    {
        $this->expectException(HttpException::class);
        BaseSigningController::parseUserText($input);
    }
}
