<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DBP\API\ESignBundle\Controller\CreateAdvancedlySignedDocumentAction;
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
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *         },
 *         "post" = {
 *             "method" = "POST",
 *             "path" = "/advancedly_signed_documents",
 *             "controller" = CreateAdvancedlySignedDocumentAction::class,
 *             "deserialize" = false,
 *             "openapi_context" = {
 *                 "requestBody" = {
 *                     "content" = {
 *                         "multipart/form-data" = {
 *                             "schema" = {
 *                                 "type" = "object",
 *                                 "properties" = {
 *                                     "profile" = {"description" = "Name of the signature profile", "type" = "string", "example" = "official"},
 *                                     "file" = {"type" = "string", "format" = "binary"},
 *                                     "x" = {"description" = "Position of the signature from the left", "type" = "number", "example" = "100"},
 *                                     "y" = {"description" = "Position of the signature from the bottom", "type" = "number", "example" = "100"},
 *                                     "r" = {"description" = "Rotation of the signature counterclockwise", "type" = "number", "example" = "90"},
 *                                     "w" = {"description" = "Width of the signature", "type" = "number", "example" = "240"},
 *                                     "p" = {"description" = "Page number the signature should be placed", "type" = "number", "example" = "2"},
 *                                 },
 *                                 "required" = {"file", "profile"},
 *                             }
 *                         }
 *                     }
 *                 },
 *                 "add_responses" = {
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
 *         "get" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY')"
 *         }
 *     },
 *     iri="http://schema.org/MediaObject",
 *     description="Advanced signed PDF document",
 *     normalizationContext={
 *         "jsonld_embed_context" = true,
 *         "groups" = {"AdvancedlySignedDocument:output"}
 *     }
 * )
 */
class AdvancedlySignedDocument
{
    /**
     * @Groups({"AdvancedlySignedDocument:output"})
     * @ApiProperty(identifier=true, iri="https://schema.org/identifier")
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"AdvancedlySignedDocument:output"})
     *
     * @var string
     */
    private $contentUrl;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"AdvancedlySignedDocument:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="https://schema.org/contentSize")
     * @Groups({"AdvancedlySignedDocument:output"})
     *
     * @var int
     */
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
