<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerifyRequest
{
    /**
     * @var string
     */
    protected $inputData;

    /**
     * @var PropertyMap
     */
    protected $preprocessorArguments;

    /**
     * @var string
     */
    protected $requestID;

    /**
     * @var int
     */
    protected $signatureIndex;

    /**
     * @var string
     */
    protected $verificationLevel;

    /**
     * @param string $inputData
     * @param string $requestID
     */
    public function __construct($inputData, $requestID)
    {
        $this->inputData = $inputData;
        $this->requestID = $requestID;
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
     */
    public function setInputData($inputData): void
    {
        $this->inputData = $inputData;
    }

    /**
     * @return PropertyMap
     */
    public function getPreprocessorArguments()
    {
        return $this->preprocessorArguments;
    }

    /**
     * @param PropertyMap $preprocessorArguments
     */
    public function setPreprocessorArguments($preprocessorArguments): void
    {
        $this->preprocessorArguments = $preprocessorArguments;
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
     */
    public function setRequestID($requestID): void
    {
        $this->requestID = $requestID;
    }

    /**
     * @return int
     */
    public function getSignatureIndex()
    {
        return $this->signatureIndex;
    }

    /**
     * @param int $signatureIndex
     */
    public function setSignatureIndex($signatureIndex): void
    {
        $this->signatureIndex = $signatureIndex;
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
     */
    public function setVerificationLevel($verificationLevel): void
    {
        $this->verificationLevel = $verificationLevel->value;
    }
}
