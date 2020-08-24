<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Controller;

use DBP\API\CoreBundle\Exception\ApiError;
use DBP\API\ESignBundle\Entity\OfficiallySignedDocument;
use DBP\API\ESignBundle\Helpers\Tools;
use DBP\API\ESignBundle\Service\PdfAsException;
use DBP\API\ESignBundle\Service\PdfAsUnavailableException;
use DBP\API\ESignBundle\Service\SignatureProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

final class CreateOfficiallySignedDocumentAction extends AbstractController
{
    protected $api;

    public function __construct(SignatureProviderInterface $api)
    {
        $this->api = $api;
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request): OfficiallySignedDocument
    {
        $this->denyAccessUnlessGranted('ROLE_SCOPE_OFFICIAL-SIGNATURE');

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

        // sign the pdf data
        // Throwing exceptions in officiallySignPdfData causes an exception
        try {
            $signedPdfData = $this->api->officiallySignPdfData(
                file_get_contents($uploadedFile->getPathname()), $requestId, $positionData);
        } catch (PdfAsUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (PdfAsException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        // we cannot actually return a new file because our tmpfile would be gone when its content is converted to an uri string
//        $tmpFile = tmpfile();
//        fwrite($tmpFile, $signedPdfData);
//        $metaData = stream_get_meta_data($tmpFile);
//        $tmpFilename = $metaData['uri'];
//        dump($metaData);
//        $file = new File($tmpFilename);
//        dump($file);

        // we cannot return the text directly neither because it can't be output in the json
        // but we can hijack the uploaded file, it will stay alive until the php process is closed
        file_put_contents($uploadedFile->getPathname(), $signedPdfData);

        // add some suffix for signed documents
        $signedFileName = Tools::generateSignedFileName($uploadedFile->getClientOriginalName());

        $document = new OfficiallySignedDocument();
        $document->setIdentifier($requestId);
        $document->setContentUrl(Tools::getDataURI($signedPdfData, 'application/pdf'));
        $document->setName($signedFileName);
        $document->setContentSize(strlen($signedPdfData));

        return $document;
    }
}
