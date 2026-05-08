<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsApi;

class SigningRequest
{
    private string $data;
    private string $profileName;
    private string $requestId;
    private SignatureBlockPosition $signatureBlockPosition;
    private array $userText;
    private array $systemText;
    private ?string $userImageData;
    private bool $invisible;

    public function __construct(
        string $data,
        string $profileName,
        string $requestId,
        ?SignatureBlockPosition $positionData = null,
        array $userText = [],
        ?string $userImageData = null,
        bool $invisible = false,
        array $systemText = [],
    ) {
        $this->data = $data;
        $this->profileName = $profileName;
        $this->requestId = $requestId;
        $this->signatureBlockPosition = $positionData ?? new SignatureBlockPosition();
        $this->userText = $userText;
        $this->systemText = $systemText;
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

    public function getSignatureBlockPosition(): SignatureBlockPosition
    {
        return $this->signatureBlockPosition;
    }

    public function getUserText(): array
    {
        return $this->userText;
    }

    public function getSystemText(): array
    {
        return $this->systemText;
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
