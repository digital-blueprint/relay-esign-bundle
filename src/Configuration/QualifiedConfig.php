<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class QualifiedConfig
{
    public function __construct(private array $config)
    {
    }

    public function getServerUrl(): string
    {
        return $this->config['server_url'];
    }

    public function getCallbackUrl(): ?string
    {
        return $this->config['callback_url'] ?? null;
    }

    public function getErrorCallbackUrl(): ?string
    {
        return $this->config['error_callback_url'] ?? null;
    }

    public function getProfile(string $name): ?QualifiedProfile
    {
        foreach ($this->getProfiles() as $profile) {
            if ($profile->getName() === $name) {
                return $profile;
            }
        }

        return null;
    }

    /**
     * @return QualifiedProfile[]
     */
    public function getProfiles(): array
    {
        $profiles = [];
        foreach ($this->config['profiles'] ?? [] as $profileConfig) {
            $profiles[] = new QualifiedProfile($profileConfig);
        }

        return $profiles;
    }
}
