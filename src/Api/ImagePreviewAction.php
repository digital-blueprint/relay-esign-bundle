<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ImagePreviewAction
{
    use CustomControllerTrait;

    public function __construct(private readonly AuthorizationService $authorizationService, private readonly BundleConfig $config)
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
        $path = $this->config->getAdvanced()->getProfile($identifier)->getPreviewImage();

        if (empty($path)) {
            throw ApiError::withDetails(Response::HTTP_BAD_REQUEST, 'There is no preview image defined for this profile', 'esign:no-preview-image-defined');
        }

        if (!file_exists($path)) {
            throw ApiError::withDetails(Response::HTTP_INTERNAL_SERVER_ERROR, 'The defined preview image does not exist', 'esign:preview-image-does-not-exists');
        }

        return new BinaryFileResponse($path);
    }
}
