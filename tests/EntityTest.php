<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\Entity\ElectronicSignature;
use Dbp\Relay\EsignBundle\Entity\ElectronicSignatureVerificationReport;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EntityTest extends WebTestCase
{
    public function testAll()
    {
        $sig = new ElectronicSignature();
        $this->assertNotNull($sig);

        $report = new ElectronicSignatureVerificationReport();
        $this->assertNotNull($report);
    }
}
