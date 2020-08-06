<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class VerifyResponse
{
    /**
     * @var VerifyResult[]
     */
    protected $verifyResults = null;

    /**
     * @param VerifyResult[] $verifyResults
     */
    public function __construct(array $verifyResults)
    {
        $this->verifyResults = $verifyResults;
    }

    /**
     * @return VerifyResult[]
     */
    public function getVerifyResults()
    {
        if ($this->verifyResults === null) {
            return [];
        }

        return $this->verifyResults;
    }

    /**
     * @param VerifyResult[] $verifyResults
     *
     * @return VerifyResponse
     */
    public function setVerifyResults(array $verifyResults)
    {
        $this->verifyResults = $verifyResults;

        return $this;
    }
}
