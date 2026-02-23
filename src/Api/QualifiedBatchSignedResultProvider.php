<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @implements ProviderInterface<QualifiedBatchSigningResult>
 */
class QualifiedBatchSignedResultProvider implements ProviderInterface
{
    public function __construct(private readonly PdfAsApi $api, private readonly AuthorizationService $authorizationService)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): QualifiedBatchSigningResult
    {
        $this->authorizationService->checkCanSignWithAnyQualifiedProfile();

        $token = $uriVariables['identifier'];
        assert(is_string($token));

        try {
            $results = $this->api->fetchQualifiedlySignedDocuments($token);
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        $items = [];
        foreach ($results as $index => $result) {
            $signedPdfData = $result->getSignedPDF();
            $item = new QualifiedlyBatchSignedDocument();
            $item->setIdentifier($token.'-'.$index);
            $item->setContentUrl(Utils::getDataURI($signedPdfData, 'application/pdf'));
            $item->setContentSize(strlen($signedPdfData));
            $items[] = $item;
        }

        $batch = new QualifiedBatchSigningResult();
        $batch->setDocuments($items);

        return $batch;
    }
}
