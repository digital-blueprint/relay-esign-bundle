<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Service;

use DBP\API\ESignBundle\Entity\QualifiedlySignedDocument;
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyResult;

interface SignatureProviderInterface
{
    /**
     * @throws SigningException
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
     * @throws SigningException
     */
    public function officiallySignPdfData($data, $requestId = '', $positionData = []);

    /**
     * @param array $positionData
     *
     * @throws SigningException
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
     * @throws SigningException
     */
    public function verifyPdfData($data, $requestId = '');
}
