<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Service;

use DBP\API\ESignBundle\Entity\QualifiedlySignedDocument;
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyResult;

interface SignatureProviderInterface
{
    /**
     * @throws PdfAsException
     */
    public function fetchQualifiedlySignedDocument(string $requestId, string $fileName = ''): QualifiedlySignedDocument;

    /**
     * Officially signs $data.
     *
     * @param string $data
     * @param string $requestId
     * @param array  $positionData
     *
     * @return string
     *
     * @throws PdfAsException
     */
    public function officiallySignPdfData($data, $requestId = '', $positionData = []);

    /**
     * @param array $positionData
     *
     * @throws PdfAsException
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $requestId = '', $positionData = []): string;

    /**
     * Verifies pdf $data.
     *
     * @param string $data
     * @param string $requestId
     *
     * @return VerifyResult[]
     *
     * @throws PdfAsException
     */
    public function verifyPdfData($data, $requestId = '');
}
