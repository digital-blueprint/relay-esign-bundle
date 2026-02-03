<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignParameters
{
    /**
     * @var string
     */
    protected $connector;

    /**
     * @var string
     */
    protected $keyIdentifier;

    /**
     * @var PropertyMap
     */
    protected $configurationOverrides;

    /**
     * @var string
     */
    protected $position;

    /**
     * @var PropertyMap
     */
    protected $preprocessorArguments;

    /**
     * @var string
     */
    protected $profile;

    /**
     * @var string
     */
    protected $qrCodeContent;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $invokeErrorUrl;

    /**
     * @var string
     */
    protected $invokeTarget;

    /**
     * @var string
     */
    protected $invokeUrl;

    /**
     * @param Connector $connector
     */
    public function __construct($connector)
    {
        $this->connector = $connector->value;
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        return Connector::from($this->connector);
    }

    /**
     * @param Connector $connector
     */
    public function setConnector($connector): void
    {
        $this->connector = $connector->value;
    }

    /**
     * @return ?string
     */
    public function getInvokeerrorurl()
    {
        return $this->invokeErrorUrl;
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
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

    /**
     * @param string $invokeErrorUrl
     */
    public function setInvokeErrorUrl($invokeErrorUrl): void
    {
        $this->invokeErrorUrl = $invokeErrorUrl;
    }

    /**
     * @return ?string
     */
    public function getInvokeTarget()
    {
        return $this->invokeTarget;
    }

    /**
     * @param string $invokeTarget
     */
    public function setInvokeTarget($invokeTarget): void
    {
        $this->invokeTarget = $invokeTarget;
    }

    /**
     * @return ?string
     */
    public function getInvokeUrl()
    {
        return $this->invokeUrl;
    }

    /**
     * @param string $invokeUrl
     */
    public function setInvokeUrl($invokeUrl): void
    {
        $this->invokeUrl = $invokeUrl;
    }

    /**
     * @return ?string
     */
    public function getKeyIdentifier()
    {
        return $this->keyIdentifier;
    }

    /**
     * @param string $keyIdentifier
     */
    public function setKeyIdentifier($keyIdentifier): void
    {
        $this->keyIdentifier = $keyIdentifier;
    }

    /**
     * @return ?PropertyMap
     */
    public function getConfigurationOverrides()
    {
        return $this->configurationOverrides;
    }

    /**
     * @param PropertyMap $configurationOverrides
     */
    public function setConfigurationOverrides($configurationOverrides): void
    {
        $this->configurationOverrides = $configurationOverrides;
    }

    /**
     * @return ?string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return ?PropertyMap
     */
    public function getPreprocessorArguments()
    {
        return $this->preprocessorArguments;
    }

    /**
     * @param PropertyMap $preprocessorArguments
     */
    public function setPreprocessorArguments($preprocessorArguments): void
    {
        $this->preprocessorArguments = $preprocessorArguments;
    }

    /**
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param string $profile
     */
    public function setProfile($profile): void
    {
        $this->profile = $profile;
    }

    /**
     * @return string
     */
    public function getQrCodeContent()
    {
        return $this->qrCodeContent;
    }

    /**
     * @param string $qrCodeContent
     */
    public function setQrCodeContent($qrCodeContent): void
    {
        $this->qrCodeContent = $qrCodeContent;
    }

    /**
     * @return ?string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId): void
    {
        $this->transactionId = $transactionId;
    }
}
