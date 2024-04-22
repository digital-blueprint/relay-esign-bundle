<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class QualifiedSigningRequest
{
    #[Groups(['EsignQualifiedSigningRequest:output'])]
    private $identifier;

    /**
     * @var string
     */
    #[Groups(['EsignQualifiedSigningRequest:output'])]
    private $name;

    /**
     * @var string
     */
    #[Groups(['EsignQualifiedSigningRequest:output'])]
    private $url;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
