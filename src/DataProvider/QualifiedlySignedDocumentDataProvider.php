<?php

namespace DBP\API\ESignBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DBP\API\ESignBundle\Entity\QualifiedlySignedDocument;
use App\Exception\ItemNotLoadedException;
use DBP\API\ESignBundle\Service\PdfAsApi;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
     * @return QualifiedlySignedDocument|null
     * @throws ItemNotLoadedException
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?QualifiedlySignedDocument
    {
//        $this->throwRandomException();

        $api = $this->api;
        $filters = $context['filters'] ?? [];
        $fileName = $filters["fileName"] ?? "";

        return $api->fetchQualifiedlySignedDocument($id, $fileName);
    }

    /**
     * @throws ServiceUnavailableHttpException
     * @throws ItemNotLoadedException
     */
    private static function throwRandomException()
    {
        switch (rand(0, 2)) {
            case 0:
                throw new ServiceUnavailableHttpException(100, "Too many requests!");
                break;
            case 1:
                throw new ItemNotLoadedException("Signing document download request failed!");
                break;
            case 2:
                throw new ItemNotLoadedException("Signing request soap call failed!");
                break;
        }
    }
}
