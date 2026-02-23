<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignedMultipleFile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignResponse;
use Psr\Http\Message\ResponseInterface;

class PDFDataResponse
{
    public function __construct(private readonly string $signedPDF, private readonly int $valueCode, private readonly int $certificateCode, private readonly ?string $signerCertificate)
    {
    }

    public static function fromResponse(ResponseInterface $response, string $sessionId): PDFDataResponse
    {
        $signedPdfData = (string) $response->getBody();

        if ($response->getHeader('Content-Type')[0] !== 'application/pdf') {
            // PDF-AS doesn't use 404 status code when document wasn't found
            if (strpos($signedPdfData, '<p>No signed pdf document available.</p>') !== false) {
                throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' was not found!", $sessionId));
            }

            throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded!", $sessionId));
        }

        $valueCode = $response->getHeader('ValueCheckCode')[0];
        if (!is_numeric($valueCode)) {
            throw new SigningException('Invalid value code: '.$valueCode);
        }
        $valueCode = (int) $valueCode;

        $certificateCode = $response->getHeader('CertificateCheckCode')[0];
        if (!is_numeric($certificateCode)) {
            throw new SigningException('Invalid certificate code: '.$certificateCode);
        }
        $certificateCode = (int) $certificateCode;

        $signerCertificateBase64 = $response->getHeader('Signer-Certificate')[0];
        $signerCertificate = base64_decode($signerCertificateBase64, true);
        if ($signerCertificate === false) {
            throw new SigningException('Invalid signer certificate: '.$signerCertificateBase64);
        }

        return new PDFDataResponse($signedPdfData, $valueCode, $certificateCode, $signerCertificate);
    }

    public static function fromSoapSignResponse(SignResponse $response): PDFDataResponse
    {
        assert($response->getError() === null);

        $verificationResponse = $response->getVerificationResponse();

        return new PDFDataResponse(
            $response->getSignedPDF(), $verificationResponse->getValueCode(),
            $verificationResponse->getCertificateCode(), $verificationResponse->getSignerCertificate());
    }

    public static function fromSoapSignMultipleResponse(SignedMultipleFile $response): PDFDataResponse
    {
        $verificationResponse = $response->getVerificationResponse();

        return new PDFDataResponse(
            $response->getOutputData(), $verificationResponse->getValueCode(),
            $verificationResponse->getCertificateCode(), $verificationResponse->getSignerCertificate());
    }

    public function getSignedPDF(): string
    {
        return $this->signedPDF;
    }

    public function getValueCode(): int
    {
        return $this->valueCode;
    }

    public function getCertificateCode(): int
    {
        return $this->certificateCode;
    }

    public function getSignerCertificate(): ?string
    {
        return $this->signerCertificate;
    }
}
