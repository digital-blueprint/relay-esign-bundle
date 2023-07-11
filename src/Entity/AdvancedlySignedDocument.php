<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class AdvancedlySignedDocument
{
    /**
     * @Groups({"EsignAdvancedlySignedDocument:output"})
     */
    private $identifier;

    /**
     * @Groups({"EsignAdvancedlySignedDocument:output"})
     *
     * @var string
     */
    private $contentUrl;

    /**
     * @Groups({"EsignAdvancedlySignedDocument:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @Groups({"EsignAdvancedlySignedDocument:output"})
     *
     * @var int
     */
    private $contentSize;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getContentUrl()
    {
        return $this->contentUrl;
    }

    public function setContentUrl(string $contentUrl)
    {
        $this->contentUrl = $contentUrl;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getContentSize(): int
    {
        return $this->contentSize;
    }

    public function setContentSize(int $contentSize): self
    {
        $this->contentSize = $contentSize;

        return $this;
    }
}
