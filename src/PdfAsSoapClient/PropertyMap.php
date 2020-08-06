<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class PropertyMap
{
    /**
     * @var PropertyEntry[]
     */
    protected $propertyEntries = null;

    /**
     * @param PropertyEntry[] $propertyEntries
     */
    public function __construct(array $propertyEntries)
    {
        $this->propertyEntries = $propertyEntries;
    }

    /**
     * @return PropertyEntry[]
     */
    public function getPropertyEntries()
    {
        return $this->propertyEntries;
    }

    /**
     * @param PropertyEntry[] $propertyEntries
     *
     * @return PropertyMap
     */
    public function setPropertyEntries(array $propertyEntries)
    {
        $this->propertyEntries = $propertyEntries;

        return $this;
    }
}
