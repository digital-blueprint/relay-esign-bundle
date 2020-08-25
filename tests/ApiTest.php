<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends ApiTestCase
{
    /** @var Client */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testNotAuth()
    {
        $endpoints = [
            ['POST', '/advancedly_signed_documents/sign'],
            ['GET', '/advancedly_signed_documents'],
            ['GET', '/advancedly_signed_documents/123'],
            ['GET', '/electronic_signature_verification_reports'],
            ['POST', '/electronic_signature_verification_reports/create'],
            ['GET', '/electronic_signature_verification_reports/123'],
            ['GET', '/electronic_signatures/123'],
            ['POST', '/qualified_signing_requests/create'],
            ['GET', '/qualified_signing_requests'],
            ['GET', '/qualified_signing_requests/123'],
            ['GET', '/qualifiedly_signed_documents'],
            ['GET', '/qualifiedly_signed_documents/123'],
        ];

        foreach ($endpoints as [$method, $path]) {
            $response = $this->client->request($method, $path);
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        }
    }
}
