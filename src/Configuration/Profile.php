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

    public function getAllowManualPositioning(): bool
    {
        return $this->config['allow_manual_positioning'];
    }

    public function getAllowAnnotations(): bool
    {
        return $this->config['allow_annotations'];
    }

    public function getDisplayNameEn(): string
    {
        return $this->config['display_name_en'];
    }

    public function getDisplayNameDe(): string
    {
        return $this->config['display_name_de'];
    }

    public function getInvisible(): bool
    {
        if (array_key_exists('invisible', $this->config)) {
            return $this->config['invisible'];
        }

        return false;
    }

    public function getUserText(): ?UserTextConfig
    {
        if (array_key_exists('user_text', $this->config)) {
            return new UserTextConfig($this->config['user_text']);
        }

        return null;
    }
}
