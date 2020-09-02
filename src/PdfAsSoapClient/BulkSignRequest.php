<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class BulkSignRequest
{
    /**
     * @var SignRequest[]
     */
    protected $signRequests = null;

    /**
     * @param SignRequest[] $signRequests
     */
    public function __construct(array $signRequests)
    {
        $this->signRequests = $signRequests;
    }

    /**
     * @return SignRequest[]
     */
    public function getSignRequests()
    {
        return $this->signRequests;
    }

    /**
     * @param SignRequest[] $signRequests
     *
     * @return BulkSignRequest
     */
    public function setSignRequests(array $signRequests)
    {
        $this->signRequests = $signRequests;

        return $this;
    }
}
