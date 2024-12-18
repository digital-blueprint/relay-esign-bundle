<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class QualifiedProfile extends Profile
{
    public function __construct(array $config)
    {
        parent::__construct($config);
    }
}
