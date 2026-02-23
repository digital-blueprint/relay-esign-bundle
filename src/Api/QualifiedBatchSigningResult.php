<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignQualifiedBatchSigningResult',
    description: 'Qualifiedly batch signed PDF documents',
    operations: [
        new GetCollection(
            uriTemplate: 'y',
            openapi: false,
            provider: DummySignProvider::class
        ),
        new Get(
            uriTemplate: '/qualified-batch-signing-results/{identifier}',
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
                        description: 'Code of batch signed documents to fetch',
                        required: true,
                        schema: ['type' => 'string'],
                        example: '28DbA8052CE1410AF5985E'
                    ),
                ]
            ),
            provider: QualifiedBatchSignedResultProvider::class
        ),
    ],
    routePrefix: '/esign',
    normalizationContext: [
        'groups' => ['EsignQualifiedBatchSigningResult:output'],
    ]
)]
class QualifiedBatchSigningResult
{
    /**
     * @var QualifiedlyBatchSignedDocument[]
     */
    #[ApiProperty(iris: ['https://schema.org/itemListElement'])]
    #[Groups(['EsignQualifiedBatchSigningResult:output'])]
    private array $documents = [];

    /**
     * @param QualifiedlyBatchSignedDocument[] $documents
     */
    public function setDocuments(array $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return QualifiedlyBatchSignedDocument[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}
