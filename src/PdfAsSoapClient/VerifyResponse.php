<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class VerifyResponse
{
    /**
     * @var VerifyResult[]
     */
    protected array $verifyResults = [];

    /**
     * @param VerifyResult[] $verifyResults
     */
    public function __construct(array $verifyResults)
    {
        $this->verifyResults = $verifyResults;
    }

    public function getVerifyResults(): array
    {
        return $this->verifyResults;
    }

    /**
     * @param VerifyResult[] $verifyResults
     */
    public function setVerifyResults(array $verifyResults): void
    {
        $this->verifyResults = $verifyResults;
    }
}
