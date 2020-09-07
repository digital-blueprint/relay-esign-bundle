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

 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "method"="POST",
 *             "path"="/advancedly_signed_documents",
 *             "controller"=CreateAdvancedlySignedDocumentAction::class,
 *             "deserialize"=false,
 *             "openapi_context"={
 *                 "parameters"={
 *                    {"name"="profile", "in"="query", "description"="Name of the signature profile", "type"="string", "example"="official", "required"="1"},
 *                    {"name"="x", "in"="query", "description"="Position of the signature from the left", "type"="number", "example"="100"},
 *                    {"name"="y", "in"="query", "description"="Position of the signature from the bottom", "type"="number", "example"="100"},
 *                    {"name"="r", "in"="query", "description"="Rotation of the signature counterclockwise", "type"="number", "example"="90"},
 *                    {"name"="w", "in"="query", "description"="Width of the signature", "type"="number", "example"="240"},
 *                    {"name"="p", "in"="query", "description"="Page number the signature should be placed", "type"="number", "example"="2"}
 *                 },
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
 *                         "description"="Unsupported Media Type - Only PDF files can be signed!"
 *                     },
 *                     "502"={
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
 *     iri="http://schema.org/MediaObject",
 *     description="Advanced signed PDF document",
 *     normalizationContext={"jsonld_embed_context"=true, "groups"={"AdvancedlySignedDocument:read"}}
 * )
 */
class AdvancedlySignedDocument
{
    /**
     * @Groups({"AdvancedlySignedDocument:read"})
     * @ApiProperty(identifier=true,iri="https://schema.org/identifier")
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"AdvancedlySignedDocument:read"})
     *
     * @var string
     */
    private $contentUrl;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"AdvancedlySignedDocument:read"})
     *
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="https://schema.org/contentSize")
     * @Groups({"AdvancedlySignedDocument:read"})
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
