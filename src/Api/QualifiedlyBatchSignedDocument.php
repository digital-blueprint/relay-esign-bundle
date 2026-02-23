<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [],
    routePrefix: '/esign',
)]
class QualifiedlyBatchSignedDocument
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignQualifiedBatchSigningResult:output'])]
    private string $identifier;

    #[ApiProperty(iris: ['http://schema.org/contentUrl'])]
    #[Groups(['EsignQualifiedBatchSigningResult:output'])]
    private string $contentUrl;

    #[ApiProperty(iris: ['https://schema.org/contentSize'])]
    #[Groups(['EsignQualifiedBatchSigningResult:output'])]
    private int $contentSize;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    public function setContentUrl(string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }

    public function getContentSize(): int
    {
        return $this->contentSize;
    }

    public function setContentSize(int $contentSize): void
    {
        $this->contentSize = $contentSize;
    }
}
