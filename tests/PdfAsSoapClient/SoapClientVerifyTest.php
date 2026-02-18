<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsSoapClient;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASVerificationImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyMap;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerificationLevel;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerifyRequest;
use PHPUnit\Framework\TestCase;

class SoapClientVerifyTest extends TestCase
{
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

    /**
     * @return PDFASVerificationImplService
     */
    private function getMockVerificationService(?string $response)
    {
        $soapClientMock = $this->getMockBuilder(PDFASVerificationImplService::class)
            ->setConstructorArgs(['nope', -1, true])
            ->onlyMethods(['__doParentRequest'])
            ->getMock();
        $soapClientMock->method('__doParentRequest')->willReturn($response);

        return $soapClientMock;
    }

    public function testBasicVerify()
    {
        new PDFASVerificationImplService('nope');
        $this->expectNotToPerformAssertions();
    }

    public function testVerify()
    {
        $soapClientMock = $this->getMockVerificationService(self::$FAKE_VERIFY_RESPONSE);

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $request->setVerificationLevel(VerificationLevel::intOnly);
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
        $soapClientMock = $this->getMockVerificationService(null);

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $this->expectException(\SoapFault::class);
        $soapClientMock->verify($request);
    }

    public function testVerifyNoSig()
    {
        $soapClientMock = $this->getMockVerificationService(self::$FAKE_EMPTY_VERIFY_RESPONSE);

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $request->setVerificationLevel(VerificationLevel::intOnly);
        $request->setSignatureIndex(99);
        $request->setPreprocessorArguments(new PropertyMap([]));
        $response = $soapClientMock->verify($request);
        $results = $response->getVerifyResults();
        $this->assertCount(0, $results);
    }

    public function testVerifyFailed()
    {
        $soapClientMock = $this->getMockVerificationService(self::$FAKE_VERIFY_FAILED);

        $request = new VerifyRequest('somebogusdata', 'foobar');
        $this->expectException(\SoapFault::class);
        $this->expectExceptionMessageMatches('/Server Verification failed/');
        $soapClientMock->verify($request);
    }
}
