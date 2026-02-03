<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignRequest
{
    /**
     * @var string
     */
    protected $inputData;

    /**
     * @var SignParameters
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $requestID;

    /**
     * @var string
     */
    protected $verificationLevel;

    /**
     * @var SignatureBlockParameter[]
     */
    protected $signatureBlockParameters;

    /**
     * @param string                    $inputData
     * @param SignParameters            $parameters
     * @param string                    $requestID
     * @param SignatureBlockParameter[] $signatureBlockParameters
     */
    public function __construct($inputData, $parameters, $requestID, $signatureBlockParameters = [])
    {
        $this->inputData = $inputData;
        $this->parameters = $parameters;
        $this->requestID = $requestID;
        $this->signatureBlockParameters = $signatureBlockParameters;
    }

    /**
     * @return string
     */
    public function getInputData()
    {
        return $this->inputData;
    }

    /**
     * @param string $inputData
     *
     * @return SignRequest
     */
    public function setInputData($inputData)
    {
        $this->inputData = $inputData;

        return $this;
    }

    /**
     * @return SignParameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param SignParameters $parameters
     *
     * @return SignRequest
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestID()
    {
        return $this->requestID;
    }

    /**
     * @param string $requestID
     *
     * @return SignRequest
     */
    public function setRequestID($requestID)
    {
        $this->requestID = $requestID;

        return $this;
    }

    /**
     * @return VerificationLevel
     */
    public function getVerificationLevel()
    {
        return VerificationLevel::from($this->verificationLevel);
    }

    /**
     * @param VerificationLevel $verificationLevel
     *
     * @return SignRequest
     */
    public function setVerificationLevel($verificationLevel)
    {
        $this->verificationLevel = $verificationLevel->value;

        return $this;
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
