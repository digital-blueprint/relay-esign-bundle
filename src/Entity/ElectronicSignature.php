<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class ElectronicSignature
{
    /**
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $identifier;

    /**
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $givenName;

    /**
     * @var string
     *
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $familyName;

    /**
     * @var string
     *
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $serialNumber;

    /**
     * @var string
     *
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $signedBy;

    /**
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $nationality;

    /**
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $valueMessage;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getSignedBy(): ?string
    {
        return $this->signedBy;
    }

    public function setSignedBy(?string $signedBy): self
    {
        $this->signedBy = $signedBy;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getValueMessage(): ?string
    {
        return $this->valueMessage;
    }

    public function setValueMessage(?string $valueMessage): self
    {
        $this->valueMessage = $valueMessage;

        return $this;
    }
}
