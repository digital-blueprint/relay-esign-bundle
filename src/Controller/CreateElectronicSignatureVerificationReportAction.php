<?php

namespace DBP\API\ESignBundle\Controller;

use DBP\API\ESignBundle\Entity\ElectronicSignature;
use DBP\API\ESignBundle\Entity\ElectronicSignatureVerificationReport;
use App\Exception\ItemNotLoadedException;
use DBP\API\ESignBundle\Service\PdfAsApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;



final class CreateElectronicSignatureVerificationReportAction extends AbstractController
{
    protected $api;

    public function __construct(PdfAsApi $api)
    {
        $this->api = $api;
    }

    /**
     * Also see: https://www.signatur.rtr.at/de/vd/Pruefung.html
     *
     * @param Request $request
     * @return ElectronicSignatureVerificationReport
     * @throws ItemNotLoadedException
     */
    public function __invoke(Request $request): ElectronicSignatureVerificationReport
    {
        // enable this to test exceptions
//        $this->throwRandomException();

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
        if ($uploadedFile->getMimeType() != "application/pdf") {
            throw new UnsupportedMediaTypeHttpException('Only PDF files can be verified!');
        }

        // check if file is empty
        if ($uploadedFile->getSize() == 0) {
            throw new BadRequestHttpException('Empty files cannot be verified!');
        }

        // generate a request id for the signing process
        $requestId = $this->api->generateRequestId();

        // verify the pdf data
        $results = $this->api->verifyPdfData(file_get_contents($uploadedFile->getPathname()), $requestId);

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

//        dump($results);
        $signatures = [];

        foreach($results as $result) {
            $signature = new ElectronicSignature();
            $signedBy = $result->getSignedBy();
            $signature->setSignedBy($signedBy);
            $signature->setValueMessage($result->getValueMessage());

            $signedByData = preg_split("/,/", $signedBy);
            foreach ($signedByData as $declaration) {
                if ($declaration == "") {
                    break;
                }

                [$variable, $value] = preg_split("/=/", $declaration);

                switch ($variable) {
                    case "serialNumber":
                        $signature->setIdentifier("sn-" . $value);
                        $signature->setSerialNumber($value);
                        break;
                    case "givenName":
                        $signature->setGivenName($value);
                        break;
                    case "SN":
                        $signature->setFamilyName($value);
                        break;
                    case "C":
                        $signature->setNationality($value);
                        break;
                }
            }

            // use a fallback if no serial number was set (e.g. for official signatures)
            if ($signature->getIdentifier() == "") {
                $signature->setIdentifier("ri-" . $requestId . "-" . $result->getSignatureIndex());
            }

            $signatures[] = $signature;
        }


        $report = new ElectronicSignatureVerificationReport();
        $report->setIdentifier($requestId);
        $report->setName($uploadedFile->getClientOriginalName());
        $report->setSignatures($signatures);

        return $report;
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
                throw new ItemNotLoadedException("Verification failed!");
                break;
            case 2:
                throw new ItemNotLoadedException("Verification soap call failed!");
                break;
        }
    }
}
