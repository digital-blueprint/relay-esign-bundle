<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class BulkSignResponse
{
    /**
     * @var SignResponse[]
     */
    protected $signResponses = null;

    /**
     * @param SignResponse[] $signResponses
     */
    public function __construct(array $signResponses)
    {
        $this->signResponses = $signResponses;
    }

    /**
     * @return SignResponse[]
     */
    public function getSignResponses()
    {
        return $this->signResponses;
    }

    /**
     * @param SignResponse[] $signResponses
     *
     * @return BulkSignResponse
     */
    public function setSignResponses(array $signResponses)
    {
        $this->signResponses = $signResponses;

        return $this;
    }
}
