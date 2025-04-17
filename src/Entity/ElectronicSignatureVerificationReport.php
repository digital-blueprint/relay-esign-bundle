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
use Dbp\Relay\EsignBundle\Controller\CreateElectronicSignatureVerificationReportAction;
use Dbp\Relay\EsignBundle\State\DummyVerifyProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'EsignElectronicSignatureVerificationReport',
    types: ['https://schema.tugraz.at/ElectronicSignatureVerificationReport'],
    operations: [
        new GetCollection(
            uriTemplate: '/electronic-signature-verification-reports',
            openapi: new Operation(
                tags: ['Electronic Signatures']
            ),
            provider: DummyVerifyProvider::class
        ),
        new Get(
            uriTemplate: '/electronic-signature-verification-reports/{identifier}',
            openapi: new Operation(
                tags: ['Electronic Signatures']
            ),
            provider: DummyVerifyProvider::class
        ),
        new Post(
            uriTemplate: '/electronic-signature-verification-reports',
            inputFormats: [
                'multipart' => 'multipart/form-data',
            ],
            controller: CreateElectronicSignatureVerificationReportAction::class,
            openapi: new Operation(
                tags: ['Electronic Signatures'],
                responses: [
                    415 => new Response(description: 'Unsupported Media Type - Only PDF files can be verified!'),
                    502 => new Response(description: 'PDF-AS error'),
                    503 => new Response(description: 'PDF-AS service unavailable'),
                ],
                summary: 'Retrieves a ElectronicSignatureVerificationReport resource with a collection of ElectronicSignature resources of a signed document.',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['file'],
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
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
        'groups' => ['EsignElectronicSignatureVerificationReport:output', 'ElectronicSignature:output'],
    ]
)]
class ElectronicSignatureVerificationReport
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EsignElectronicSignatureVerificationReport:output'])]
    private $identifier;

    /**
     * @var string
     */
    #[ApiProperty(iris: ['http://schema.org/name'])]
    #[Groups(['EsignElectronicSignatureVerificationReport:output'])]
    private $name;

    /**
     * @var ElectronicSignature[]
     */
    #[Groups(['EsignElectronicSignatureVerificationReport:output'])]
    private $signatures;

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

    public function getSignatures(): ?array
    {
        return $this->signatures;
    }

    public function setSignatures(array $signatures): self
    {
        $this->signatures = $signatures;

        return $this;
    }
}
