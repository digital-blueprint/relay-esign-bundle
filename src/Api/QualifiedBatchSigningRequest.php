<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignQualifiedBatchSigningRequest',
    description: 'Qualified signing batch request',
    types: ['http://schema.org/EntryPoint'],
    operations: [
        new Post(
            uriTemplate: '/qualified-batch-signing-requests',
            inputFormats: [
                'multipart' => 'multipart/form-data',
            ],
            controller: CreateQualifiedBatchSigningRequestAction::class,
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
                            'encoding' => [
                                'requests[]' => [
                                    'explode' => true,
                                    'contentType' => 'application/json',
                                ],
                            ],
                            'schema' => [
                                'type' => 'object',
                                'required' => ['files[]', 'requests[]'],
                                'properties' => [
                                    'files[]' => [
                                        'description' => 'PDF files to sign (order matches requests array).',
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                        ],
                                    ],
                                    'requests[]' => [
                                        'description' => 'Signing parameters per file (order matches files array).',
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['profile'],
                                            'properties' => [
                                                'profile' => [
                                                    'description' => 'ID of the signature profile',
                                                    'type' => 'string',
                                                    'example' => 'default',
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
                                                'rotation' => [
                                                    'description' => 'Rotation of the signature counterclockwise (in degrees)',
                                                    'type' => 'number',
                                                    'example' => '0',
                                                ],
                                                'width' => [
                                                    'description' => 'Width of the signature (in points)',
                                                    'type' => 'number',
                                                    'example' => '340',
                                                ],
                                                'page' => [
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
                                ],
                            ],
                        ],
                    ])
                )
            ),
            deserialize: false
        ),
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignQualifiedBatchSigningRequest:output'],
    ]
)]
class QualifiedBatchSigningRequest
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignQualifiedBatchSigningRequest:output'])]
    private string $identifier;

    #[ApiProperty(iris: ['https://schema.org/url'])]
    #[Groups(['EsignQualifiedBatchSigningRequest:output'])]
    private string $url;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
