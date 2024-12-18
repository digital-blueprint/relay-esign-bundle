<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class UserTextConfig
{
    public function __construct(private array $config)
    {
    }

    public function getTable(): string
    {
        return $this->config['user_text_table'];
    }

    public function getRow(): int
    {
        return $this->config['user_text_row'];
    }

    public function getAttachParent(): ?string
    {
        return $this->config['user_text_attach_parent'] ?? null;
    }

    public function getAttachChild(): ?string
    {
        return $this->config['user_text_attach_child'] ?? null;
    }

    public function getAttachRow(): ?int
    {
        return $this->config['user_text_attach_row'] ?? null;
    }
}
