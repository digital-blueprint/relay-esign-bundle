<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @implements ProviderInterface<QualifiedlySignedDocument>
 */
class QualifiedlySignedDocumentProvider implements ProviderInterface
{
    /**
     * @var PdfAsApi
     */
    private $api;

    public function __construct(PdfAsApi $api, private readonly AuthorizationService $authorizationService)
    {
        $this->api = $api;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): QualifiedlySignedDocument
    {
        // There is currently no way to see which profile created this signed PDF, so we can't check if the user
        // is allowed to fetch it. Since only the signer knows the ID this isn't a problem though.
        // But just for extra safety we check if the current user would be able to sign anything in the first place.
        $this->authorizationService->checkCanSignWithAnyQualifiedProfile();

        $id = $uriVariables['identifier'];
        assert(is_string($id));
        $api = $this->api;
        $filters = $context['filters'] ?? [];
        $fileName = $filters['fileName'] ?? 'document.pdf';

        try {
            $result = $api->fetchQualifiedlySignedDocument($id);
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        $signedPdfData = $result->getSignedPDF();
        $document = new QualifiedlySignedDocument();
        $document->setIdentifier($id);
        $document->setContentUrl(Tools::getDataURI($signedPdfData, 'application/pdf'));
        $document->setContentSize(strlen($signedPdfData));
        $document->setName(Tools::generateSignedFileName($fileName));

        return $document;
    }
}
