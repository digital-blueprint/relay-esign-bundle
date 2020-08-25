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
     * @param string $data
     * @param string $requestId
     * @param array  $positionData
     *
     * @return string
     *
     * @throws SigningException
     */
    public function advancedSignPdfData($data, $requestId = '', $positionData = []);

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
     * @return array
     *
     * @throws SigningException
     */
    public function verifyPdfData($data, $requestId = '');
}
