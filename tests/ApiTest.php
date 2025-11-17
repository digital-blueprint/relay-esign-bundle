<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\CoreBundle\TestUtils\AbstractApiTest;

class ApiTest extends AbstractApiTest
{
    public function testNotAuth()
    {
        $endpoints = [
            ['POST', '/esign/advancedly-signed-documents?profile=foo', 403],
            ['GET', '/esign/advancedly-signed-documents', 200],
            ['GET', '/esign/advancedly-signed-documents/123', 404],
            ['GET', '/esign/electronic-signature-verification-reports', 403],
            ['POST', '/esign/electronic-signature-verification-reports', 403],
            ['GET', '/esign/electronic-signature-verification-reports/123', 403],
            ['GET', '/esign/electronic-signatures/123', 403],
            ['POST', '/esign/qualified-signing-requests?profile=foo', 403],
            ['GET', '/esign/qualified-signing-requests', 200],
            ['GET', '/esign/qualified-signing-requests/123', 404],
            ['GET', '/esign/qualifiedly-signed-documents', 200],
            ['GET', '/esign/qualifiedly-signed-documents/123', 403],
        ];

        foreach ($endpoints as $ep) {
            [$method, $path, $status] = $ep;
            $this->testClient->setUpUser('foobar', token: '42');
            if ($method === 'POST') {
                $headers['Content-Type'] = 'multipart/form-data';
            }
            $response = $this->testClient->request($method, $path, token: '42');
            $this->assertEquals($status, $response->getStatusCode(), $path);

            // Without any token
            $response = $this->testClient->request($method, $path, token: null);
            $this->assertContains($response->getStatusCode(), [401, 404, 403], $path);
        }
    }
}
