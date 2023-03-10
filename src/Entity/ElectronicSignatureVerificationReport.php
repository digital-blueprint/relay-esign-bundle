<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Dbp\Relay\EsignBundle\Controller\CreateElectronicSignatureVerificationReportAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Note: We need a "collectionOperations" setting for "get" to get an "entryPoint" in JSONLD.
 *
 * @ApiResource(
 *     attributes={
 *         "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')"
 *     },
 *     collectionOperations={
 *         "get" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')",
 *             "path" = "/esign/electronic-signature-verification-reports",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         },
 *         "post" = {
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')",
 *             "method" = "POST",
 *             "path" = "/esign/electronic-signature-verification-reports",
 *             "controller" = CreateElectronicSignatureVerificationReportAction::class,
 *             "deserialize" = false,
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *                 "summary" = "Retrieves a ElectronicSignatureVerificationReport resource with a collection of ElectronicSignature resources of a signed document.",
 *                 "requestBody" = {
 *                     "content" = {
 *                         "multipart/form-data" = {
 *                             "schema" = {
 *                                 "type" = "object",
 *                                 "properties" = {
 *                                     "file" = {
 *                                         "type" = "string",
 *                                         "format" = "binary"
 *                                     }
 *                                 }
 *                             }
 *                         }
 *                     }
 *                 },
 *                 "responses" = {
 *                     "415" = {
 *                         "description" = "Unsupported Media Type - Only PDF files can be verified!",
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
 *             "security" = "is_granted('IS_AUTHENTICATED_FULLY') and is_granted('ROLE_SCOPE_VERIFY-SIGNATURE')",
 *             "path" = "/esign/electronic-signature-verification-reports/{identifier}",
 *             "openapi_context" = {
 *                 "tags" = {"Electronic Signatures"},
 *             },
 *         }
 *     },
 *     iri="https://schema.tugraz.at/ElectronicSignatureVerificationReport",
 *     shortName="EsignElectronicSignatureVerificationReport",
 *     normalizationContext={
 *         "jsonld_embed_context" = true,
 *         "groups" = {"EsignElectronicSignatureVerificationReport:output", "ElectronicSignature:output"}
 *     }
 * )
 */
class ElectronicSignatureVerificationReport
{
    /**
     * @Groups({"EsignElectronicSignatureVerificationReport:output"})
     * @ApiProperty(identifier=true, iri="https://schema.org/identifier")
     * Note: Every entity needs an identifier!
     */
    private $identifier;

    /**
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"EsignElectronicSignatureVerificationReport:output"})
     *
     * @var string
     */
    private $name;

    /**
     * @Groups({"EsignElectronicSignatureVerificationReport:output"})
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
