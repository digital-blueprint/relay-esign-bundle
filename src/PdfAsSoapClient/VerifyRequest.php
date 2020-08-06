<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class VerifyRequest
{
    /**
     * @var string
     */
    protected $inputData = null;

    /**
     * @var PropertyMap
     */
    protected $preprocessorArguments = null;

    /**
     * @var string
     */
    protected $requestID = null;

    /**
     * @var int
     */
    protected $signatureIndex = null;

    /**
     * @var VerificationLevel
     */
    protected $verificationLevel = null;

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
     *
     * @return VerifyRequest
     */
    public function setInputData($inputData)
    {
        $this->inputData = $inputData;

        return $this;
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
     *
     * @return VerifyRequest
     */
    public function setPreprocessorArguments($preprocessorArguments)
    {
        $this->preprocessorArguments = $preprocessorArguments;

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
     * @return VerifyRequest
     */
    public function setRequestID($requestID)
    {
        $this->requestID = $requestID;

        return $this;
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
     *
     * @return VerifyRequest
     */
    public function setSignatureIndex($signatureIndex)
    {
        $this->signatureIndex = $signatureIndex;

        return $this;
    }

    /**
     * @return VerificationLevel
     */
    public function getVerificationLevel()
    {
        return $this->verificationLevel;
    }

    /**
     * @param VerificationLevel $verificationLevel
     *
     * @return VerifyRequest
     */
    public function setVerificationLevel($verificationLevel)
    {
        $this->verificationLevel = $verificationLevel;

        return $this;
    }
}
