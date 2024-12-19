<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class BundleConfig
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->ensureUniqueProfileNames();
    }

    private function ensureUniqueProfileNames(): void
    {
        $names = [];
        foreach ($this->getProfiles() as $profile) {
            if (in_array($profile->getName(), $names, true)) {
                throw new \RuntimeException('esign: profile names need to be unique');
            }
            $names[] = $profile->getName();
        }
    }

    /**
     * @return Profile[]
     */
    public function getProfiles(): array
    {
        $profiles = [];
        $qualifiedConfig = $this->getQualified();
        if ($qualifiedConfig !== null) {
            $profiles = $qualifiedConfig->getProfiles();
        }
        $advancedConfig = $this->getAdvanced();
        if ($advancedConfig !== null) {
            $profiles = array_merge($profiles, $advancedConfig->getProfiles());
        }

        return $profiles;
    }

    public function getProfile(string $name): ?Profile
    {
        $qualifiedConfig = $this->getQualified();
        if ($qualifiedConfig !== null) {
            $profile = $qualifiedConfig->getProfile($name);
            if ($profile !== null) {
                return $profile;
            }
        }
        $advancedConfig = $this->getAdvanced();
        if ($advancedConfig !== null) {
            $profile = $advancedConfig->getProfile($name);
            if ($profile !== null) {
                return $profile;
            }
        }

        return null;
    }

    public function getQualified(): ?QualifiedConfig
    {
        if (array_key_exists('qualified_signature', $this->config)) {
            return new QualifiedConfig($this->config['qualified_signature']);
        }

        return null;
    }

    public function getAdvanced(): ?AdvancedConfig
    {
        if (array_key_exists('advanced_signature', $this->config)) {
            return new AdvancedConfig($this->config['advanced_signature']);
        }

        return null;
    }

    public static function hasVerification(): bool
    {
        return ($_ENV['ESIGN_PDF_AS_VERIFICATION_ENABLE'] ?? 'false') === 'true';
    }
}
