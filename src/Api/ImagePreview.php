<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignImagePreview',
    description: 'Image preview for signature profiles',
    operations: [
        new Get(
            uriTemplate: '/preview/{identifier}',
            controller: ImagePreviewAction::class,
            openapi: new Operation(
                tags: ['Electronic Signatures'],
                summary: 'Get the image preview for a specified signature profile',
                parameters: [
                    new Parameter(
                        name: 'identifier',
                        in: 'path',
                        description: 'ID of the signature profile for which the image preview is requested',
                        required: true,
                        schema: ['type' => 'string'],
                        example: 'advanced',
                    ),
                ],
            ),
            output: false,
            read: false,
        ),
    ],
    formats: [
        0 => 'jsonld',
        // for backwards compat we also support json here
        'json' => ['application/json'],
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignImagePreview:output'],
    ],
    security: 'is_granted("IS_AUTHENTICATED_FULLY")',
)]
class ImagePreview
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignImagePreview:output'])]
    private $identifier;

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }
}
