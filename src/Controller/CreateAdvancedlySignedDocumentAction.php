<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Controller;

use DBP\API\CoreBundle\Exception\ApiError;
use DBP\API\ESignBundle\Entity\AdvancedlySignedDocument;
use DBP\API\ESignBundle\Helpers\Tools;
use DBP\API\ESignBundle\Service\SignatureProviderInterface;
use DBP\API\ESignBundle\Service\SigningException;
use DBP\API\ESignBundle\Service\SigningUnavailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

final class CreateAdvancedlySignedDocumentAction extends AbstractController
{
    protected $api;
    protected $config;

    public function __construct(ContainerInterface $container, SignatureProviderInterface $api)
    {
        $this->config = $container->getParameter('dbp_api.esign.config');
        $this->api = $api;
    }

    public function checkProfilePermissions(string $profileName)
    {
        $advancedProfiles = $this->config['advanced_profiles'] ?? [];
        foreach ($advancedProfiles as $profile) {
            if ($profile['name'] === $profileName) {
                if (!isset($profile['role'])) {
                    throw new \RuntimeException('No role set');
                }
                $role = $profile['role'];
                $this->denyAccessUnlessGranted($role);

                return;
            }
        }
        throw new AccessDeniedHttpException('unknown profile');
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request): AdvancedlySignedDocument
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $profileName = $request->get('profile');
        if ($profileName === null) {
            throw new BadRequestHttpException('Missing "profile"');
        }

        $this->checkProfilePermissions($profileName);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');

//        dump($uploadedFile);

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

        // generate a request id for the signing process
        $requestId = Tools::generateRequestId();

        $positionData = [];

        if ($request->get('x', '') !== '') {
            $positionData['x'] = (int) round((float) $request->get('x'));
        }

        if ($request->get('y', '') !== '') {
            $positionData['y'] = (int) round((float) $request->get('y'));
        }

        // there only is "w", no "h" allowed in PDF-AS
        if ($request->get('w', '') !== '') {
            $positionData['w'] = (int) round((float) $request->get('w'));
        }

        if ($request->get('r', '') !== '') {
            $positionData['r'] = (int) round((float) $request->get('r'));
        }

        if ($request->get('p', '') !== '') {
            $positionData['p'] = (int) $request->get('p');
        }

        // sign the pdf data
        try {
            $signedPdfData = $this->api->advancedlySignPdfData(
                file_get_contents($uploadedFile->getPathname()), $profileName, $requestId, $positionData);
        } catch (SigningUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        // add some suffix for signed documents
        $signedFileName = Tools::generateSignedFileName($uploadedFile->getClientOriginalName());

        $document = new AdvancedlySignedDocument();
        $document->setIdentifier($requestId);
        $document->setContentUrl(Tools::getDataURI($signedPdfData, 'application/pdf'));
        $document->setName($signedFileName);
        $document->setContentSize(strlen($signedPdfData));

        return $document;
    }
}
