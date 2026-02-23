<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Api;

use Dbp\Relay\EsignBundle\Api\AdvancedlySignedDocument;
use Dbp\Relay\EsignBundle\Api\CreateAdvancedlySignedDocumentAction;
use Dbp\Relay\EsignBundle\Api\CreateQualifiedBatchSigningRequestAction;
use Dbp\Relay\EsignBundle\Api\CreateQualifiedSigningRequestAction;
use Dbp\Relay\EsignBundle\Api\QualifiedSigningRequest;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ApiTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (is_string($path) && file_exists($path)) {
                @unlink($path);
            }
        }
        $this->tempFiles = [];
    }

    public function testAdvancedlySignedDocumentsSingleFile(): void
    {
        $request = new Request();
        $request->query->set('profile', 'official');
        $file = $this->createUploadedFile('single.pdf');
        $request->files->set('file', $file);

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->once())
            ->method('advancedlySignPdf')
            ->willReturn(new SigningResponse('signed', 0, 0, null));

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSign');
        $auth->expects($this->once())
            ->method('checkCanSignWithProfile')
            ->with('official');

        $action = new CreateAdvancedlySignedDocumentAction($api, $auth);
        $result = $action($request);

        $this->assertInstanceOf(AdvancedlySignedDocument::class, $result);
        $this->assertStringStartsWith('data:application/pdf;base64,', $result->getContentUrl());
        $this->assertFalse(str_ends_with($result->getIdentifier(), '-0'));
    }

    public function testQualifiedSigningRequestSingleFile(): void
    {
        $request = new Request();
        $request->query->set('profile', 'official');
        $file = $this->createUploadedFile('single.pdf');
        $request->files->set('file', $file);

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->once())
            ->method('createQualifiedSigningRequestRedirectUrl')
            ->willReturn('https://example.com/redirect');
        $api->expects($this->never())
            ->method('createQualifiedSigningRequestsRedirectUrl');

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSign');
        $auth->expects($this->once())
            ->method('checkCanSignWithProfile')
            ->with('official');

        $action = new CreateQualifiedSigningRequestAction($api, $auth);
        $result = $action($request);

        $this->assertInstanceOf(QualifiedSigningRequest::class, $result);
        $this->assertSame('https://example.com/redirect', $result->getUrl());
        $this->assertFalse(str_ends_with($result->getIdentifier(), '-0'));
    }

    public function testQualifiedSigningBatchRequest(): void
    {
        $request = new Request();
        $file1 = $this->createUploadedFile('first.pdf');
        $file2 = $this->createUploadedFile('second.pdf');
        $request->files->set('files', [$file1, $file2]);
        $request->request->set('requests', [
            json_encode(['profile' => 'official', 'x' => 10]),
            json_encode(['profile' => 'official', 'x' => 20]),
        ]);

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->once())
            ->method('createQualifiedSigningRequestsRedirectUrl')
            ->willReturn('https://example.com/redirect');
        $api->expects($this->never())
            ->method('createQualifiedSigningRequestRedirectUrl');

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSign');
        $auth->expects($this->exactly(2))
            ->method('checkCanSignWithProfile')
            ->with('official');

        $action = new CreateQualifiedBatchSigningRequestAction($api, $auth);
        $result = $action($request);
        $this->assertSame('https://example.com/redirect', $result->getUrl());
    }

    public function testQualifiedSigningBatchRequestSingleObject(): void
    {
        $request = new Request();
        $file = $this->createUploadedFile('single.pdf');
        $request->files->set('files', [$file]);
        $request->request->set('requests', [json_encode(['profile' => 'official', 'x' => 10])]);

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->once())
            ->method('createQualifiedSigningRequestsRedirectUrl')
            ->willReturn('https://example.com/redirect');

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSign');
        $auth->expects($this->once())
            ->method('checkCanSignWithProfile')
            ->with('official');

        $action = new CreateQualifiedBatchSigningRequestAction($api, $auth);
        $result = $action($request);
        $this->assertSame('https://example.com/redirect', $result->getUrl());
    }

    private function createUploadedFile(string $name, string $content = "%PDF-1.4\n%PDF\n"): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'esign');
        if ($path === false) {
            throw new \RuntimeException('Failed to create temp file');
        }
        $this->tempFiles[] = $path;
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, 'application/pdf', null, true);
    }
}
