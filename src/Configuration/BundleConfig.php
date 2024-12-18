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
