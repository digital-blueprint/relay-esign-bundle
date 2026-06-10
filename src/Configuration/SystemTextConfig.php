<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Configuration;

class SystemTextConfig
{
    public function __construct(private array $config = [])
    {
    }

    public function getSeparator(): bool
    {
        return $this->config['separator'];
    }

    public function getTargetTable(): string
    {
        return $this->config['target_table'];
    }

    public function getNameTable(): ?string
    {
        if (array_key_exists('name_table', $this->config)) {
            return $this->config['name_table'];
        }

        return null;
    }

    public function getTargetRow(): int
    {
        return $this->config['target_row'];
    }

    public function hasAttach(): bool
    {
        return array_key_exists('attach', $this->config);
    }

    public function getAttachParentTable(): string
    {
        assert($this->hasAttach());

        return $this->config['attach']['parent_table'];
    }

    public function getAttachChildTable(): string
    {
        assert($this->hasAttach());

        return $this->config['attach']['child_table'];
    }

    public function getAttachParentRow(): int
    {
        assert($this->hasAttach());

        return $this->config['attach']['parent_row'];
    }
}
