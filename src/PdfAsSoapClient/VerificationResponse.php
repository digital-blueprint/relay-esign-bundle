<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class VerificationResponse
{

    /**
     * @var int $certificateCode
     */
    protected $certificateCode = null;

    /**
     * @var string $signerCertificate
     */
    protected $signerCertificate = null;

    /**
     * @var int $valueCode
     */
    protected $valueCode = null;

    /**
     * @param int $certificateCode
     * @param int $valueCode
     */
    public function __construct($certificateCode, $valueCode)
    {
      $this->certificateCode = $certificateCode;
      $this->valueCode = $valueCode;
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
     * @return VerificationResponse
     */
    public function setCertificateCode($certificateCode)
    {
      $this->certificateCode = $certificateCode;
      return $this;
    }

    /**
     * @return string
     */
    public function getSignerCertificate()
    {
      return $this->signerCertificate;
    }

    /**
     * @param string $signerCertificate
     * @return VerificationResponse
     */
    public function setSignerCertificate($signerCertificate)
    {
      $this->signerCertificate = $signerCertificate;
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
     * @return VerificationResponse
     */
    public function setValueCode($valueCode)
    {
      $this->valueCode = $valueCode;
      return $this;
    }

}
