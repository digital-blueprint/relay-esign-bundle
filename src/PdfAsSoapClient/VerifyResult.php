<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerifyResult
{
    /**
     * @var string
     */
    protected $certificate;

    /**
     * @var int
     */
    protected $certificateCode;

    /**
     * @var string
     */
    protected $certificateMessage;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var bool
     */
    protected $processed;

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
    protected $signedBy;

    /**
     * @var string
     */
    protected $signedData;

    /**
     * @var int
     */
    protected $valueCode;

    /**
     * @var string
     */
    protected $valueMessage;

    /**
     * @param string $certificate
     * @param int    $certificateCode
     * @param string $certificateMessage
     * @param string $error
     * @param bool   $processed
     * @param string $requestID
     * @param int    $signatureIndex
     * @param string $signedBy
     * @param string $signedData
     * @param int    $valueCode
     * @param string $valueMessage
     */
    public function __construct($certificate, $certificateCode, $certificateMessage, $error, $processed, $requestID, $signatureIndex, $signedBy, $signedData, $valueCode, $valueMessage)
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

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     */
    public function setCertificate($certificate): void
    {
        $this->certificate = $certificate;
    }

    /**
     * @return int
     */
    public function getCertificateCode()
    {
        return $this->certificateCode;
    }

    /**
     * @param int $certificateCode
     */
    public function setCertificateCode($certificateCode): void
    {
        $this->certificateCode = $certificateCode;
    }

    /**
     * @return string
     */
    public function getCertificateMessage()
    {
        return $this->certificateMessage;
    }

    /**
     * @param string $certificateMessage
     */
    public function setCertificateMessage($certificateMessage): void
    {
        $this->certificateMessage = $certificateMessage;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }

    /**
     * @return bool
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param bool $processed
     */
    public function setProcessed($processed): void
    {
        $this->processed = $processed;
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
     * @return string
     */
    public function getSignedBy()
    {
        return $this->signedBy;
    }

    /**
     * @param string $signedBy
     */
    public function setSignedBy($signedBy): void
    {
        $this->signedBy = $signedBy;
    }

    /**
     * @return string
     */
    public function getSignedData()
    {
        return $this->signedData;
    }

    /**
     * @param string $signedData
     */
    public function setSignedData($signedData): void
    {
        $this->signedData = $signedData;
    }

    /**
     * @return int
     */
    public function getValueCode()
    {
        return $this->valueCode;
    }

    /**
     * @param int $valueCode
     */
    public function setValueCode($valueCode): void
    {
        $this->valueCode = $valueCode;
    }

    /**
     * @return string
     */
    public function getValueMessage()
    {
        return $this->valueMessage;
    }

    /**
     * @param string $valueMessage
     */
    public function setValueMessage($valueMessage): void
    {
        $this->valueMessage = $valueMessage;
    }
}
