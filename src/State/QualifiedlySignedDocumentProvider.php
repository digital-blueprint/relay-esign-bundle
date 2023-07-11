<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Entity\QualifiedlySignedDocument;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\Service\SignatureProviderInterface;
use Dbp\Relay\EsignBundle\Service\SigningException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class QualifiedlySignedDocumentProvider extends AbstractController implements ProviderInterface
{
    /**
     * @var SignatureProviderInterface
     */
    private $api;

    public function __construct(SignatureProviderInterface $api)
    {
        $this->api = $api;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): QualifiedlySignedDocument
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
