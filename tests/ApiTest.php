<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
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
            ['POST', '/esign/advancedly-signed-documents?profile=foo', 403],
            ['GET', '/esign/advancedly-signed-documents', 200],
            ['GET', '/esign/advancedly-signed-documents/123', 404],
            ['GET', '/esign/electronic-signature-verification-reports', 403],
            ['POST', '/esign/electronic-signature-verification-reports', 403],
            ['GET', '/esign/electronic-signature-verification-reports/123', 404],
            ['GET', '/esign/electronic-signatures/123', 404],
            ['POST', '/esign/qualified-signing-requests?profile=foo', 403],
            ['GET', '/esign/qualified-signing-requests', 200],
            ['GET', '/esign/qualified-signing-requests/123', 404],
            ['GET', '/esign/qualifiedly-signed-documents', 200],
            ['GET', '/esign/qualifiedly-signed-documents/123', 502],
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
