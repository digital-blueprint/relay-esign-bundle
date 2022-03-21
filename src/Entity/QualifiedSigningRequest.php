<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Dbp\Relay\EsignBundle\Controller\CreateQualifiedSigningRequestAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Note: We need a "collectionOperations" setting for "get" to get an "entryPoint" in JSONLD.
 *
 * @ApiResource(
 *     attributes={
 *         "security" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *     },
 *     collectionOperations={
 *         "get" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *             "path" = "/esign/qualified_signing_requests",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         },
 *         "post" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *             "method" = "POST",
 *             "path" = "/esign/qualified_signing_requests",
 *             "controller" = CreateQualifiedSigningRequestAction::class,
 *             "deserialize" = false,
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *                 "requestBody" = {
 *                     "content" = {
 *                         "multipart/form-data" = {
 *                             "schema" = {
 *                                 "type" = "object",
 *                                 "properties" = {
 *                                     "profile" = {"description" = "ID of the signature profile", "type" = "string", "example" = "default"},
 *                                     "file" = {"type" = "string", "format" = "binary"},
 *                                     "x" = {"description" = "Position of the signature from the left (in points)", "type" = "number", "example" = "300"},
 *                                     "y" = {"description" = "Position of the signature from the bottom (in points)", "type" = "number", "example" = "300"},
 *                                     "r" = {"description" = "Rotation of the signature counterclockwise (in degrees)", "type" = "number", "example" = "0"},
 *                                     "w" = {"description" = "Width of the signature (in points)", "type" = "number", "example" = "340"},
 *                                     "p" = {"description" = "Page number the signature should be placed (starting with 1)", "type" = "number", "example" = "2"},
 *                                     "user_text" = {"description" = "User defined text. JSON list of objects with description/value", "type" = "string", "example" = "[{""description"": ""Some ID"", ""value"": ""123456""}]"},
 *                                 },
 *                                 "required" = {"file", "profile"},
 *                             }
 *                         }
 *                     }
 *                 },
 *                 "responses" = {
 *                     "413" = {
 *                         "description" = "Payload Too Large - PDF file too large to sign!",
 *                         "content" = {}
 *                     },
 *                     "415" = {
 *                         "description" = "Unsupported Media Type - Only PDF files can be signed!",
 *                         "content" = {}
 *                     },
 *                     "502" = {
 *                         "description" = "PDF-AS error",
 *                         "content" = {}
 *                     },
 *                     "503" = {
 *                         "description" = "PDF-AS service unavailable",
 *                         "content" = {}
 *                     }
 *                 }
 *             }
 *         },
 *     },
 *     itemOperations={
 *         "get" = {
 *             "path" = "/esign/qualified_signing_requests/{identifier}",
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         }
 *     },
 *     iri="http://schema.org/EntryPoint",
 *     shortName="EsignQualifiedSigningRequest",
 *     description="Qualified signing request",
 *     normalizationContext={
 *         "jsonld_embed_context" = true,
 *         "groups" = {"EsignQualifiedSigningRequest:output"}
 *     }
 * )
 */
class QualifiedSigningRequest
{
    /**
     * @ApiProperty(identifier=true, iri="https://schema.org/identifier")
     * @Groups({"EsignQualifiedSigningRequest:output"})
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"EsignQualifiedSigningRequest:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="http://schema.org/url")
     * @Groups({"EsignQualifiedSigningRequest:output"})
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
