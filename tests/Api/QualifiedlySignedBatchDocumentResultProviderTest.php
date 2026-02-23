<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Api;

use ApiPlatform\Metadata\Get;
use Dbp\Relay\EsignBundle\Api\QualifiedBatchSignedResultProvider;
use Dbp\Relay\EsignBundle\Api\QualifiedBatchSigningResult;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningResponse;
use PHPUnit\Framework\TestCase;

class QualifiedlySignedBatchDocumentResultProviderTest extends TestCase
{
    public function testProvideBuildsBatchItems(): void
    {
        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->once())
            ->method('fetchQualifiedlySignedDocuments')
            ->with('token-123')
            ->willReturn([
                new SigningResponse('signed-1', 0, 0, null),
                new SigningResponse('signed-2', 0, 0, null),
            ]);

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSignWithAnyQualifiedProfile');

        $provider = new QualifiedBatchSignedResultProvider($api, $auth);
        $result = $provider->provide(new Get(), ['identifier' => 'token-123']);

        $this->assertInstanceOf(QualifiedBatchSigningResult::class, $result);
        $this->assertCount(2, $result->getDocuments());
        $this->assertStringStartsWith('data:application/pdf;base64,', $result->getDocuments()[0]->getContentUrl());
    }
}
