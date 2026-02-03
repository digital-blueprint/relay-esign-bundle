<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class PropertyMap
{
    /**
     * @var PropertyEntry[]
     */
    protected $propertyEntries;

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
     */
    public function setPropertyEntries(array $propertyEntries): void
    {
        $this->propertyEntries = $propertyEntries;
    }
}
