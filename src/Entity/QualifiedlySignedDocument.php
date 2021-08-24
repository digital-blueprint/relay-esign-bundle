<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
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
 *             "path" = "/esign/qualifiedly_signed_documents",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         }
 *     },
 *     itemOperations={
 *         "get" = {
 *             "path" = "/esign/qualifiedly_signed_documents/{identifier}",
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *                 "parameters" = {
 *                     {"name" = "identifier", "in" = "path", "description" = "Id of document to fetch", "required" = true, "type" = "string", "example" = "28DbA8052CE1410AF5985E"},
 *                     {"name" = "fileName", "in" = "query", "description" = "File name of the original file", "required" = false, "type" = "string", "example" = "my-document.pdf"}
 *                 },
 *                 "responses" = {
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
 *         }
 *     },
 *     iri="http://schema.org/MediaObject",
 *     shortName="EsignQualifiedlySignedDocument",
 *     description="Qualifiedly signed PDF document",
 *     normalizationContext={
 *         "jsonld_embed_context" = true,
 *         "groups" = {"EsignQualifiedlySignedDocument:output"}
 *     }
 * )
 */
class QualifiedlySignedDocument
{
    /**
     * @Groups({"EsignQualifiedlySignedDocument:output"})
     * @ApiProperty(identifier=true, iri="https://schema.org/identifier")
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"EsignQualifiedlySignedDocument:output"})
     *
     * @var string
     */
    private $contentUrl;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"EsignQualifiedlySignedDocument:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="https://schema.org/contentSize")
     * @Groups({"EsignQualifiedlySignedDocument:output"})
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
