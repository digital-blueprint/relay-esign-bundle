<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Service;

class SigningRequest
{
    private string $data;
    private string $profileName;
    private string $requestId;
    private array $positionData;
    private array $userText;
    private ?string $userImageData;
    private bool $invisible;

    public function __construct(
        string $data,
        string $profileName,
        string $requestId,
        array $positionData = [],
        array $userText = [],
        ?string $userImageData = null,
        bool $invisible = false
    ) {
        $this->data = $data;
        $this->profileName = $profileName;
        $this->requestId = $requestId;
        $this->positionData = $positionData;
        $this->userText = $userText;
        $this->userImageData = $userImageData;
        $this->invisible = $invisible;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getProfileName(): string
    {
        return $this->profileName;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function getPositionData(): array
    {
        return $this->positionData;
    }

    public function getUserText(): array
    {
        return $this->userText;
    }

    public function getUserImageData(): ?string
    {
        return $this->userImageData;
    }

    public function isInvisible(): bool
    {
        return $this->invisible;
    }
}
