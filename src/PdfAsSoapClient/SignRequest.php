<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignRequest
{
    /**
     * @var string
     */
    protected $inputData = null;

    /**
     * @var SignParameters
     */
    protected $parameters = null;

    /**
     * @var string
     */
    protected $requestID = null;

    /**
     * @var VerificationLevel
     */
    protected $verificationLevel = null;

    /**
     * @param string         $inputData
     * @param SignParameters $parameters
     * @param string         $requestID
     */
    public function __construct($inputData, $parameters, $requestID)
    {
        $this->inputData = $inputData;
        $this->parameters = $parameters;
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
        return $this->verificationLevel;
    }

    /**
     * @param VerificationLevel $verificationLevel
     *
     * @return SignRequest
     */
    public function setVerificationLevel($verificationLevel)
    {
        $this->verificationLevel = $verificationLevel;

        return $this;
    }
}
