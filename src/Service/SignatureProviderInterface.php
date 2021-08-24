<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Service;

interface SignatureProviderInterface
{
    /**
     * @throws SigningException
     */
    public function fetchQualifiedlySignedDocument(string $requestId): string;

    /**
     * The role required for signing with the given profile.
     *
     * @throws SigningException
     */
    public function getQualifiedlySignRequiredRole(string $profileName): string;

    /**
     * Signs $data.
     *
     * @throws SigningException
     */
    public function advancedlySignPdfData(string $data, string $profileName, string $requestId, array $positionData = [], array $userText = []): string;

    /**
     * The role required for signing with the given profile.
     *
     * @throws SigningException
     */
    public function getAdvancedlySignRequiredRole(string $profileName): string;

    /**
     * @throws SigningException
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $profileName, string $requestId, array $positionData = [], array $userText = []): string;

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
    public function verifyPdfData($data, $requestId);
}
