<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Entity\QualifiedlySignedDocument;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\Service\SignatureProviderInterface;
use Dbp\Relay\EsignBundle\Service\SigningException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class QualifiedlySignedDocumentDataProvider extends AbstractController implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $api;

    public function __construct(SignatureProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return QualifiedlySignedDocument::class === $resourceClass;
    }

    /**
     * @return QualifiedlySignedDocument
     *
     * @throws HttpException
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?QualifiedlySignedDocument
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_SCOPE_QUALIFIED-SIGNATURE');

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
