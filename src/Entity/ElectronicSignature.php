<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={
 *         "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')"
 *     },
 *     collectionOperations={
 *     },
 *     itemOperations={
 *         "get" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')",
 *             "path" = "/electronic_signatures/{identifier}",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         }
 *     },
 *     iri="http://schema.tugraz.at/ElectronicSignature",
 *     description="An electronic signature of a signed document",
 *     normalizationContext={
 *         "groups" = {"EsignElectronicSignature:output"},
 *         "jsonld_embed_context" = true
 *     }
 * )
 */
class ElectronicSignature
{
    /**
     * @ApiProperty(identifier=true)
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/givenName")
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $givenName;

    /**
     * @var string
     * @ApiProperty(iri="http://schema.org/familyName")
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $familyName;

    /**
     * @var string
     * @ApiProperty(iri="http://schema.org/serialNumber")
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $serialNumber;

    /**
     * @var string
     * @ApiProperty(iri="http://schema.org/Text")
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $signedBy;

    /**
     * @ApiProperty(iri="http://schema.org/nationality")
     * @Groups({"EsignElectronicSignature:output"})
     */
    private $nationality;

    /**
     * @ApiProperty(iri="http://schema.org/Text")
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
