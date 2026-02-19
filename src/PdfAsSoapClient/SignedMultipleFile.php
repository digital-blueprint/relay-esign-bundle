<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignedMultipleFile
{
    public function __construct(
        protected string $outputData,
        protected ?string $fileName = null,
        protected ?VerificationResponse $verificationResponse = null
    ) {
    }

    public function getOutputData(): string
    {
        return $this->outputData;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getVerificationResponse(): ?VerificationResponse
    {
        return $this->verificationResponse;
    }
}
