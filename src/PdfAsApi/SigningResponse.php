<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignMultipleResponse;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignResponse;
use Psr\Http\Message\ResponseInterface;

class SigningResponse
{
    public function __construct(private readonly string $signedPDF, private readonly int $valueCode, private readonly int $certificateCode, private readonly ?string $signerCertificate)
    {
    }

    public static function fromPdfDataResponse(ResponseInterface $response, string $sessionId): SigningResponse
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

        return new SigningResponse($signedPdfData, $valueCode, $certificateCode, $signerCertificate);
    }

    public static function fromSoapSignResponse(SignResponse $response): SigningResponse
    {
        if ($response->getError() !== null) {
            throw new SigningException('Signing failed!');
        }

        $signedPdfData = $response->getSignedPDF();
        if ($signedPdfData === null) {
            // This likely means pdf-as failed uncontrolled (check the logs)
            throw new SigningException('Signing failed!');
        }

        $verificationResponse = $response->getVerificationResponse();

        return new SigningResponse(
            $response->getSignedPDF(), $verificationResponse->getValueCode(),
            $verificationResponse->getCertificateCode(), $verificationResponse->getSignerCertificate());
    }

    /**
     * @return SigningResponse[]
     */
    public static function fromSoapSignMultipleResponse(SignMultipleResponse $response): array
    {
        if ($response->getError() !== null) {
            throw new SigningException('Signing failed!');
        }

        $responses = [];
        foreach ($response->getDocuments() as $document) {
            $verificationResponse = $document->getVerificationResponse();
            $responses[] = new SigningResponse(
                $document->getOutputData(), $verificationResponse->getValueCode(),
                $verificationResponse->getCertificateCode(), $verificationResponse->getSignerCertificate());
        }

        return $responses;
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
