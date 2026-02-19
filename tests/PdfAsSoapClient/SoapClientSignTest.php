<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsSoapClient;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\BulkSignRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\GetMultipleRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASSigningImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignatureBlockParameter;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignMultipleFile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignMultipleRequest;
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

    private static $FAKE_RESPONSE_BULK = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns1:signBulkResponse xmlns:ns1="http://ws.api.pdfas.egiz.gv.at/">
            <bulkResponse>
                <signResponses>
                    <requestID>e8db3773-33e8-4952-a772-752652fead20-0</requestID>
                    <signedPDF>
                        dGVzdA==
                    </signedPDF>
                    <verificationResponse>
                        <certificateCode>0</certificateCode>
                        <signerCertificate>
                            dGVzdA==</signerCertificate>
                        <valueCode>0</valueCode>
                    </verificationResponse>
                </signResponses>
                <signResponses>
                    <requestID>e8db3773-33e8-4952-a772-752652fead20-1</requestID>
                    <signedPDF>
                        dGVzdA==</signedPDF>
                    <verificationResponse>
                        <certificateCode>0</certificateCode>
                        <signerCertificate>
                            dGVzdA==</signerCertificate>
                        <valueCode>0</valueCode>
                    </verificationResponse>
                </signResponses>
            </bulkResponse>
        </ns1:signBulkResponse>
    </soap:Body>
</soap:Envelope>
';

    private static $FAKE_SIGN_MULTIPLE_RESPONSE = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns1:signMultipleResponse xmlns:ns1="http://ws.api.pdfas.egiz.gv.at/">
            <signMultipleResponse>
                <requestID>893e5003-c72e-4938-b2fc-e22bb3d7bfaa</requestID>
                <transactionId xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" />
                <redirectUrl>https://my.pdf.as/pdf-as-web/userentry?reqId=7d2aa85d-35fa-4ce3-9f43-93a8c6790883</redirectUrl>
            </signMultipleResponse>
        </ns1:signMultipleResponse>
    </soap:Body>
</soap:Envelope>';

    private static $FAKE_GET_MULTIPLE_RESPONSE = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns1:getMultipleResponse xmlns:ns1="http://ws.api.pdfas.egiz.gv.at/">
            <getMultipleResponse>
                <requestID>75de7806-b369-4a17-b46e-e548c4549b43</requestID>
                <transactionId xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" />
                <documents>
                    <outputData>c29tZWRhdGE=</outputData>
                    <fileName>75de7806-b369-4a17-b46e-e548c4549b43-0</fileName>
                    <verificationResponse>
                        <certificateCode>99</certificateCode>
                        <valueCode>0</valueCode>
                    </verificationResponse>
                </documents>
                <documents>
                    <outputData>c29tZWRhdGE=</outputData>
                    <fileName>75de7806-b369-4a17-b46e-e548c4549b43-1</fileName>
                    <verificationResponse>
                        <certificateCode>99</certificateCode>
                        <valueCode>0</valueCode>
                    </verificationResponse>
                </documents>
            </getMultipleResponse>
        </ns1:getMultipleResponse>
    </soap:Body>
</soap:Envelope>';

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

    public function testSingleSignXMLOutput()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_RESPONSE);

        $params = new SignParameters(Connector::jks);
        $params->setInvokeTarget('http://invoke-target');
        $params->setInvokeErrorUrl('http://invoke-error-url');
        $params->setInvokeUrl('http://invoke-url');

        $blockParams = [new SignatureBlockParameter('foo1', 'bar1'), new SignatureBlockParameter('foo2', 'bar2')];
        $request = new SignRequest('foobar', $params, 'my-id', $blockParams);
        $response = $soapClientMock->signSingle($request, 42);
        $lastRequest = $soapClientMock->__getLastRequest();
        $this->assertStringContainsString('<signatureBlockParameters><entry><key>foo1</key><value>bar1</value></entry><entry><key>foo2</key><value>bar2</value></entry></signatureBlockParameters>', $lastRequest);
        $this->assertNotFalse($response);
    }

    public function testSignBulk()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_RESPONSE_BULK);

        $params = new SignParameters(Connector::jks);
        $request = new SignRequest('foobar', $params, 'my-id');
        $bulkRequest = new BulkSignRequest([$request, $request]);
        $bulkResponse = $soapClientMock->signBulk($bulkRequest);

        $responses = $bulkResponse->getSignResponses();
        $this->assertCount(2, $responses);

        $this->assertEquals('e8db3773-33e8-4952-a772-752652fead20-0', $responses[0]->getRequestID());
        $this->assertEquals('test', $responses[0]->getSignedPDF());
        $veriResponse = $responses[0]->getVerificationResponse();
        $this->assertEquals('test', $veriResponse->getSignerCertificate());

        $this->assertEquals('e8db3773-33e8-4952-a772-752652fead20-1', $responses[1]->getRequestID());
        $this->assertEquals('test', $responses[1]->getSignedPDF());
        $veriResponse = $responses[1]->getVerificationResponse();
        $this->assertEquals('test', $veriResponse->getSignerCertificate());
    }

    public function testSignMultiple()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_SIGN_MULTIPLE_RESPONSE);

        $file = new SignMultipleFile('data', 'filename');
        $request = new SignMultipleRequest('893e5003-c72e-4938-b2fc-e22bb3d7bfaa', Connector::mobilebku, [$file]);
        $response = $soapClientMock->signMultiple($request);
        $this->assertNull($response->getError());
        $this->assertSame('893e5003-c72e-4938-b2fc-e22bb3d7bfaa', $response->getRequestID());
        $this->assertNull($response->getTransactionId());
        $this->assertSame('https://my.pdf.as/pdf-as-web/userentry?reqId=7d2aa85d-35fa-4ce3-9f43-93a8c6790883', $response->getRedirectUrl());
        $this->assertSame([], $response->getDocuments());
    }

    public function testGetMultiple()
    {
        $soapClientMock = $this->getMockSigningService(self::$FAKE_GET_MULTIPLE_RESPONSE);
        $response = $soapClientMock->getMultiple(new GetMultipleRequest('sometoken'));

        $this->assertNull($response->getError());
        $this->assertSame('75de7806-b369-4a17-b46e-e548c4549b43', $response->getRequestID());
        $this->assertNull($response->getTransactionId());
        $this->assertNull($response->getRedirectUrl());
        $this->assertCount(2, $response->getDocuments());

        $this->assertSame('somedata', $response->getDocuments()[0]->getOutputData());
        $this->assertSame('75de7806-b369-4a17-b46e-e548c4549b43-0', $response->getDocuments()[0]->getFileName());
        $this->assertNull($response->getDocuments()[0]->getVerificationResponse()->getSignerCertificate());
        $this->assertSame(99, $response->getDocuments()[0]->getVerificationResponse()->getCertificateCode());
        $this->assertSame(0, $response->getDocuments()[0]->getVerificationResponse()->getValueCode());

        $this->assertSame('somedata', $response->getDocuments()[1]->getOutputData());
        $this->assertSame('75de7806-b369-4a17-b46e-e548c4549b43-1', $response->getDocuments()[1]->getFileName());
        $this->assertNull($response->getDocuments()[1]->getVerificationResponse()->getSignerCertificate());
        $this->assertSame(99, $response->getDocuments()[1]->getVerificationResponse()->getCertificateCode());
        $this->assertSame(0, $response->getDocuments()[1]->getVerificationResponse()->getValueCode());
    }
}
