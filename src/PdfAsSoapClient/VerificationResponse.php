<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerificationResponse
{
    protected int $certificateCode;

    protected ?string $signerCertificate = null;

    protected int $valueCode;

    public function __construct(int $certificateCode, int $valueCode)
    {
        $this->certificateCode = $certificateCode;
        $this->valueCode = $valueCode;
    }

    public function getCertificateCode(): int
    {
        return $this->certificateCode;
    }

    public function setCertificateCode(int $certificateCode): void
    {
        $this->certificateCode = $certificateCode;
    }

    public function getSignerCertificate(): ?string
    {
        return $this->signerCertificate;
    }

    public function setSignerCertificate(?string $signerCertificate): void
    {
        $this->signerCertificate = $signerCertificate;
    }

    public function getValueCode(): int
    {
        return $this->valueCode;
    }

    public function setValueCode(int $valueCode): void
    {
        $this->valueCode = $valueCode;
    }
}
