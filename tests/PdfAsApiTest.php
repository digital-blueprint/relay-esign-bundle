<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\Service\PdfAsApi;
use Symfony\Component\Stopwatch\Stopwatch;

class PdfAsApiTest extends ApiTestCase
{
    public function getApi($config): PdfAsApi
    {
        $router = $this->getContainer()->get('router');

        return new PdfAsApi(new Stopwatch(), $router, new BundleConfig($config));
    }

    public function testGetCallbackUrl()
    {
        $api = $this->getApi([]);
        $this->assertSame($api->getCallbackUrl(), 'http://localhost/esign/_success');
        $api = $this->getApi(['qualified_signature' => ['callback_url' => 'https://foo.bar']]);
        $this->assertSame($api->getCallbackUrl(), 'https://foo.bar');
    }

    public function testGetErrorCallbackUrl()
    {
        $api = $this->getApi([]);
        $this->assertSame($api->getErrorCallbackUrl(), 'http://localhost/esign/_error');
        $api = $this->getApi(['qualified_signature' => ['error_callback_url' => 'https://foo.bar.error']]);
        $this->assertSame($api->getErrorCallbackUrl(), 'https://foo.bar.error');
    }
}
