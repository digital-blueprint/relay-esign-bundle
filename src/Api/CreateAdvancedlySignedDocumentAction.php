<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

#[AsController]
final class CreateAdvancedlySignedDocumentAction
{
    private $api;

    public function __construct(PdfAsApi $api, private readonly AuthorizationService $authorizationService)
    {
        $this->api = $api;
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request): AdvancedlySignedDocument
    {
        $this->authorizationService->checkCanSign();

        $profileName = Utils::requestGet($request, 'profile');
        if ($profileName === null) {
            throw new BadRequestHttpException('Missing "profile"');
        }

        $this->authorizationService->checkCanSignWithProfile($profileName);

        /** @var ?UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');

        // check if there is an uploaded file
        if (!$uploadedFile) {
            throw new BadRequestHttpException('No file with parameter key "file" was received!');
        }

        // If the upload failed, figure out why
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new BadRequestHttpException($uploadedFile->getErrorMessage());
        }

        // check if file is a pdf
        if ($uploadedFile->getMimeType() !== 'application/pdf') {
            throw new UnsupportedMediaTypeHttpException('Only PDF files can be signed!');
        }

        // check if file is empty
        if ($uploadedFile->getSize() === 0) {
            throw new BadRequestHttpException('Empty files cannot be signed!');
        }

        $hasPositionParams = false;

        $x = SignatureBlockPosition::AUTO;
        if (Utils::requestGet($request, 'x', '') !== '') {
            $x = (float) Utils::requestGet($request, 'x');
            $hasPositionParams = true;
        }

        $y = SignatureBlockPosition::AUTO;
        if (Utils::requestGet($request, 'y', '') !== '') {
            $y = (float) Utils::requestGet($request, 'y');
            $hasPositionParams = true;
        }

        $width = SignatureBlockPosition::AUTO;
        if (Utils::requestGet($request, 'w', '') !== '') {
            $width = (float) Utils::requestGet($request, 'w');
            $hasPositionParams = true;
        }

        $rotation = 0.0;
        if (Utils::requestGet($request, 'r', '') !== '') {
            $rotation = (float) Utils::requestGet($request, 'r');
            $hasPositionParams = true;
        }

        $page = SignatureBlockPosition::AUTO;
        if (Utils::requestGet($request, 'p', '') !== '') {
            $page = (int) Utils::requestGet($request, 'p');
            $hasPositionParams = true;
        }

        $userText = [];
        if ($request->request->has('user_text')) {
            $data = $request->request->all()['user_text'];
            $userText = Utils::parseUserText($data);
        }

        $invisible = $request->request->getBoolean('invisible');
        if ($invisible && $hasPositionParams) {
            throw new BadRequestHttpException('Position parameters are not allowed in case the signature block is set to invisible');
        }

        $data = @file_get_contents($uploadedFile->getPathname());
        if ($data === false) {
            throw new \RuntimeException('Failed to read file');
        }

        // sign the pdf data
        $requestId = PdfAsApiUtils::generateRequestId();
        $blockPosition = new SignatureBlockPosition(x: $x, y: $y, width : $width, rotation: $rotation, page: $page);
        $request = new SigningRequest($data, $profileName, $requestId, $blockPosition, $userText, invisible: $invisible);
        try {
            $result = $this->api->advancedlySignPdf($request);
        } catch (SigningUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        // add some suffix for signed documents
        $signedFileName = Utils::generateSignedFileName($uploadedFile->getClientOriginalName());
        $signedPdfData = $result->getSignedPDF();

        $document = new AdvancedlySignedDocument();
        $document->setIdentifier($requestId);
        $document->setContentUrl(Utils::getDataURI($signedPdfData, 'application/pdf'));
        $document->setName($signedFileName);
        $document->setContentSize(strlen($signedPdfData));

        return $document;
    }
}
