<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerifyRequest
{
    protected string $inputData;

    protected ?PropertyMap $preprocessorArguments;

    protected string $requestID;

    protected ?int $signatureIndex;

    protected ?string $verificationLevel;

    public function __construct(string $inputData, string $requestID)
    {
        $this->inputData = $inputData;
        $this->requestID = $requestID;
    }

    public function getInputData(): string
    {
        return $this->inputData;
    }

    public function setInputData(string $inputData): void
    {
        $this->inputData = $inputData;
    }

    public function getPreprocessorArguments(): ?PropertyMap
    {
        return $this->preprocessorArguments;
    }

    public function setPreprocessorArguments(?PropertyMap $preprocessorArguments): void
    {
        $this->preprocessorArguments = $preprocessorArguments;
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }

    public function getSignatureIndex(): ?int
    {
        return $this->signatureIndex;
    }

    public function setSignatureIndex(?int $signatureIndex): void
    {
        $this->signatureIndex = $signatureIndex;
    }

    public function getVerificationLevel(): ?VerificationLevel
    {
        return $this->verificationLevel === null ? null : VerificationLevel::from($this->verificationLevel);
    }

    public function setVerificationLevel(?VerificationLevel $verificationLevel): void
    {
        $this->verificationLevel = $verificationLevel === null ? null : $verificationLevel->value;
    }
}
