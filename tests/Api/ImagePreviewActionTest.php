<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Tests\Api;

use Dbp\Relay\EsignBundle\Api\ImagePreviewAction;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ImagePreviewActionTest extends TestCase
{
    private BundleConfig $config;

    public function setUp(): void
    {
        $config = [
            'qualified_signature' => [
                'server_url' => 'https://sig.tugraz.at/pdf-as-web',
                'profiles' => [
                    [
                        'name' => 'some-profile',
                        'profile_id' => 'ID',
                    ],
                ],
            ],
            'advanced_signature' => [
                'server_url' => 'https://sig.tugraz.at/pdf-as-web',
                'profiles' => [],
            ],
            'authorization' => [
                'roles' => [
                    'ROLE_SIGNER' => 'user.get("HAS_ROLE_SIGNER")',
                    'ROLE_VERIFIER' => 'user.get("HAS_ROLE_VERIFIER")',
                ],
                'resource_permissions' => [
                    'ROLE_PROFILE_SIGNER' => 'resource.getName() in user.get("ALLOWED_PROFILES")',
                ],
            ],
        ];
        $this->config = new BundleConfig($config);
    }

    public function testPreviewImage(): void
    {
        $pngBytes = "\x89PNG\r\n\x1a\n";

        $request = new Request();

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->once())
            ->method('createPreviewImage')
            ->with('some-profile', 72)
            ->willReturn($pngBytes);

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSignWithProfile')
            ->with('some-profile');

        $action = new ImagePreviewAction($auth, $this->config, $api);
        $response = $action($request, 'some-profile');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame($pngBytes, $response->getContent());
    }

    public function testUnknownProfile(): void
    {
        $request = new Request();

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->never())
            ->method('createPreviewImage');

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSignWithProfile')
            ->with('unknown-profile')
            ->willThrowException(new AccessDeniedException());

        $action = new ImagePreviewAction($auth, $this->config, $api);

        $this->expectException(AccessDeniedException::class);
        $action($request, 'unknown-profile');
    }

    public function testNotAuthorized(): void
    {
        $request = new Request();

        $api = $this->createMock(PdfAsApi::class);
        $api->expects($this->never())
            ->method('createPreviewImage');

        $auth = $this->createMock(AuthorizationService::class);
        $auth->expects($this->once())
            ->method('checkCanSignWithProfile')
            ->with('some-profile')
            ->willThrowException(new AccessDeniedException());

        $action = new ImagePreviewAction($auth, $this->config, $api);

        $this->expectException(AccessDeniedException::class);
        $action($request, 'some-profile');
    }
}
