<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Service;

interface SignatureProviderInterface
{
    /**
     * @throws SigningException
     */
    public function fetchQualifiedlySignedDocument(string $requestId): string;

    /**
     * Signs $data.
     *
     * @throws SigningException
     */
    public function advancedlySignPdfData(string $data, string $profileName, string $requestId = '', array $positionData = []): string;

    /**
     * @throws SigningException
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $requestId = '', array $positionData = []): string;

    /**
     * Verifies pdf $data.
     *
     * @param string $data
     * @param string $requestId
     *
     * @return array
     *
     * @throws SigningException
     */
    public function verifyPdfData($data, $requestId = '');
}
