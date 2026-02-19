<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignMultipleResponse
{
    protected ?string $error = null;
    protected array $documents = [];
    protected ?string $redirectUrl = null;

    public function __construct(
        protected string $requestID,
        protected ?string $transactionId = null,
        $redirectUrl = null,
        $documents = [],
    ) {
        $this->documents = $documents;
        $this->redirectUrl = $redirectUrl;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @return SignedMultipleFile[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}
