<?php

namespace DBP\API\ESignBundle\Tests;

use DBP\API\ESignBundle\Entity\ElectronicSignature;
use DBP\API\ESignBundle\Entity\ElectronicSignatureVerificationReport;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EntityTest extends WebTestCase
{
    public function testAll() {
        $sig = new ElectronicSignature();
        $this->assertNotNull($sig);

        $report = new ElectronicSignatureVerificationReport();
        $this->assertNotNull($report);
    }
}