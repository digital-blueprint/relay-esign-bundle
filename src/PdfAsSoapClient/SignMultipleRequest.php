<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignMultipleRequest
{
    protected ?string $verificationLevel = null;
    protected string $connector;

    /**
     * @param SignMultipleFile[] $documents
     */
    public function __construct(
        protected string $requestID,
        Connector $connector,
        protected array $documents = [],
        protected ?string $transactionId = null,
        protected ?string $invokeUrl = null,
        protected ?string $invokeTarget = null,
        protected ?string $invokeErrorUrl = null,
        protected ?string $keyIdentifier = null,
        protected ?PropertyMap $preprocessorArguments = null,
        protected ?PropertyMap $configurationOverrides = null,
        protected array $signatureBlockParameters = [],
    ) {
        $this->setConnector($connector);
    }

    public function __get(string $name): mixed
    {
        // This is used by php-soap when serializing
        if ($name === 'invoke-error-url') {
            return $this->invokeErrorUrl;
        } elseif ($name === 'invoke-target') {
            return $this->invokeTarget;
        } elseif ($name === 'invoke-url') {
            return $this->invokeUrl;
        }

        return null;
    }

    public function addSignatureBlockParameter(string $key, string $value): void
    {
        $this->signatureBlockParameters[] = ['entry' => ['key' => $key, 'value' => $value]];
    }

    public function addDocument(SignMultipleFile $document): void
    {
        $this->documents[] = $document;
    }

    public function getConnector(): Connector
    {
        return Connector::from($this->connector);
    }

    public function setConnector(Connector $connector): void
    {
        $this->connector = $connector->value;
    }

    public function getVerificationLevel(): ?VerificationLevel
    {
        return $this->verificationLevel === null ? null : VerificationLevel::from($this->verificationLevel);
    }

    public function setVerificationLevel(?VerificationLevel $verificationLevel): void
    {
        $this->verificationLevel = $verificationLevel === null ? null : $verificationLevel->value;
    }

    public function getInvokeUrl(): ?string
    {
        return $this->invokeUrl;
    }

    public function setInvokeUrl(?string $invokeUrl): void
    {
        $this->invokeUrl = $invokeUrl;
    }

    public function getInvokeErrorUrl(): ?string
    {
        return $this->invokeErrorUrl;
    }

    public function setInvokeErrorUrl(?string $invokeErrorUrl): void
    {
        $this->invokeErrorUrl = $invokeErrorUrl;
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }
}
