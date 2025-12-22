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
     *
     * @return SignParameters
     */
    public function setConnector($connector)
    {
        $this->connector = $connector->value;

        return $this;
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
     *
     * @return SignParameters
     */
    public function setInvokeErrorUrl($invokeErrorUrl)
    {
        $this->invokeErrorUrl = $invokeErrorUrl;

        return $this;
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
     *
     * @return SignParameters
     */
    public function setInvokeTarget($invokeTarget)
    {
        $this->invokeTarget = $invokeTarget;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setInvokeUrl($invokeUrl)
    {
        $this->invokeUrl = $invokeUrl;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setKeyIdentifier($keyIdentifier)
    {
        $this->keyIdentifier = $keyIdentifier;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setConfigurationOverrides($configurationOverrides)
    {
        $this->configurationOverrides = $configurationOverrides;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setPreprocessorArguments($preprocessorArguments)
    {
        $this->preprocessorArguments = $preprocessorArguments;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
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
     *
     * @return ?SignParameters
     */
    public function setQrCodeContent($qrCodeContent)
    {
        $this->qrCodeContent = $qrCodeContent;

        return $this;
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
     *
     * @return SignParameters
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }
}
