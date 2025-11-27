<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Dbp\Relay\EsignBundle\Controller\CreateQualifiedSigningRequestAction;
use Dbp\Relay\EsignBundle\State\DummySignProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignQualifiedSigningRequest',
    description: 'Qualified signing request',
    types: ['http://schema.org/EntryPoint'],
    operations: [
        new GetCollection(
            uriTemplate: '/qualified-signing-requests',
            openapi: false,
            provider: DummySignProvider::class
        ),
        new Get(
            uriTemplate: '/qualified-signing-requests/{identifier}',
            openapi: false,
            provider: DummySignProvider::class
        ),
        new Post(
            uriTemplate: '/qualified-signing-requests',
            inputFormats: [
                'multipart' => 'multipart/form-data',
            ],
            controller: CreateQualifiedSigningRequestAction::class,
            openapi: new Operation(
                tags: ['Electronic Signatures'],
                responses: [
                    413 => new Response(description: 'Payload Too Large - PDF file too large to sign!'),
                    415 => new Response(description: 'Unsupported Media Type - Only PDF files can be signed!'),
                    502 => new Response(description: 'PDF-AS error'),
                    503 => new Response(description: 'PDF-AS service unavailable'),
                ],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['file', 'profile'],
                                'properties' => [
                                    'profile' => [
                                        'description' => 'ID of the signature profile',
                                        'type' => 'string',
                                        'example' => 'official',
                                    ],
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                    'x' => [
                                        'description' => 'Position of the signature from the left (in points)',
                                        'type' => 'number',
                                        'example' => '300',
                                    ],
                                    'y' => [
                                        'description' => 'Position of the signature from the bottom (in points)',
                                        'type' => 'number',
                                        'example' => '400',
                                    ],
                                    'r' => [
                                        'description' => 'Rotation of the signature counterclockwise (in degrees)',
                                        'type' => 'number',
                                        'example' => '0',
                                    ],
                                    'w' => [
                                        'description' => 'Width of the signature (in points)',
                                        'type' => 'number',
                                        'example' => '340',
                                    ],
                                    'p' => [
                                        'description' => 'Page number the signature should be placed (starting with 1)',
                                        'type' => 'number',
                                        'example' => '2',
                                    ],
                                    'invisible' => [
                                        'description' => 'Whether the signature block should not be visible in the PDF',
                                        'type' => 'boolean',
                                        'default' => 'false',
                                        'example' => 'true',
                                    ],
                                    'user_text' => [
                                        'description' => 'User defined text. JSON list of objects with description/value',
                                        'type' => 'string',
                                        'example' => '[{"description": "Some ID", "value": "123456"}]',
                                    ],
                                ],
                            ],
                        ],
                    ])
                )
            ),
            deserialize: false
        ),
    ],
    formats: [
        0 => 'jsonld',
        // for backwards compat we also support json here
        'json' => ['application/json'],
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignQualifiedSigningRequest:output'],
    ]
)]
class QualifiedSigningRequest
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignQualifiedSigningRequest:output'])]
    private $identifier;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['http://schema.org/name'])]
    #[Groups(['EsignQualifiedSigningRequest:output'])]
    private $name;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['https://schema.org/url'])]
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
