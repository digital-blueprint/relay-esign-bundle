<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\EsignBundle\Service\PdfAsApi;
use Dbp\Relay\EsignBundle\Service\SigningException;

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
