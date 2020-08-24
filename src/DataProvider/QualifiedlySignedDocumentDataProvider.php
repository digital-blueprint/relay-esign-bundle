<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DBP\API\CoreBundle\Exception\ApiError;
use DBP\API\ESignBundle\Entity\QualifiedlySignedDocument;
use DBP\API\ESignBundle\Helpers\Tools;
use DBP\API\ESignBundle\Service\SignatureProviderInterface;
use DBP\API\ESignBundle\Service\SigningException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class QualifiedlySignedDocumentDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
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
     * @param array|int|string $id
     *
     * @return QualifiedlySignedDocument
     *
     * @throws HttpException
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?QualifiedlySignedDocument
    {
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
