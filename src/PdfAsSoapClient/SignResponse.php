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
     *
     * @return SignResponse
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
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
     *
     * @return SignResponse
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
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
     *
     * @return SignResponse
     */
    public function setRequestID($requestID)
    {
        $this->requestID = $requestID;

        return $this;
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
     *
     * @return ?SignResponse
     */
    public function setSignedPDF($signedPDF)
    {
        $this->signedPDF = $signedPDF;

        return $this;
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
     *
     * @return SignResponse
     */
    public function setVerificationResponse($verificationResponse)
    {
        $this->verificationResponse = $verificationResponse;

        return $this;
    }
}
