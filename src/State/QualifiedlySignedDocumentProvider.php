<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Entity\QualifiedlySignedDocument;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\Service\SignatureProviderInterface;
use Dbp\Relay\EsignBundle\Service\SigningException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @implements ProviderInterface<QualifiedlySignedDocument>
 */
class QualifiedlySignedDocumentProvider extends AbstractController implements ProviderInterface
{
    /**
     * @var SignatureProviderInterface
     */
    private $api;

    public function __construct(SignatureProviderInterface $api, private readonly AuthorizationService $authorizationService)
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
            $signedPdfData = $api->fetchQualifiedlySignedDocument($id);
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        $document = new QualifiedlySignedDocument();
        $document->setIdentifier($id);
        $document->setContentUrl(Tools::getDataURI($signedPdfData, 'application/pdf'));
        $document->setContentSize(strlen($signedPdfData));
        $document->setName(Tools::generateSignedFileName($fileName));

        return $document;
    }
}
