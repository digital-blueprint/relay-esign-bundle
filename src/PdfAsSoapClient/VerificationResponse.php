<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerificationResponse
{
    /**
     * @var int
     */
    protected $certificateCode;

    /**
     * @var string
     */
    protected $signerCertificate;

    /**
     * @var int
     */
    protected $valueCode;

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
     */
    public function setCertificateCode($certificateCode): void
    {
        $this->certificateCode = $certificateCode;
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
     */
    public function setSignerCertificate($signerCertificate): void
    {
        $this->signerCertificate = $signerCertificate;
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
}
