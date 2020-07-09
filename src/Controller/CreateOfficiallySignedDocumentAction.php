<?php

namespace DBP\API\ESignBundle\Controller;

use DBP\API\ESignBundle\Entity\OfficiallySignedDocument;
use App\Exception\ItemNotLoadedException;
use DBP\API\ESignBundle\Service\PdfAsApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

final class CreateOfficiallySignedDocumentAction extends AbstractController
{
    protected $api;

    public function __construct(PdfAsApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param Request $request
     * @return OfficiallySignedDocument
     * @throws ItemNotLoadedException
     */
    public function __invoke(Request $request): OfficiallySignedDocument
    {
        // enable this to test exceptions
//        $this->throwRandomException();

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
        if ($uploadedFile->getMimeType() != "application/pdf") {
            throw new UnsupportedMediaTypeHttpException('Only PDF files can be signed!');
        }

        // check if file is empty
        if ($uploadedFile->getSize() == 0) {
            throw new BadRequestHttpException('Empty files cannot be signed!');
        }

        // generate a request id for the signing process
        $requestId = $this->api->generateRequestId();

        $positionData = [];

        if ($request->query->has("x")) {
            $positionData["x"] = (int) round($request->query->get("x"));
        }

        if ($request->query->has("y")) {
            $positionData["y"] = (int) round($request->query->get("y"));
        }

        // there only is "w", no "h" allowed in PDF-AS
        if ($request->query->has("w")) {
            $positionData["w"] = (int) round($request->query->get("w"));
        }

        if ($request->query->has("r")) {
            $positionData["r"] = (int) round($request->query->get("r"));
        }

        if ($request->query->has("p")) {
            $positionData["p"] = (int) $request->query->get("p");
        }

        // sign the pdf data
        // Throwing exceptions in officiallySignPdfData causes an exception
        $signedPdfData = $this->api->officiallySignPdfData(
            file_get_contents($uploadedFile->getPathname()), $requestId, $positionData);

        // we cannot throw exceptions in the service, so we will do it this way
        if ($this->api->hasLastError())
        {
            switch ($this->api->lastErrorStatusCode())
            {
                case 503:
                    throw new ServiceUnavailableHttpException(100, $this->api->lastErrorMessage());
                    break;
                default:
                    throw new ItemNotLoadedException($this->api->lastErrorMessage());
            }
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
        $signedFileName = $this->api->generateSignedFileName($uploadedFile->getClientOriginalName());

        $document = new OfficiallySignedDocument();
        $document->setIdentifier($requestId);
        $document->setContentUrlFile($uploadedFile);
        $document->setName($signedFileName);
        $document->setContentSize(strlen($signedPdfData));
//        dump($document);

        return $document;
    }

    /**
     * @throws ServiceUnavailableHttpException
     * @throws ItemNotLoadedException
     */
    private static function throwRandomException()
    {
        switch (rand(0, 3)) {
            case 0:
                throw new ServiceUnavailableHttpException(100, "Too many requests!");
                break;
            case 1:
                throw new ItemNotLoadedException("Signing failed!");
                break;
            case 2:
                throw new ItemNotLoadedException("Signing soap call failed!");
                break;
        }
    }
}
