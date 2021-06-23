<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use DBP\API\ESignBundle\Service\PdfAsApi;
use DBP\API\ESignBundle\Service\SigningException;

class PdfAsApiTest extends ApiTestCase
{
    public function testRequiredRoleUnknownProfile()
    {
        $api = new PdfAsApi();
        $api->setConfig(['advanced_profiles' => [['name' => 'foo']]]);
        $this->expectException(SigningException::class);
        $api->getAdvancedlySignRequiredRole('somename');
    }

    public function testRequiredRoleNoRole()
    {
        $api = new PdfAsApi();
        $api->setConfig(['advanced_profiles' => [['name' => 'somename']]]);
        $this->expectException(SigningException::class);
        $api->getAdvancedlySignRequiredRole('somename');
    }

    public function testRequiredRole()
    {
        $api = new PdfAsApi();
        $api->setConfig(['advanced_profiles' => [['name' => 'somename', 'role' => 'somerole']]]);
        $role = $api->getAdvancedlySignRequiredRole('somename');
        $this->assertSame('somerole', $role);
    }
}
