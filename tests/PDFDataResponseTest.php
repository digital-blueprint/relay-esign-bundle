<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignResponse;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerificationResponse;
use Dbp\Relay\EsignBundle\Service\PDFDataResponse;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PDFDataResponseTest extends TestCase
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

        $result = PDFDataResponse::fromResponse($response, $sessionId);

        $this->assertSame($pdfContent, $result->getSignedPDF());
        $this->assertSame($valueCode, $result->getValueCode());
        $this->assertSame($certificateCode, $result->getCertificateCode());
        $this->assertSame($signerCertificate, $result->getSignerCertificate());
    }

    public function testFromSoapResponse(): void
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

        $result = PDFDataResponse::fromSoapResponse($soapResponse);

        $this->assertSame($pdfContent, $result->getSignedPDF());
        $this->assertSame($valueCode, $result->getValueCode());
        $this->assertSame($certificateCode, $result->getCertificateCode());
        $this->assertSame($signerCertificate, $result->getSignerCertificate());
    }
}
