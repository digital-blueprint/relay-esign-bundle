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
            ['POST', '/advancedly_signed_documents?profile=foo', 403],
            ['GET', '/advancedly_signed_documents', 200],
            ['GET', '/advancedly_signed_documents/123', 404],
            ['GET', '/electronic_signature_verification_reports', 403],
            ['POST', '/electronic_signature_verification_reports', 403],
            ['GET', '/electronic_signature_verification_reports/123', 404],
            ['GET', '/electronic_signatures/123', 404],
            ['POST', '/qualified_signing_requests', 403],
            ['GET', '/qualified_signing_requests', 403],
            ['GET', '/qualified_signing_requests/123', 404],
            ['GET', '/qualifiedly_signed_documents', 403],
            ['GET', '/qualifiedly_signed_documents/123', 403],
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
