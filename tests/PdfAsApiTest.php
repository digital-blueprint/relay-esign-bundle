<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\EsignBundle\Service\PdfAsApi;
use Dbp\Relay\EsignBundle\Service\SigningException;
use Symfony\Component\Stopwatch\Stopwatch;

class PdfAsApiTest extends ApiTestCase
{
    private $api;

    public function setUp(): void
    {
        $this->api = new PdfAsApi(new Stopwatch());
    }

    public function testRequiredRoleUnknownProfile()
    {
        $api = $this->api;
        $api->setConfig(['advanced_signature' => ['profiles' => [['name' => 'foo']]]]);
        $this->expectException(SigningException::class);
        $api->getAdvancedlySignRequiredRole('somename');
    }

    public function testRequiredRoleNoRole()
    {
        $api = $this->api;
        $api->setConfig(['advanced_signature' => ['profiles' => [['name' => 'somename']]]]);
        $this->expectException(SigningException::class);
        $api->getAdvancedlySignRequiredRole('somename');
    }

    public function testRequiredRole()
    {
        $api = $this->api;
        $api->setConfig(['advanced_signature' => ['profiles' => [['name' => 'somename', 'role' => 'somerole']]]]);
        $role = $api->getAdvancedlySignRequiredRole('somename');
        $this->assertSame('somerole', $role);
    }

    public function testRequiredRoleUnknownProfileQual()
    {
        $api = $this->api;
        $api->setConfig(['qualified_signature' => ['profiles' => [['name' => 'foo']]]]);
        $this->expectException(SigningException::class);
        $api->getQualifiedlySignRequiredRole('somename');
    }

    public function testRequiredRoleNoRoleQual()
    {
        $api = $this->api;
        $api->setConfig(['qualified_signature' => ['profiles' => [['name' => 'somename']]]]);
        $this->expectException(SigningException::class);
        $api->getQualifiedlySignRequiredRole('somename');
    }

    public function testRequiredRoleQual()
    {
        $api = $this->api;
        $api->setConfig(['qualified_signature' => ['profiles' => [['name' => 'somename', 'role' => 'somerole']]]]);
        $role = $api->getQualifiedlySignRequiredRole('somename');
        $this->assertSame('somerole', $role);
    }
}
