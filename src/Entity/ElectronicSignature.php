<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use Dbp\Relay\EsignBundle\State\DummyVerifyProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignElectronicSignature',
    description: 'An electronic signature of a signed document',
    types: ['http://schema.tugraz.at/ElectronicSignature'],
    operations: [
        new GetCollection(
            uriTemplate: '/electronic-signatures',
            openapi: new Operation(
                tags: ['Electronic Signatures']
            ),
            provider: DummyVerifyProvider::class
        ),
        new Get(
            uriTemplate: '/electronic-signatures/{identifier}',
            openapi: new Operation(
                tags: ['Electronic Signatures']
            ),
            provider: DummyVerifyProvider::class
        ),
    ],
    formats: [
        0 => 'jsonld',
        // for backwards compat we also support json here
        'json' => ['application/json'],
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignElectronicSignature:output'],
    ]
)]
class ElectronicSignature
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignElectronicSignature:output'])]
    private $identifier;

    #[ApiProperty(iris: ['http://schema.org/givenName'])]
    #[Groups(['EsignElectronicSignature:output'])]
    private $givenName;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['https://schema.org/familyName'])]
    #[Groups(['EsignElectronicSignature:output'])]
    private $familyName;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['https://schema.org/serialNumber'])]
    #[Groups(['EsignElectronicSignature:output'])]
    private $serialNumber;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['https://schema.org/Text'])]
    #[Groups(['EsignElectronicSignature:output'])]
    private $signedBy;

    #[ApiProperty(iris: ['https://schema.org/nationality'])]
    #[Groups(['EsignElectronicSignature:output'])]
    private $nationality;

    #[ApiProperty(iris: ['https://schema.org/Text'])]
    #[Groups(['EsignElectronicSignature:output'])]
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
