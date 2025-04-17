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
use Dbp\Relay\EsignBundle\Controller\CreateAdvancedlySignedDocumentAction;
use Dbp\Relay\EsignBundle\State\DummySignProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignAdvancedlySignedDocument',
    description: 'Advanced signed PDF document',
    types: ['http://schema.org/MediaObject'],
    operations: [
        new GetCollection(
            uriTemplate: '/advancedly-signed-documents',
            openapi: false,
            provider: DummySignProvider::class
        ),
        new Get(
            uriTemplate: '/advancedly-signed-documents/{identifier}',
            openapi: false,
            provider: DummySignProvider::class
        ),
        new Post(
            uriTemplate: '/advancedly-signed-documents',
            inputFormats: [
                'multipart' => 'multipart/form-data',
            ],
            controller: CreateAdvancedlySignedDocumentAction::class,
            openapi: new Operation(
                tags: ['Electronic Signatures'],
                responses: [
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
        'groups' => ['EsignAdvancedlySignedDocument:output'],
    ]
)]
class AdvancedlySignedDocument
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignAdvancedlySignedDocument:output'])]
    private $identifier;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['http://schema.org/contentUrl'])]
    #[Groups(['EsignAdvancedlySignedDocument:output'])]
    private $contentUrl;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['http://schema.org/name'])]
    #[Groups(['EsignAdvancedlySignedDocument:output'])]
    private $name;

    /**
     * @var int
     */
    #[ApiProperty(iris: ['https://schema.org/contentSize'])]
    #[Groups(['EsignAdvancedlySignedDocument:output'])]
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
