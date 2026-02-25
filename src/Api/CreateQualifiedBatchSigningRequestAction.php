<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SignatureBlockPosition;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningException;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningRequest;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningUnavailableException;
use Dbp\Relay\EsignBundle\PdfAsApi\Utils as PdfAsApiUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

#[AsController]
final class CreateQualifiedBatchSigningRequestAction
{
    public function __construct(private readonly PdfAsApi $api, private readonly AuthorizationService $authorizationService, private readonly BundleConfig $bundleConfig)
    {
    }

    public function __invoke(Request $request): QualifiedBatchSigningRequest
    {
        $this->authorizationService->checkCanSign();

        if (!$this->bundleConfig->hasBatch()) {
            throw new SigningException('batch is not enabled');
        }

        $uploadedFiles = $this->extractUploadedFiles($request);
        if ($uploadedFiles === []) {
            throw new BadRequestHttpException('No file with parameter key "files" was received!');
        }
        $requestsData = $this->extractRequests($request);
        if (count($requestsData) !== count($uploadedFiles)) {
            throw new BadRequestHttpException('Number of requests must match number of uploaded files');
        }

        $requestId = PdfAsApiUtils::generateRequestId();
        $requests = [];

        foreach ($uploadedFiles as $index => $uploadedFile) {
            $requestData = $requestsData[$index];
            $profileName = $requestData['profile'] ?? null;
            if (!is_string($profileName) || $profileName === '') {
                throw new BadRequestHttpException('Missing "requests[n][profile]"');
            }
            $this->authorizationService->checkCanSignWithProfile($profileName);

            $uploadedFile = Utils::validateUploadedFile($uploadedFile);

            $hasPositionParams = false;
            $positionData = $this->extractPositionDataFromRequest($requestData, $hasPositionParams);
            $userText = $this->extractUserTextFromRequest($requestData);
            $invisible = $this->extractInvisibleFromRequest($requestData);
            if ($invisible && $hasPositionParams) {
                throw new BadRequestHttpException('Position parameters are not allowed in case the signature block is set to invisible');
            }

            $data = $uploadedFile->getContent();
            $subRequestId = $requestId.'-'.$index;
            $requests[] = new SigningRequest($data, $profileName, $subRequestId, $positionData, $userText, invisible: $invisible);
        }

        try {
            $url = $this->api->createQualifiedSigningRequestsRedirectUrl($requestId, $requests);
        } catch (SigningUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        $batch = new QualifiedBatchSigningRequest();
        $batch->setIdentifier($requestId);
        $batch->setUrl($url);

        return $batch;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractRequests(Request $request): array
    {
        $decodedRequests = [];
        if ($request->request->has('requests')) {
            $requests = $request->request->all()['requests'];
            if (!is_array($requests)) {
                throw new BadRequestHttpException('Invalid "requests" payload');
            }
            foreach ($requests as $request) {
                if (!is_string($request)) {
                    throw new BadRequestHttpException('Invalid "requests" payload');
                }
                try {
                    $decoded = json_decode($request, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new ApiError(Response::HTTP_BAD_REQUEST, 'invalid JSON');
                }
                $decodedRequests[] = $decoded;
            }
        }

        return $decodedRequests;
    }

    /**
     * @return UploadedFile[]
     */
    private function extractUploadedFiles(Request $request): array
    {
        $files = $request->files->get('files');
        if (!is_array($files)) {
            throw new BadRequestHttpException('Invalid "files" payload');
        }
        foreach ($files as &$file) {
            $file = Utils::validateUploadedFile($file);
        }

        return $files;
    }

    private function extractPositionDataFromRequest(array $requestData, bool &$hasPositionParams): SignatureBlockPosition
    {
        $hasPositionParams = false;

        $x = SignatureBlockPosition::AUTO;
        if (isset($requestData['x'])) {
            $x = $requestData['x'];
            if (!is_int($x) && !is_float($x)) {
                throw new BadRequestHttpException('Invalid "x" payload');
            }
            $hasPositionParams = true;
        }
        $y = SignatureBlockPosition::AUTO;
        if (isset($requestData['y'])) {
            $y = $requestData['y'];
            if (!is_int($y) && !is_float($y)) {
                throw new BadRequestHttpException('Invalid "y" payload');
            }
            $hasPositionParams = true;
        }
        $width = SignatureBlockPosition::AUTO;
        if (isset($requestData['width'])) {
            $width = $requestData['width'];
            if (!is_int($width) && !is_float($width)) {
                throw new BadRequestHttpException('Invalid "width" payload');
            }
            $hasPositionParams = true;
        }
        $rotation = 0.0;
        if (isset($requestData['rotation'])) {
            $rotation = $requestData['rotation'];
            if (!is_int($rotation) && !is_float($rotation)) {
                throw new BadRequestHttpException('Invalid "rotation" payload');
            }
            $hasPositionParams = true;
        }
        $page = SignatureBlockPosition::AUTO;
        if (isset($requestData['page'])) {
            $page = $requestData['page'];
            if (!is_int($page)) {
                throw new BadRequestHttpException('Invalid "page" payload');
            }
            $hasPositionParams = true;
        }

        return new SignatureBlockPosition(x: $x, y: $y, width: $width, page: $page, rotation: $rotation);
    }

    private function extractUserTextFromRequest(array $requestData): array
    {
        if (!array_key_exists('user_text', $requestData)) {
            return [];
        }

        $data = $requestData['user_text'];
        if (!is_string($data)) {
            throw new BadRequestHttpException('Invalid "requests[n][user_text]"');
        }

        return Utils::parseUserText($data);
    }

    private function extractInvisibleFromRequest(array $requestData): bool
    {
        $invisible = $requestData['invisible'] ?? false;
        if (!is_bool($invisible)) {
            throw new BadRequestHttpException('Invalid "invisible" payload');
        }

        return $invisible;
    }
}
