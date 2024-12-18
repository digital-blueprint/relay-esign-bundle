<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class AdvancedProfile extends Profile
{
    public function __construct(private array $config)
    {
        parent::__construct($config);
    }

    public function getKeyId(): string
    {
        return $this->config['key_id'];
    }
}
