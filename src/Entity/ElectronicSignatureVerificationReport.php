<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DBP\API\ESignBundle\Controller\CreateElectronicSignatureVerificationReportAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Note: We need a "collectionOperations" setting for "get" to get an "entryPoint" in JSONLD.
 *
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')"},
 *     collectionOperations={
 *         "get",
 *         "sign"={
 *             "method"="POST",
 *             "path"="/electronic_signature_verification_reports/create",
 *             "controller"=CreateElectronicSignatureVerificationReportAction::class,
 *             "deserialize"=false,
 *             "openapi_context"={
 *                 "summary"="Retrieves a ElectronicSignatureVerificationReport resource with a collection of ElectronicSignature resources of a signed document.",
 *                 "requestBody"={
 *                     "content"={
 *                         "multipart/form-data"={
 *                             "schema"={
 *                                 "type"="object",
 *                                 "properties"={
 *                                     "file"={
 *                                         "type"="string",
 *                                         "format"="binary"
 *                                     }
 *                                 }
 *                             }
 *                         }
 *                     }
 *                 },
 *                 "add_responses"={
 *                     "415"={
 *                         "description"="Unsupported Media Type - Only PDF files can be verified!"
 *                     },
 *                     "424"={
 *                         "description"="PDF-AS error"
 *                     },
 *                     "503"={
 *                         "description"="PDF-AS service unavailable"
 *                     }
 *                 }
 *             }
 *         },
 *     },
 *     itemOperations={"get"},
 *     iri="https://schema.tugraz.at/ElectronicSignatureVerificationReport",
 *     description="Officially signed PDF document",
 *     normalizationContext={"jsonld_embed_context"=true, "groups"={"ElectronicSignature:output"}}
 * )
 */
class ElectronicSignatureVerificationReport
{
    /**
     * @Groups({"ElectronicSignature:output"})
     * @ApiProperty(identifier=true,iri="https://schema.org/identifier")
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"ElectronicSignature:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @Groups({"ElectronicSignature:output"})
     *
     * @var ElectronicSignature[]
     */
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
