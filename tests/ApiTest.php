<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\CoreBundle\TestUtils\UserAuthTrait;

class ApiTest extends ApiTestCase
{
    use UserAuthTrait;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testNotAuth()
    {
        $endpoints = [
            ['POST', '/esign/advancedly_signed_documents?profile=foo', 403],
            ['GET', '/esign/advancedly_signed_documents', 200],
            ['GET', '/esign/advancedly_signed_documents/123', 404],
            ['GET', '/esign/electronic_signature_verification_reports', 403],
            ['POST', '/esign/electronic_signature_verification_reports', 403],
            ['GET', '/esign/electronic_signature_verification_reports/123', 404],
            ['GET', '/esign/electronic_signatures/123', 404],
            ['POST', '/esign/qualified_signing_requests?profile=foo', 403],
            ['GET', '/esign/qualified_signing_requests', 200],
            ['GET', '/esign/qualified_signing_requests/123', 404],
            ['GET', '/esign/qualifiedly_signed_documents', 200],
            ['GET', '/esign/qualifiedly_signed_documents/123', 502],
        ];

        foreach ($endpoints as $ep) {
            [$method, $path, $status] = $ep;
            $client = $this->withUser('foobar', [], '42');
            $response = $client->request($method, $path, ['headers' => [
                'Authorization' => 'Bearer 42',
            ]]);

            $this->assertEquals($status, $response->getStatusCode(), $path);

            // Without any token
            $response = $client->request($method, $path);
            $this->assertContains($response->getStatusCode(), [401, 404, 403], $path);
        }
    }
}
