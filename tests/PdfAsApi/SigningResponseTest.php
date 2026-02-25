<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\PdfAsApi;

use Dbp\Relay\EsignBundle\PdfAsApi\SigningResponse;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignedMultipleFile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignMultipleResponse;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignResponse;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerificationResponse;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SigningResponseTest extends TestCase
{
    public function testFromResponse(): void
    {
        $pdfContent = '%PDF-1.4 fake signed pdf content';
        $valueCode = 0;
        $certificateCode = 0;
        $signerCertificate = 'this-is-a-fake-certificate';
        $signerCertificateBase64 = base64_encode($signerCertificate);
        $sessionId = 'test-session-123';

        $response = new Response(200, [
            'Content-Type' => 'application/pdf',
            'ValueCheckCode' => (string) $valueCode,
            'CertificateCheckCode' => (string) $certificateCode,
            'Signer-Certificate' => $signerCertificateBase64,
        ], $pdfContent);

        $result = SigningResponse::fromPdfDataResponse($response, $sessionId);

        $this->assertSame($pdfContent, $result->getSignedPDF());
        $this->assertSame($valueCode, $result->getValueCode());
        $this->assertSame($certificateCode, $result->getCertificateCode());
        $this->assertSame($signerCertificate, $result->getSignerCertificate());
    }

    public function testFromSoapSingleResponse(): void
    {
        $pdfContent = '%PDF-1.4 fake signed pdf content';
        $valueCode = 0;
        $certificateCode = 0;
        $signerCertificate = 'this-is-a-fake-certificate';

        $verificationResponse = new VerificationResponse(0, 0);
        $verificationResponse->setSignerCertificate($signerCertificate);

        $soapResponse = new SignResponse('some-id');
        $soapResponse->setError(null);
        $soapResponse->setSignedPDF($pdfContent);
        $soapResponse->setVerificationResponse($verificationResponse);

        $result = SigningResponse::fromSoapSignResponse($soapResponse);

        $this->assertSame($pdfContent, $result->getSignedPDF());
        $this->assertSame($valueCode, $result->getValueCode());
        $this->assertSame($certificateCode, $result->getCertificateCode());
        $this->assertSame($signerCertificate, $result->getSignerCertificate());
    }

    public function testFromSoapMultiResponse(): void
    {
        $pdfContent = '%PDF-1.4 fake signed pdf content';
        $valueCode = 0;
        $certificateCode = 0;
        $verificationResponse = new VerificationResponse(0, 0);

        $soapDocument = new SignedMultipleFile($pdfContent, 'filename', $verificationResponse);
        $soapResponse = new SignMultipleResponse('42', documents: [$soapDocument]);
        $result = SigningResponse::fromSoapSignMultipleResponse($soapResponse);

        $this->assertSame($pdfContent, $result[0]->getSignedPDF());
        $this->assertSame($valueCode, $result[0]->getValueCode());
        $this->assertSame($certificateCode, $result[0]->getCertificateCode());
        $this->assertNull($result[0]->getSignerCertificate());
    }
}
