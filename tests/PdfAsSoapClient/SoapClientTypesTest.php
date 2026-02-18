<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsSoapClient;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignParameters;
use PHPUnit\Framework\TestCase;

class SoapClientTypesTest extends TestCase
{
    public function testSignParameters()
    {
        $param = new SignParameters(Connector::bku);
        $this->assertNull($param->getInvokeUrl());
        $this->assertNull($param->getInvokeerrorurl());
        $this->assertNull($param->getInvokeTarget());
        $param->setInvokeUrl('url');
        $this->assertEquals('url', $param->getInvokeUrl());
        $param->setInvokeErrorUrl('error-url');
        $this->assertEquals('error-url', $param->getInvokeerrorurl());
        $param->setInvokeTarget('target');
        $this->assertEquals('target', $param->getInvokeTarget());
    }
}
