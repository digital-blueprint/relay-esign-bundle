<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Controller;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Entity\QualifiedSigningRequest;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\Service\SignatureProviderInterface;
use Dbp\Relay\EsignBundle\Service\SigningException;
use Dbp\Relay\EsignBundle\Service\SigningUnavailableException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class CreateQualifiedSigningRequestAction extends BaseSigningController
{
    protected $api;

    public function __construct(SignatureProviderInterface $api)
    {
        $this->api = $api;
    }

    public function checkProfilePermissions(string $profileName)
    {
        try {
            $role = $this->api->getQualifiedlySignRequiredRole($profileName);
        } catch (SigningException $e) {
            throw new AccessDeniedException($e->getMessage());
        }

        $this->denyAccessUnlessGranted($role);
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request): QualifiedSigningRequest
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $profileName = self::requestGet($request, 'profile');

        if ($profileName === null) {
            throw new BadRequestHttpException('Missing "profile"');
        }

        $this->checkProfilePermissions($profileName);

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

        // check if file is too large
        if ($uploadedFile->getSize() > 33554432) {
            throw new ApiError(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'PDF file too large to sign (32MB limit)!');
        }

        $positionData = [];

        if (self::requestGet($request, 'x', '') !== '') {
            $positionData['x'] = (int) round((float) self::requestGet($request, 'x'));
        }

        if (self::requestGet($request, 'y', '') !== '') {
            $positionData['y'] = (int) round((float) self::requestGet($request, 'y'));
        }

        // there only is "w", no "h" allowed in PDF-AS
        if (self::requestGet($request, 'w', '') !== '') {
            $positionData['w'] = (int) round((float) self::requestGet($request, 'w'));
        }

        if (self::requestGet($request, 'r', '') !== '') {
            $positionData['r'] = (int) round((float) self::requestGet($request, 'r'));
        }

        if (self::requestGet($request, 'p', '') !== '') {
            $positionData['p'] = (int) self::requestGet($request, 'p');
        }

        $userText = [];
        if ($request->request->has('user_text')) {
            $data = $request->request->all()['user_text'];
            $userText = self::parseUserText($data);
        }

        // generate a request id for the signing process
        $requestId = Tools::generateRequestId();

        // create redirect url for signing request
        try {
            $url = $this->api->createQualifiedSigningRequestRedirectUrl(
                file_get_contents($uploadedFile->getPathname()), $profileName, $requestId, $positionData, $userText);
        } catch (SigningUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        $request = new QualifiedSigningRequest();
        $request->setIdentifier($requestId);
        $request->setName($uploadedFile->getClientOriginalName());
        $request->setUrl($url);

        return $request;
    }
}
