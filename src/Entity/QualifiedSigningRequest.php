<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DBP\API\ESignBundle\Controller\CreateQualifiedSigningRequestAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Note: We need a "collectionOperations" setting for "get" to get an "entryPoint" in JSONLD.
 *
 * @ApiResource(
 *     attributes={
 *         "security" = "is_granted('ROLE_SCOPE_QUALIFIED-SIGNATURE')"
 *     },
 *     collectionOperations={
 *         "get",
 *         "post" = {
 *             "method" = "POST",
 *             "path" = "/qualified_signing_requests",
 *             "controller" = CreateQualifiedSigningRequestAction::class,
 *             "deserialize" = false,
 *             "openapi_context" = {
 *                 "requestBody" = {
 *                     "content" = {
 *                         "multipart/form-data" = {
 *                             "schema" = {
 *                                 "type" = "object",
 *                                 "properties" = {
 *                                     "file" = {"type" = "string", "format" = "binary"},
 *                                     "x" = {"description" = "Position of the signature from the left", "type" = "number", "example" = "100"},
 *                                     "y" = {"description" = "Position of the signature from the bottom", "type" = "number", "example" = "100"},
 *                                     "r" = {"description" = "Rotation of the signature counterclockwise", "type" = "number", "example" = "90"},
 *                                     "w" = {"description" = "Width of the signature", "type" = "number", "example" = "240"},
 *                                     "p" = {"description" = "Page number the signature should be placed", "type" = "number", "example" = "2"},
 *                                 },
 *                                 "required" = {"file"},
 *                             }
 *                         }
 *                     }
 *                 },
 *                 "add_responses" = {
 *                     "413" = {
 *                         "description" = "Payload Too Large - PDF file too large to sign!"
 *                     },
 *                     "415" = {
 *                         "description" = "Unsupported Media Type - Only PDF files can be signed!"
 *                     },
 *                     "502" = {
 *                         "description" = "PDF-AS error"
 *                     },
 *                     "503" = {
 *                         "description" = "PDF-AS service unavailable"
 *                     }
 *                 }
 *             }
 *         },
 *     },
 *     itemOperations={
 *         "get"
 *     },
 *     iri="http://schema.org/EntryPoint",
 *     description="Qualified signing request",
 *     normalizationContext={
 *         "jsonld_embed_context" = true,
 *         "groups" = {"QualifiedSigningRequest:output"}
 *     }
 * )
 */
class QualifiedSigningRequest
{
    /**
     * @ApiProperty(identifier=true, iri="https://schema.org/identifier")
     * @Groups({"QualifiedSigningRequest:output"})
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"QualifiedSigningRequest:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @Groups({"QualifiedSigningRequest:output"})
     *
     * @var string
     */
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
