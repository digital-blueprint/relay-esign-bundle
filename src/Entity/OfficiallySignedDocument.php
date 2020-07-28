<?php

namespace DBP\API\ESignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use DBP\API\ESignBundle\Controller\CreateOfficiallySignedDocumentAction;

/**
 * Note: We need a "collectionOperations" setting for "get" to get an "entryPoint" in JSONLD
 *
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_SCOPE_OFFICIAL-SIGNATURE')"},
 *     collectionOperations={
 *         "get",
 *         "sign"={
 *             "method"="POST",
 *             "path"="/officially_signed_documents/sign",
 *             "controller"=CreateOfficiallySignedDocumentAction::class,
 *             "deserialize"=false,
 *             "openapi_context"={
 *                 "parameters"={
 *                    {"name"="x", "in"="path", "description"="Position of the signature from the left", "type"="number", "example"="100"},
 *                    {"name"="y", "in"="path", "description"="Position of the signature from the bottom", "type"="number", "example"="100"},
 *                    {"name"="r", "in"="path", "description"="Rotation of the signature counterclockwise", "type"="number", "example"="90"},
 *                    {"name"="w", "in"="path", "description"="Width of the signature", "type"="number", "example"="240"},
 *                    {"name"="p", "in"="path", "description"="Page number the signature should be placed", "type"="number", "example"="2"}
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
 *     iri="http://schema.org/MediaObject",
 *     description="Officially signed PDF document",
 *     normalizationContext={"jsonld_embed_context"=true, "groups"={"OfficiallySignedDocument:output"}}
 * )
 */
class OfficiallySignedDocument
{
    /**
     * @Groups({"OfficiallySignedDocument:output"})
     * @ApiProperty(identifier=true,iri="https://schema.org/identifier")
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"OfficiallySignedDocument:output"})
     * @var string
     */
    private $contentUrl;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"OfficiallySignedDocument:output"})
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="https://schema.org/contentSize")
     * @Groups({"OfficiallySignedDocument:output"})
     * @var integer
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
