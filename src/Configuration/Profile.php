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

    public function getPreviewImage(): string
    {
        return $this->config['preview_image'];
    }

    public function getUserText(): ?UserTextConfig
    {
        if (array_key_exists('user_text', $this->config)) {
            return new UserTextConfig($this->config['user_text']);
        }

        return null;
    }
}
