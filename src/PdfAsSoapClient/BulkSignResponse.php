<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class BulkSignResponse
{
    /**
     * @var SignResponse[]
     */
    protected array $signResponses;

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
     */
    public function setSignResponses(array $signResponses): void
    {
        $this->signResponses = $signResponses;
    }
}
