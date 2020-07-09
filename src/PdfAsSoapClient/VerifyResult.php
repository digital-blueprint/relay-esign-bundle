<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class VerifyResult
{
    /**
     * @var string $certificate
     */
    protected $certificate = null;

    /**
     * @var int $certificateCode
     */
    protected $certificateCode = null;

    /**
     * @var string $certificateMessage
     */
    protected $certificateMessage = null;

    /**
     * @var string $error
     */
    protected $error = null;

    /**
     * @var boolean $processed
     */
    protected $processed = null;

    /**
     * @var string $requestID
     */
    protected $requestID = null;

    /**
     * @var int $signatureIndex
     */
    protected $signatureIndex = null;

    /**
     * @var string $signedBy
     */
    protected $signedBy = null;

    /**
     * @var string $signedData
     */
    protected $signedData = null;

    /**
     * @var int $valueCode
     */
    protected $valueCode = null;

    /**
     * @var string $valueMessage
     */
    protected $valueMessage = null;

    /**
     * @param string $certificate
     * @param int $certificateCode
     * @param string $certificateMessage
     * @param string $error
     * @param boolean $processed
     * @param string $requestID
     * @param int $signatureIndex
     * @param string $signedBy
     * @param string $signedData
     * @param int $valueCode
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
     * @return VerifyResult
     */
    public function setCertificate($certificate)
    {
      $this->certificate = $certificate;
      return $this;
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
     * @return VerifyResult
     */
    public function setCertificateCode($certificateCode)
    {
      $this->certificateCode = $certificateCode;
      return $this;
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
     * @return VerifyResult
     */
    public function setCertificateMessage($certificateMessage)
    {
      $this->certificateMessage = $certificateMessage;
      return $this;
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
     * @return VerifyResult
     */
    public function setError($error)
    {
      $this->error = $error;
      return $this;
    }

    /**
     * @return boolean
     */
    public function getProcessed()
    {
      return $this->processed;
    }

    /**
     * @param boolean $processed
     * @return VerifyResult
     */
    public function setProcessed($processed)
    {
      $this->processed = $processed;
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
     * @return VerifyResult
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
     * @return VerifyResult
     */
    public function setSignatureIndex($signatureIndex)
    {
      $this->signatureIndex = $signatureIndex;
      return $this;
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
     * @return VerifyResult
     */
    public function setSignedBy($signedBy)
    {
      $this->signedBy = $signedBy;
      return $this;
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
     * @return VerifyResult
     */
    public function setSignedData($signedData)
    {
      $this->signedData = $signedData;
      return $this;
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
     * @return VerifyResult
     */
    public function setValueCode($valueCode)
    {
      $this->valueCode = $valueCode;
      return $this;
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
     * @return VerifyResult
     */
    public function setValueMessage($valueMessage)
    {
      $this->valueMessage = $valueMessage;
      return $this;
    }

}
