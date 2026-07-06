<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\CoreBundle\Rest\CustomControllerTrait;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
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
    public function __invoke(Request $request, string $identifier): Response
    {
        if (!$identifier) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, 'Missing signature type');
        }

        $profileName = $identifier;
        $this->authorizationService->checkCanSignWithProfile($profileName);

        $profile = $this->config->getProfile($profileName);

        if ($profile === null) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, "Unknown signature profile: $profileName");
        }

        if ($profile->getInvisible()) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, "Profile $profileName is invisible");
        }

        $res = (int) $request->query->get('resolution', 72);

        if ($res < 16 || $res > 512) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, "Resolution out of range (16-512): $res");
        }

        $image = $this->pdfasApi->createPreviewImage($profileName, $res);

        return new Response($image, Response::HTTP_OK, [
            'Content-Type' => 'image/png',
        ]);
    }
}
