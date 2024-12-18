<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

abstract class Profile
{
    public function __construct(private array $config)
    {
    }

    public function getName(): string
    {
        return $this->config['name'];
    }

    public function getRole(): ?string
    {
        return $this->config['role'] ?? null;
    }

    public function getProfileId(): string
    {
        return $this->config['profile_id'];
    }

    public function getUserText(): ?UserTextConfig
    {
        if (($this->config['user_text_table'] ?? '') !== '') {
            return new UserTextConfig($this->config);
        }

        return null;
    }
}
