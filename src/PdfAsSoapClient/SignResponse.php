<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignResponse
{
    protected ?string $error = null;

    protected ?string $redirectUrl = null;

    protected string $requestID;

    protected ?string $signedPDF = null;

    protected ?VerificationResponse $verificationResponse = null;

    public function __construct(string $requestID)
    {
        $this->requestID = $requestID;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }

    public function getSignedPDF(): ?string
    {
        return $this->signedPDF;
    }

    public function setSignedPDF(?string $signedPDF): void
    {
        $this->signedPDF = $signedPDF;
    }

    public function getVerificationResponse(): ?VerificationResponse
    {
        return $this->verificationResponse;
    }

    public function setVerificationResponse(?VerificationResponse $verificationResponse): void
    {
        $this->verificationResponse = $verificationResponse;
    }
}
