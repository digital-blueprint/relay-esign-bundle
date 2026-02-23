<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
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
        $this->assertSame($api->getCallbackUrl('foo'), 'http://localhost/esign/_success?_dbpRelayEsignId=foo');
    }

    public function testGetErrorCallbackUrl()
    {
        $api = $this->getApi([]);
        $this->assertSame($api->getErrorCallbackUrl('foo'), 'http://localhost/esign/_error?_dbpRelayEsignId=foo');
    }

    public function testFetchQualifiedlySignedDocument()
    {
        $api = $this->getApi(['qualified_signature' => ['server_url' => 'https://foo.bar']]);
        $smallExamplePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'mtom-response-example-small.pdf';
        $content = file_get_contents($smallExamplePath);
        $this->assertNotFalse($content);
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline;filename=document.pdf',
            'ValueCheckCode' => '0',
            'CertificateCheckCode' => '99',
            'Signer-Certificate' => base64_encode('not-a-real-cert'),
        ];
        $handler = new MockHandler([new Response(200, $headers, $content)]);
        $result = $api->fetchQualifiedlySignedDocument('1234', $handler);
        $this->assertSame($content, $result->getSignedPDF());
    }

    public function testFetchQualifiedlySignedDocumentNotFound()
    {
        $api = $this->getApi(['qualified_signature' => ['server_url' => 'https://foo.bar']]);
        // In case of an error we get back 200 (yay...), so we have to detect things differently
        $headers = [
            'Content-Type' => 'text/html;charset=ISO-8859-1',
        ];
        $content = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n
<html lang=\"de\">\n
<head>\n
<title>Error Page</title>\n
</head>\n
<body>\n
Error Page:\n
    <p>No signed pdf document available.</p>\n
<p></p>\n
</body>\n
</html>\n
";
        $handler = new MockHandler([new Response(200, $headers, $content)]);
        $this->expectExceptionMessageMatches('/was not found/');
        $api->fetchQualifiedlySignedDocument('1234', $handler);
    }
}
