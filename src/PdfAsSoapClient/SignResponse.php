<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

class SignResponse
{
    /**
     * @var ?string
     */
    protected $error;

    /**
     * @var ?string
     */
    protected $redirectUrl;

    /**
     * @var ?string
     */
    protected $requestID;

    /**
     * @var ?string
     */
    protected $signedPDF;

    /**
     * @var ?VerificationResponse
     */
    protected $verificationResponse;

    /**
     * @param ?string $requestID
     */
    public function __construct($requestID)
    {
        $this->requestID = $requestID;
    }

    /**
     * @return ?string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param ?string $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }

    /**
     * @return ?string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param ?string $redirectUrl
     */
    public function setRedirectUrl($redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return ?string
     */
    public function getRequestID()
    {
        return $this->requestID;
    }

    /**
     * @param ?string $requestID
     */
    public function setRequestID($requestID): void
    {
        $this->requestID = $requestID;
    }

    /**
     * @return ?string
     */
    public function getSignedPDF()
    {
        return $this->signedPDF;
    }

    /**
     * @param ?string $signedPDF
     */
    public function setSignedPDF($signedPDF): void
    {
        $this->signedPDF = $signedPDF;
    }

    /**
     * @return ?VerificationResponse
     */
    public function getVerificationResponse()
    {
        return $this->verificationResponse;
    }

    /**
     * @param ?VerificationResponse $verificationResponse
     */
    public function setVerificationResponse($verificationResponse): void
    {
        $this->verificationResponse = $verificationResponse;
    }
}
