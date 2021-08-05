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
 *         "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_QUALIFIED-SIGNATURE')"
 *     },
 *     collectionOperations={
 *         "get" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_QUALIFIED-SIGNATURE')",
 *             "path" = "/esign/qualified_signing_requests",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         },
 *         "post" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_QUALIFIED-SIGNATURE')",
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
 *                                     "file" = {"type" = "string", "format" = "binary"},
 *                                     "x" = {"description" = "Position of the signature from the left", "type" = "number", "example" = "300"},
 *                                     "y" = {"description" = "Position of the signature from the bottom", "type" = "number", "example" = "300"},
 *                                     "r" = {"description" = "Rotation of the signature counterclockwise", "type" = "number", "example" = "0"},
 *                                     "w" = {"description" = "Width of the signature", "type" = "number", "example" = "340"},
 *                                     "p" = {"description" = "Page number the signature should be placed", "type" = "number", "example" = "2"},
 *                                     "user_text" = {"description" = "User defined text. JSON list of objects with description/value", "type" = "string", "example" = "[{""description"": ""Some ID"", ""value"": ""123456""}]"},
 *                                 },
 *                                 "required" = {"file"},
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
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_QUALIFIED-SIGNATURE')",
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
