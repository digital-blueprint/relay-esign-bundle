<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class AdvancedConfig
{
    public function __construct(private array $config)
    {
    }

    public function getServerUrl(): string
    {
        return $this->config['server_url'];
    }

    public function getProfile(string $name): ?AdvancedProfile
    {
        foreach ($this->getProfiles() as $profile) {
            if ($profile->getName() === $name) {
                return $profile;
            }
        }

        return null;
    }

    /**
     * @return AdvancedProfile[]
     */
    public function getProfiles(): array
    {
        $profiles = [];
        foreach ($this->config['profiles'] ?? [] as $profileConfig) {
            $profiles[] = new AdvancedProfile($profileConfig);
        }

        return $profiles;
    }
}
