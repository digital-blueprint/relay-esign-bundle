<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignProfiles',
    description: 'Signature profiles for signing documents',
    operations: [
        new GetCollection(
            uriTemplate: '/profiles',
            openapi: new Operation(
                tags: ['Electronic Signatures'],
                summary: 'Get the profiles for a specified signature type',
                parameters: [
                    new Parameter(
                        name: 'type',
                        in: 'query',
                        description: 'Signature type for which profiles are provided',
                        required: true,
                        schema: ['type' => 'string'],
                        example: 'advanced',
                    ),
                ],
            ),
            provider: ProfilesProvider::class
        ),
    ],
    formats: [
        0 => 'jsonld',
        // for backwards compat we also support json here
        'json' => ['application/json'],
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignProfiles:output'],
    ],
    security: 'is_granted("IS_AUTHENTICATED_FULLY")'
)]
class Profiles
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignProfiles:output'])]
    private $identifier;

    /**
     * @var bool
     */
    #[Groups(['EsignProfiles:output'])]
    private $allowAnnotations;

    /**
     * @var bool
     */
    #[Groups(['EsignProfiles:output'])]
    private $allowManualPositioning;

    /**
     * @var string
     */
    #[Groups(['EsignProfiles:output'])]
    private $displayNameEn;

    /**
     * @var string
     */
    #[Groups(['EsignProfiles:output'])]
    private $displayNameDe;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getAllowAnnotations(): ?bool
    {
        return $this->allowAnnotations;
    }

    public function setAllowAnnotations(bool $allowAnnotations)
    {
        $this->allowAnnotations = $allowAnnotations;
    }

    public function getAllowManualPositioning(): ?bool
    {
        return $this->allowManualPositioning;
    }

    public function setAllowManualPositioning(bool $allowManualPositioning): self
    {
        $this->allowManualPositioning = $allowManualPositioning;

        return $this;
    }

    public function getDisplayNameEn(): ?string
    {
        return $this->displayNameEn;
    }

    public function setDisplayNameEn(string $displayNameEn): self
    {
        $this->displayNameEn = $displayNameEn;

        return $this;
    }

    public function getDisplayNameDe(): ?string
    {
        return $this->displayNameDe;
    }

    public function setDisplayNameDe(string $displayNameDe): self
    {
        $this->displayNameDe = $displayNameDe;

        return $this;
    }
}
