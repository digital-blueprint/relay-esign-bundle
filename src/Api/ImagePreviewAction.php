<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ImagePreviewAction
{
    use CustomControllerTrait;

    public function __construct(private readonly AuthorizationService $authorizationService, private readonly BundleConfig $config, private readonly PdfAsApi $pdfasApi)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, string $identifier): BinaryFileResponse
    {
        if (!$identifier) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, 'Missing signature type');
        }

        if (!$this->config->getProfile($identifier)) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, "Unknown signature profile: $identifier");
        }

        $this->authorizationService->checkCanSignWithProfile($identifier);

        $image = $this->pdfasApi->createPreviewImage($identifier, 72);

        $filesystem = new Filesystem();
        $tmpFilePath = $filesystem->tempnam(sys_get_temp_dir(), 'temp_esign_preview_img_');
        $filesystem->dumpFile($tmpFilePath, $image);

        $response = new BinaryFileResponse($tmpFilePath);
        $response->deleteFileAfterSend();

        return $response;
    }
}
