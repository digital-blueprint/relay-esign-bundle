<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Authorization;

class ProfileData
{
    public function __construct(private readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
