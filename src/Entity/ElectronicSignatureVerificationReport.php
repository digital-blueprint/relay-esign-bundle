<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class ElectronicSignatureVerificationReport
{
    #[Groups(['EsignElectronicSignatureVerificationReport:output'])]
    private $identifier;

    /**
     * @var string
     */
    #[Groups(['EsignElectronicSignatureVerificationReport:output'])]
    private $name;

    /**
     * @var ElectronicSignature[]
     */
    #[Groups(['EsignElectronicSignatureVerificationReport:output'])]
    private $signatures;

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

    public function getSignatures(): ?array
    {
        return $this->signatures;
    }

    public function setSignatures(array $signatures): self
    {
        $this->signatures = $signatures;

        return $this;
    }
}
