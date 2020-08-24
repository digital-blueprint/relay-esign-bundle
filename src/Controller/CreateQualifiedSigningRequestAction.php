<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Controller;

use DBP\API\CoreBundle\Exception\ApiError;
use DBP\API\ESignBundle\Entity\QualifiedSigningRequest;
use DBP\API\ESignBundle\Service\PdfAsApi;
use DBP\API\ESignBundle\Service\PdfAsException;
use DBP\API\ESignBundle\Service\PdfAsUnavailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

final class CreateQualifiedSigningRequestAction extends AbstractController
{
    protected $api;

    public function __construct(PdfAsApi $api)
    {
        $this->api = $api;
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request): QualifiedSigningRequest
    {
        $this->denyAccessUnlessGranted('ROLE_SCOPE_QUALIFIED-SIGNATURE');

        /** @var UploadedFile $uploadedFile */
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

        // check if file is too large
        if ($uploadedFile->getSize() > 33554432) {
            throw new APIError(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'PDF file too large to sign (32MB limit)!');
        }

        // generate a request id for the signing process
        $requestId = $this->api->generateRequestId();

        $positionData = [];

        if ($request->query->has('x')) {
            $positionData['x'] = (int) round((float) $request->query->get('x'));
        }

        if ($request->query->has('y')) {
            $positionData['y'] = (int) round((float) $request->query->get('y'));
        }

        // there only is "w", no "h" allowed in PDF-AS
        if ($request->query->has('w')) {
            $positionData['w'] = (int) round((float) $request->query->get('w'));
        }

        if ($request->query->has('r')) {
            $positionData['r'] = (int) round((float) $request->query->get('r'));
        }

        if ($request->query->has('p')) {
            $positionData['p'] = (int) $request->query->get('p');
        }

        // create redirect url for signing request
        try {
            $url = $this->api->createQualifiedSigningRequestRedirectUrl(
                file_get_contents($uploadedFile->getPathname()), $requestId, $positionData);
        } catch (PdfAsUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (PdfAsException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        $request = new QualifiedSigningRequest();
        $request->setIdentifier($requestId);
        $request->setName($uploadedFile->getClientOriginalName());
        $request->setUrl($url);

        return $request;
    }
}
