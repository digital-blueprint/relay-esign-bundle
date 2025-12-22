<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASSigningImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignParameters;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SoapResponseParser;
use PHPUnit\Framework\TestCase;

class SoapClientSignTest extends TestCase
{
    private static $FAKE_RESPONSE = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ns1:signSingleResponse xmlns:ns1="http://ws.api.pdfas.egiz.gv.at/">
      <signResponse>
        <requestID>some-request-id</requestID>
        <signedPDF>bm90YXJlYWxyZXNwb25zZQ==</signedPDF>
        <verificationResponse>
          <certificateCode>99</certificateCode>
          <signerCertificate>bm90YXJlYWxjZXJ0</signerCertificate>
          <valueCode>0</valueCode>
        </verificationResponse>
      </signResponse>
    </ns1:signSingleResponse>
  </soap:Body>
</soap:Envelope>
';

    private function getExampleMTOMResponse(): string
    {
        $smallExamplePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'mtom-response-example-small.bin';

        return file_get_contents($smallExamplePath);
    }

    public function testBasicSign()
    {
        $service = new PDFASSigningImplService('nope');
        $this->assertNotNull($service);
    }

    /**
     * @return PDFASSigningImplService
     */
    private function getMockSigningService(?string $response)
    {
        $soapClientMock = $this->getMockBuilder(PDFASSigningImplService::class)
            ->setConstructorArgs(['nope', -1, true])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->willReturn($response);

        return $soapClientMock;
    }

    public function testSingleSignParams()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_RESPONSE);

        $params = new SignParameters(Connector::jks);
        $params->setInvokeErrorUrl('https://XXXXXXXXXXXXXXXXXXXXXX');
        $request = new SignRequest('foobar', $params, 'my-id');
        $response = $soapClientMock->signSingle($request);

        $this->assertEquals('some-request-id', $response->getRequestID());
        $this->assertEquals('notarealresponse', $response->getSignedPDF());
        $veriResponse = $response->getVerificationResponse();
        $this->assertEquals('notarealcert', $veriResponse->getSignerCertificate());
    }

    public function testSingleSignParamsWithExample()
    {
        $smallExample = $this->getExampleMTOMResponse();
        $soapClientMock = $this->getMockSigningService($smallExample);

        $params = new SignParameters(Connector::jks);
        $request = new SignRequest('foobar', $params, 'my-id');
        $response = $soapClientMock->signSingle($request);
        $pdf = $response->getSignedPDF();
        $this->assertEquals('%PDF-1.5', substr($pdf, 0, strlen('%PDF-1.5')));
        $this->assertEquals("%EOF\n", substr($pdf, strlen($pdf) - 5));
    }

    public function testMTOMXMLParser()
    {
        $smallExample = $this->getExampleMTOMResponse();
        $parser = new SoapResponseParser();
        $xml = $parser->parse($smallExample);
        $parsed = simplexml_load_string($xml);
        $this->assertNotFalse($parsed);
    }

    public function testMTOMXMLParserLeadingNewlines()
    {
        // https://gitlab.tugraz.at/dbp/esign/dbp-relay-esign-bundle/-/issues/2
        $smallExample = "\r\n".$this->getExampleMTOMResponse();
        $parser = new SoapResponseParser();
        $xml = $parser->parse($smallExample);
        $parsed = simplexml_load_string($xml);
        $this->assertNotFalse($parsed);
    }

    public function testSingleSignNullResult()
    {
        $soapClientMock = $this->getMockSigningService(null);

        $params = new SignParameters(Connector::jks);
        $request = new SignRequest('foobar', $params, 'my-id');
        $this->expectException(\SoapFault::class);
        $soapClientMock->signSingle($request);
    }

    public function testSingleSignWithTimeout()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_RESPONSE);

        $params = new SignParameters(Connector::jks);
        $request = new SignRequest('foobar', $params, 'my-id');
        $response = $soapClientMock->signSingle($request, 42);
        $this->assertNotFalse($response);
    }

    public function testSingleSignWithSpecialSignParams()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_RESPONSE);

        $params = new SignParameters(Connector::jks);
        $params->setInvokeTarget('http://invoke-target');
        $params->setInvokeErrorUrl('http://invoke-error-url');
        $params->setInvokeUrl('http://invoke-url');

        $request = new SignRequest('foobar', $params, 'my-id');
        $response = $soapClientMock->signSingle($request, 42);
        $lastRequest = $soapClientMock->__getLastRequest();

        // make sure they end up in the request, despite being magic properties
        $this->assertStringContainsString('http://invoke-target', $lastRequest);
        $this->assertStringContainsString('http://invoke-error-url', $lastRequest);
        $this->assertStringContainsString('http://invoke-url', $lastRequest);

        $this->assertNotFalse($response);
    }
}
