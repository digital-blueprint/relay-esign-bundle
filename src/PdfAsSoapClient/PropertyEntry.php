<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class PropertyEntry
{
    /**
     * @var string
     */
    protected $key = null;

    /**
     * @var string
     */
    protected $value = null;

    /**
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return PropertyEntry
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return PropertyEntry
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
