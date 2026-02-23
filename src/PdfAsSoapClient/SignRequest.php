<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignRequest
{
    protected string $inputData;

    protected SignParameters $parameters;

    protected string $requestID;

    protected ?string $verificationLevel = null;

    /**
     * @var SignatureBlockParameter[]
     */
    protected array $signatureBlockParameters;

    public function __construct(string $inputData, SignParameters $parameters, string $requestID, $signatureBlockParameters = [])
    {
        $this->inputData = $inputData;
        $this->parameters = $parameters;
        $this->requestID = $requestID;
        $this->signatureBlockParameters = $signatureBlockParameters;
    }

    public function getInputData(): string
    {
        return $this->inputData;
    }

    public function setInputData(string $inputData): void
    {
        $this->inputData = $inputData;
    }

    public function getParameters(): SignParameters
    {
        return $this->parameters;
    }

    public function setParameters(SignParameters $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }

    public function getVerificationLevel(): ?VerificationLevel
    {
        return $this->verificationLevel === null ? null : VerificationLevel::from($this->verificationLevel);
    }

    public function setVerificationLevel(?VerificationLevel $verificationLevel): void
    {
        $this->verificationLevel = $verificationLevel === null ? null : $verificationLevel->value;
    }

    /**
     * @return SignatureBlockParameter[]
     */
    public function getSignatureBlockParameters(): array
    {
        return $this->signatureBlockParameters;
    }

    /**
     * @param SignatureBlockParameter[] $signatureBlockParameters
     */
    public function setSignatureBlockParameters(array $signatureBlockParameters): void
    {
        $this->signatureBlockParameters = $signatureBlockParameters;
    }
}
