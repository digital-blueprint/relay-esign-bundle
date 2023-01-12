<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignParameters;
use PHPUnit\Framework\TestCase;

class SoapClientTypesTest extends TestCase
{
    public function testSignParameters()
    {
        $param = new SignParameters(Connector::bku());
        $this->assertNull($param->getInvokeurl());
        $this->assertNull($param->getInvokeerrorurl());
        $this->assertNull($param->getInvoketarget());
        $param->setInvokeurl('url');
        $this->assertEquals('url', $param->getInvokeurl());
        $param->setInvokeerrorurl('error-url');
        $this->assertEquals('error-url', $param->getInvokeerrorurl());
        $param->setInvoketarget('target');
        $this->assertEquals('target', $param->getInvoketarget());
    }
}
