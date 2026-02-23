<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignParameters
{
    protected string $connector;

    protected ?string $keyIdentifier = null;

    protected ?PropertyMap $configurationOverrides = null;

    protected ?string $position = null;

    protected ?PropertyMap $preprocessorArguments = null;

    protected ?string $profile = null;

    protected ?string $qrCodeContent = null;

    protected ?string $transactionId = null;

    protected ?string $invokeErrorUrl = null;

    protected ?string $invokeTarget = null;

    protected ?string $invokeUrl = null;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector->value;
    }

    public function getConnector(): Connector
    {
        return Connector::from($this->connector);
    }

    public function setConnector(Connector $connector): void
    {
        $this->connector = $connector->value;
    }

    public function getInvokeErrorUrl(): ?string
    {
        return $this->invokeErrorUrl;
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

    public function setInvokeErrorUrl(string $invokeErrorUrl): void
    {
        $this->invokeErrorUrl = $invokeErrorUrl;
    }

    public function getInvokeTarget(): ?string
    {
        return $this->invokeTarget;
    }

    public function setInvokeTarget(string $invokeTarget): void
    {
        $this->invokeTarget = $invokeTarget;
    }

    public function getInvokeUrl(): ?string
    {
        return $this->invokeUrl;
    }

    public function setInvokeUrl(string $invokeUrl): void
    {
        $this->invokeUrl = $invokeUrl;
    }

    public function getKeyIdentifier(): ?string
    {
        return $this->keyIdentifier;
    }

    public function setKeyIdentifier(?string $keyIdentifier): void
    {
        $this->keyIdentifier = $keyIdentifier;
    }

    public function getConfigurationOverrides(): ?PropertyMap
    {
        return $this->configurationOverrides;
    }

    public function setConfigurationOverrides(?PropertyMap $configurationOverrides): void
    {
        $this->configurationOverrides = $configurationOverrides;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getPreprocessorArguments(): ?PropertyMap
    {
        return $this->preprocessorArguments;
    }

    public function setPreprocessorArguments(?PropertyMap $preprocessorArguments): void
    {
        $this->preprocessorArguments = $preprocessorArguments;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(?string $profile): void
    {
        $this->profile = $profile;
    }

    public function getQrCodeContent(): ?string
    {
        return $this->qrCodeContent;
    }

    public function setQrCodeContent(?string $qrCodeContent): void
    {
        $this->qrCodeContent = $qrCodeContent;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }
}
