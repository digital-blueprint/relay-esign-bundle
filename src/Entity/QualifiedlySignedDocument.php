<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Dbp\Relay\EsignBundle\State\DummySignProvider;
use Dbp\Relay\EsignBundle\State\QualifiedlySignedDocumentProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignQualifiedlySignedDocument',
    description: 'Qualifiedly signed PDF document',
    types: ['http://schema.org/MediaObject'],
    operations: [
        new GetCollection(
            uriTemplate: '/qualifiedly-signed-documents',
            openapi: false,
            provider: DummySignProvider::class
        ),
        new Get(
            uriTemplate: '/qualifiedly-signed-documents/{identifier}',
            openapi: new Operation(
                tags: ['Electronic Signatures'],
                responses: [
                    502 => new Response(description: 'PDF-AS error'),
                    503 => new Response(description: 'PDF-AS service unavailable'),
                ],
                parameters: [
                    new Parameter(
                        name: 'identifier',
                        in: 'path',
                        description: 'Id of document to fetch',
                        required: true,
                        schema: ['type' => 'string'],
                        example: '28DbA8052CE1410AF5985E'
                    ),
                    new Parameter(
                        name: 'fileName',
                        in: 'query',
                        description: 'File name of the original file',
                        required: false,
                        schema: ['type' => 'string'],
                        example: 'my-document.pdf'
                    ),
                ]
            ),
            provider: QualifiedlySignedDocumentProvider::class
        ),
    ],
    formats: [
        0 => 'jsonld',
        // for backwards compat we also support json here
        'json' => ['application/json'],
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignQualifiedlySignedDocument:output'],
    ]
)]
class QualifiedlySignedDocument
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignQualifiedlySignedDocument:output'])]
    private $identifier;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['http://schema.org/contentUrl'])]
    #[Groups(['EsignQualifiedlySignedDocument:output'])]
    private $contentUrl;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['http://schema.org/name'])]
    #[Groups(['EsignQualifiedlySignedDocument:output'])]
    private $name;

    /**
     * @var int
     */
    #[ApiProperty(iris: ['https://schema.org/contentSize'])]
    #[Groups(['EsignQualifiedlySignedDocument:output'])]
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
