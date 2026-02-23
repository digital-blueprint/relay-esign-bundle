<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerifyResult
{
    protected string $certificate;

    protected int $certificateCode;

    protected string $certificateMessage;

    protected string $error;

    protected bool $processed;

    protected string $requestID;
    protected int $signatureIndex;

    protected string $signedBy;

    protected string $signedData;

    protected int $valueCode;

    protected string $valueMessage;

    public function __construct(string $certificate, int $certificateCode, string $certificateMessage,
        string $error, bool $processed, string $requestID, int $signatureIndex,
        string $signedBy, string $signedData, int $valueCode, string $valueMessage)
    {
        $this->certificate = $certificate;
        $this->certificateCode = $certificateCode;
        $this->certificateMessage = $certificateMessage;
        $this->error = $error;
        $this->processed = $processed;
        $this->requestID = $requestID;
        $this->signatureIndex = $signatureIndex;
        $this->signedBy = $signedBy;
        $this->signedData = $signedData;
        $this->valueCode = $valueCode;
        $this->valueMessage = $valueMessage;
    }

    public function getCertificate(): string
    {
        return $this->certificate;
    }

    public function setCertificate(string $certificate): void
    {
        $this->certificate = $certificate;
    }

    public function getCertificateCode(): int
    {
        return $this->certificateCode;
    }

    public function setCertificateCode(int $certificateCode): void
    {
        $this->certificateCode = $certificateCode;
    }

    public function getCertificateMessage(): string
    {
        return $this->certificateMessage;
    }

    public function setCertificateMessage(string $certificateMessage): void
    {
        $this->certificateMessage = $certificateMessage;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
    }

    public function getProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }

    public function getSignatureIndex(): int
    {
        return $this->signatureIndex;
    }

    public function setSignatureIndex(int $signatureIndex): void
    {
        $this->signatureIndex = $signatureIndex;
    }

    public function getSignedBy(): string
    {
        return $this->signedBy;
    }

    public function setSignedBy(string $signedBy): void
    {
        $this->signedBy = $signedBy;
    }

    public function getSignedData(): string
    {
        return $this->signedData;
    }

    public function setSignedData(string $signedData): void
    {
        $this->signedData = $signedData;
    }

    public function getValueCode(): int
    {
        return $this->valueCode;
    }

    public function setValueCode(int $valueCode): void
    {
        $this->valueCode = $valueCode;
    }

    public function getValueMessage(): string
    {
        return $this->valueMessage;
    }

    public function setValueMessage(string $valueMessage): void
    {
        $this->valueMessage = $valueMessage;
    }
}
