<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class SignParameters
{
    /**
     * @var Connector $connector
     */
    protected $connector = null;

    /**
     * @var string $keyIdentifier
     */
    protected $keyIdentifier = null;

    /**
     * @var PropertyMap $configurationOverrides
     */
    protected $configurationOverrides = null;

    /**
     * @var string $position
     */
    protected $position = null;

    /**
     * @var PropertyMap $preprocessorArguments
     */
    protected $preprocessorArguments = null;

    /**
     * @var string $profile
     */
    protected $profile = null;

    /**
     * @var string $qrCodeContent
     */
    protected $qrCodeContent = null;

    /**
     * @var string $transactionId
     */
    protected $transactionId = null;

    /**
     * @param Connector $connector
     * @psalm-suppress UndefinedThisPropertyAssignment
     */
    public function __construct($connector)
    {
        $this->connector = $connector;

        $this->{'invoke-error-url'} = null;
        $this->{'invoke-target'} = null;
        $this->{'invoke-url'} = null;
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
      return $this->connector;
    }

    /**
     * @param Connector $connector
     * @return SignParameters
     */
    public function setConnector($connector)
    {
      $this->connector = $connector;
      return $this;
    }

    /**
     * @return string
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function getInvokeerrorurl()
    {
        return $this->{'invoke-error-url'};
    }

    /**
     * @param string $invokeerrorurl
     * @return SignParameters
     * @psalm-suppress UndefinedThisPropertyAssignment
     */
    public function setInvokeerrorurl($invokeerrorurl)
    {
        $this->{'invoke-error-url'} = $invokeerrorurl;
        return $this;
    }

    /**
     * @return string
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function getInvoketarget()
    {
        return $this->{'invoke-target'};
    }

    /**
     * @param string $invoketarget
     * @return SignParameters
     * @psalm-suppress UndefinedThisPropertyAssignment
     */
    public function setInvoketarget($invoketarget)
    {
      $this->{'invoke-target'} = $invoketarget;
      return $this;
    }

    /**
     * @return string
     * @psalm-suppress UndefinedThisPropertyFetch
     */
    public function getInvokeurl()
    {
      return $this->{'invoke-url'};
    }

    /**
     * @param string $invokeurl
     * @return SignParameters
     * @psalm-suppress UndefinedThisPropertyAssignment
     */
    public function setInvokeurl($invokeurl)
    {
      $this->{'invoke-url'} = $invokeurl;
      return $this;
    }

    /**
     * @return string
     */
    public function getKeyIdentifier()
    {
      return $this->keyIdentifier;
    }

    /**
     * @param string $keyIdentifier
     * @return SignParameters
     */
    public function setKeyIdentifier($keyIdentifier)
    {
      $this->keyIdentifier = $keyIdentifier;
      return $this;
    }

    /**
     * @return PropertyMap
     */
    public function getConfigurationOverrides()
    {
      return $this->configurationOverrides;
    }

    /**
     * @param PropertyMap $configurationOverrides
     * @return SignParameters
     */
    public function setConfigurationOverrides($configurationOverrides)
    {
      $this->configurationOverrides = $configurationOverrides;
      return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
      return $this->position;
    }

    /**
     * @param string $position
     * @return SignParameters
     */
    public function setPosition($position)
    {
      $this->position = $position;
      return $this;
    }

    /**
     * @return PropertyMap
     */
    public function getPreprocessorArguments()
    {
      return $this->preprocessorArguments;
    }

    /**
     * @param PropertyMap $preprocessorArguments
     * @return SignParameters
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
     * @return SignParameters
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
     * @return SignParameters
     */
    public function setQrCodeContent($qrCodeContent)
    {
      $this->qrCodeContent = $qrCodeContent;
      return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
      return $this->transactionId;
    }

    /**
     * @param string $transactionId
     * @return SignParameters
     */
    public function setTransactionId($transactionId)
    {
      $this->transactionId = $transactionId;
      return $this;
    }

}
