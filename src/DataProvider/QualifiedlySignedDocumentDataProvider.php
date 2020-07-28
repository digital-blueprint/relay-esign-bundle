<?php

namespace DBP\API\ESignBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DBP\API\ESignBundle\Entity\QualifiedlySignedDocument;
use DBP\API\ESignBundle\Service\PdfAsException;
use DBP\API\ESignBundle\Service\PdfAsApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class QualifiedlySignedDocumentDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $api;

    public function __construct(PdfAsApi $api)
    {
        $this->api = $api;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return QualifiedlySignedDocument::class === $resourceClass;
    }

    /**
     * @param string $resourceClass
     * @param array|int|string $id
     * @param string|null $operationName
     * @param array $context
     * @return QualifiedlySignedDocument
     * @throws HttpException
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?QualifiedlySignedDocument
    {
        $api = $this->api;
        $filters = $context['filters'] ?? [];
        $fileName = $filters["fileName"] ?? "";

        try {
            return $api->fetchQualifiedlySignedDocument($id, $fileName);
        } catch (PdfAsException $e) {
            throw new HttpException(Response::HTTP_FAILED_DEPENDENCY, $e->getMessage());
        }
    }
}
