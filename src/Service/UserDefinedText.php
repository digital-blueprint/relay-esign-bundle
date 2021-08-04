<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Service;

class UserDefinedText
{
    private $description;
    private $value;

    public function __construct(string $description, string $value)
    {
        $this->description = $description;
        $this->value = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
