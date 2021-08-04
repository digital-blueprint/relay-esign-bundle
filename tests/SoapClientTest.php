<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASSigningImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASVerificationImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyMap;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignParameters;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SoapResponseParser;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerificationLevel;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerifyRequest;
use PHPUnit\Framework\TestCase;

class SoapClientTest extends TestCase
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

    private static $FAKE_VERIFY_RESPONSE = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns1:verifyResponse xmlns:ns1="http://ws.api.pdfas.egiz.gv.at/">
            <verifyResponse>
                <verifyResults>
                    <certificate>signCert?SIGID=0</certificate>
                    <certificateCode>99</certificateCode>
                    <certificateMessage>not checked</certificateMessage>
                    <processed>true</processed>
                    <requestID>foobar</requestID>
                    <signatureIndex>0</signatureIndex>
                    <signedBy>serialNumber=790413824466,givenName=Erika,SN=Mustermann,CN=Erika Mustermann,C=AT</signedBy>
                    <signedData>signData?SIGID=0</signedData>
                    <valueCode>0</valueCode>
                    <valueMessage>OK</valueMessage>
                </verifyResults>
                <verifyResults>
                    <certificate>signCert?SIGID=1</certificate>
                    <certificateCode>99</certificateCode>
                    <certificateMessage>not checked</certificateMessage>
                    <processed>true</processed>
                    <requestID>foobar</requestID>
                    <signatureIndex>1</signatureIndex>
                    <signedBy>serialNumber=522116084353,givenName=Max,SN=Mustermann,CN=Max Mustermann,C=AT</signedBy>
                    <signedData>signData?SIGID=1</signedData>
                    <valueCode>0</valueCode>
                    <valueMessage>OK</valueMessage>
                </verifyResults>
            </verifyResponse>
        </ns1:verifyResponse>
    </soap:Body>
</soap:Envelope>
';

    private static $FAKE_EMPTY_VERIFY_RESPONSE = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <ns1:verifyResponse xmlns:ns1="http://ws.api.pdfas.egiz.gv.at/">
            <verifyResponse/>
        </ns1:verifyResponse>
    </soap:Body>
</soap:Envelope>
';

    private static $FAKE_VERIFY_FAILED = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>soap:Server</faultcode>
            <faultstring>Server Verification failed.</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
';

    private function getExampleMTOMResponse(): string
    {
        $smallExamplePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'mtom-response-example-small.bin';

        return file_get_contents($smallExamplePath);
    }

    public function testSignParameters()
    {
        $param = new SignParameters(Connector::bku());
        $this->assertNull($param->getInvokeurl());
        $this->assertNull($param->getInvokeerrorurl());
        $this->assertNull($param->getInvoketarget());
        $param->setInvokeurl('url');
        $this->assertEquals('url', $param->getInvokeurl());
        $param->setInvokeerrorurl('error-url');
        $this->assertEquals('error-url', $param->getInvokeerrorurl());
        $param->setInvoketarget('target');
        $this->assertEquals('target', $param->getInvoketarget());
    }

    public function testBasicSign()
    {
        $service = new PDFASSigningImplService('nope');
        $this->assertNotNull($service);
    }

    public function testBasicVerify()
    {
        $service = new PDFASVerificationImplService('nope');
        $this->assertNotNull($service);
    }

    public function testVerify()
    {
        $soapClientMock = $this->getMockBuilder(PDFASVerificationImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(self::$FAKE_VERIFY_RESPONSE));
        /* @var $soapClientMock PDFASVerificationImplService */

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $request->setVerificationLevel(VerificationLevel::intOnly());
        $request->setSignatureIndex(-1);
        $request->setPreprocessorArguments(new PropertyMap([]));
        $response = $soapClientMock->verify($request);
        $results = $response->getVerifyResults();
        $this->assertCount(2, $results);
        $this->assertEquals('serialNumber=522116084353,givenName=Max,SN=Mustermann,CN=Max Mustermann,C=AT',
            $results[1]->getSignedBy());
    }

    public function testNoResponse()
    {
        $soapClientMock = $this->getMockBuilder(PDFASVerificationImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(null));
        /* @var $soapClientMock PDFASVerificationImplService */

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $this->expectException(\SoapFault::class);
        $soapClientMock->verify($request);
    }

    public function testVerifyNoSig()
    {
        $soapClientMock = $this->getMockBuilder(PDFASVerificationImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(self::$FAKE_EMPTY_VERIFY_RESPONSE));
        /* @var $soapClientMock PDFASVerificationImplService */

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $request->setVerificationLevel(VerificationLevel::intOnly());
        $request->setSignatureIndex(99);
        $request->setPreprocessorArguments(new PropertyMap([]));
        $response = $soapClientMock->verify($request);
        $results = $response->getVerifyResults();
        $this->assertCount(0, $results);
    }

    public function testVerifyFailed()
    {
        $soapClientMock = $this->getMockBuilder(PDFASVerificationImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(self::$FAKE_VERIFY_FAILED));
        /* @var $soapClientMock PDFASVerificationImplService */

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $this->expectException(\SoapFault::class);
        $this->expectExceptionMessageMatches('/Server Verification failed/');
        $soapClientMock->verify($request);
    }

    public function testSingleSignParams()
    {
        $soapClientMock = $this->getMockBuilder(PDFASSigningImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(self::$FAKE_RESPONSE));

        $params = new SignParameters(Connector::jks());
        $request = new SignRequest('foobar', $params, 'my-id');
        /* @var $soapClientMock PDFASSigningImplService */
        $response = $soapClientMock->signSingle($request);
        $this->assertEquals('some-request-id', $response->getRequestID());
        $this->assertEquals('notarealresponse', $response->getSignedPDF());
        $veriResponse = $response->getVerificationResponse();
        $this->assertEquals('notarealcert', $veriResponse->getSignerCertificate());
    }

    public function testSingleSignParamsWithExample()
    {
        $smallExample = $this->getExampleMTOMResponse();
        $soapClientMock = $this->getMockBuilder(PDFASSigningImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue($smallExample));

        $params = new SignParameters(Connector::jks());
        $request = new SignRequest('foobar', $params, 'my-id');
        /* @var $soapClientMock PDFASSigningImplService */
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

    public function testSingleSignNullResult()
    {
        $soapClientMock = $this->getMockBuilder(PDFASSigningImplService::class)
            ->setConstructorArgs(['nope'])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(null));

        $params = new SignParameters(Connector::jks());
        $request = new SignRequest('foobar', $params, 'my-id');
        $this->expectException(\SoapFault::class);
        /* @var $soapClientMock PDFASSigningImplService */
        $soapClientMock->signSingle($request);
    }

    public function testSingleSignWithTimeout()
    {
        $soapClientMock = $this->getMockBuilder(PDFASSigningImplService::class)
            ->setConstructorArgs(['nope', 42])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->will($this->returnValue(self::$FAKE_RESPONSE));

        $params = new SignParameters(Connector::jks());
        $request = new SignRequest('foobar', $params, 'my-id');
        /* @var $soapClientMock PDFASSigningImplService */
        $response = $soapClientMock->signSingle($request, 42);
        $this->assertNotFalse($response);
    }
}
