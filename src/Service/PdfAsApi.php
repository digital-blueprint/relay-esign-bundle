<?php

declare(strict_types=1);
/**
 * PDF AS service.
 */

namespace DBP\API\ESignBundle\Service;

use DBP\API\CoreBundle\Service\AuditLogger;
use DBP\API\ESignBundle\Helpers\Tools;
use DBP\API\ESignBundle\PdfAsSoapClient\Connector;
use DBP\API\ESignBundle\PdfAsSoapClient\PDFASSigningImplService;
use DBP\API\ESignBundle\PdfAsSoapClient\PDFASVerificationImplService;
use DBP\API\ESignBundle\PdfAsSoapClient\PropertyMap;
use DBP\API\ESignBundle\PdfAsSoapClient\SignParameters;
use DBP\API\ESignBundle\PdfAsSoapClient\SignRequest;
use DBP\API\ESignBundle\PdfAsSoapClient\SignResponse;
use DBP\API\ESignBundle\PdfAsSoapClient\VerificationLevel;
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyRequest;
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\uri_template;
use SoapFault;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PdfAsApi implements SignatureProviderInterface
{
    private $signingService = null;

    private $logger;

    // Signature types
    public const SIG_TYPE_OFFICIALLY = 1;
    public const SIG_TYPE_QUALIFIEDLY = 2;

    private const PDF_AS_TIMEOUT = 40;

    private $officialUrl;
    private $qualifiedUrl;
    private $qualifiedStaticUrl;

    public function __construct(ContainerInterface $container, AuditLogger $logger)
    {
        $this->logger = $logger;

        $config = $container->getParameter('dbp_api.esign.config');
        $this->officialUrl = $config['official_url'] ?? '';
        $this->qualifiedUrl = $config['qualified_url'] ?? '';
        $this->qualifiedStaticUrl = $config['qualified_static_url'] ?? '';
    }

    /**
     * @param int $sigType
     *
     * @throws SoapFault
     */
    private function getService($sigType = self::SIG_TYPE_OFFICIALLY): PDFASSigningImplService
    {
        if ($sigType === self::SIG_TYPE_OFFICIALLY) {
            $wsBaseUri = $this->officialUrl;
        } elseif ($sigType === self::SIG_TYPE_QUALIFIEDLY) {
            $wsBaseUri = $this->qualifiedUrl;
        } else {
            throw new \RuntimeException('invalid type');
        }
        if ($this->signingService === null) {
            $this->signingService = new PDFASSigningImplService($wsBaseUri.'/services/wssign', self::PDF_AS_TIMEOUT);
        }

        return $this->signingService;
    }

    /**
     * @param array $positionData
     *
     * @throws SigningException
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $requestId = '', $positionData = []): string
    {
        // fetch the redirectUrl
        $response = $this->doSingleSignRequest($data, self::SIG_TYPE_QUALIFIEDLY, $requestId, $positionData);

        if ($response->getError() !== null) {
            throw new SigningException('Failed fetching redirectURL: '.$response->getError());
        }

        // get the redirect url
        $redirectUrl = $response->getRedirectUrl();

        $this->log('QualifiedSigningRequest redirectUrl was created', ['request_id' => $requestId]);

        // return html of extracted form
        return $redirectUrl;
    }

    public function createFakeQualifiedSigningRequestHTML(string $fileName, string $requestId = ''): string
    {
        $text = "
            <form action='https://httpbin.org/post' method='post'>
                <input type='hidden' name='file_name' value='${fileName}'>
                <input type='hidden' name='request_id' value='${requestId}'>
            </form>";
//        $text .= "<script>document.querySelector('form').submit();</script>";

        return $text;
    }

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
    public function verifyPdfData($data, $requestId = '')
    {
        $response = $this->doVerifyRequest($data, $requestId);

        $results = $response->getVerifyResults();
        $this->log('PDF was verified', ['request_id' => $requestId]);

        return $results;
    }

    /**
     * Officially signs $data.
     *
     * @param string $data
     * @param string $requestId
     * @param array  $positionData
     *
     * @throws SigningException
     */
    public function officiallySignPdfData($data, $requestId = '', $positionData = []): string
    {
        $response = $this->doSingleSignRequest($data, self::SIG_TYPE_OFFICIALLY, $requestId, $positionData);

        if ($response->getError() !== null) {
            throw new SigningException('Signing failed!');
        }

        $signedPdfData = $response->getSignedPDF();
        $contentSize = strlen($signedPdfData);

        // the happens for example if you sign already signed files
        if ($contentSize === 0) {
            throw new SigningException('Signing of this file is not possible! Maybe it was already signed?');
        }

        $this->log('PDF was officially signed', ['request_id' => $requestId, 'signed_content_size' => $contentSize]);

        return $signedPdfData;
    }

    /**
     * Throwing exceptions in this method causes an exception:.
     *
     * @param int   $sigType
     * @param array $positionData
     *
     * @return SignResponse
     *
     * @throws SigningException
     */
    public function doSingleSignRequest(string $data, $sigType = self::SIG_TYPE_OFFICIALLY, string $requestId = '', $positionData = [])
    {
        if ($requestId === '') {
            $requestId = Tools::generateRequestId();
        }

        try {
            $service = $this->getService($sigType);
        } catch (SoapFault $e) {
            throw new SigningException('Signing soap call failed, wsdl URI cannot be loaded!');
        }

        // choose the connector
        $connector = $sigType === self::SIG_TYPE_OFFICIALLY ? Connector::jks() : Connector::mobilebku();

        try {
            $params = new SignParameters($connector);
            if ($sigType === self::SIG_TYPE_OFFICIALLY) {
                $params->setKeyIdentifier('tugraz-official');
                $params->setProfile('SIGNATURBLOCK_TUGRAZ_AMTSSIGNATUR');
            }

            // add the callback url for the qualified signature process
            if ($sigType === self::SIG_TYPE_QUALIFIEDLY) {
                $staticUri = $this->qualifiedStaticUrl;
                $params->setInvokeurl($staticUri.'/callback.html');
                // it's important to add the port "443", PDF-AS has a bug that will set the port to "-1" if it isn't set
                $params->setInvokeerrorurl(Tools::getUriWithPort($staticUri.'/error.html'));
            }

            // add signature position data if there is any
            if (count($positionData) !== 0) {
                array_walk($positionData, function (&$item, $key) { $item = "$key:$item"; });
                $params->setPosition(implode(';', $positionData));
            }

            $request = new SignRequest($data, $params, $requestId);

            // can and will throw a SoapFault "looks like we got no XML document"
            return $service->signSingle($request, self::PDF_AS_TIMEOUT);
        } catch (SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
        }
    }

    /**
     * @throws SigningException
     * @throws SigningUnavailableException
     */
    private function handleSoapFault(SoapFault $e)
    {
        switch (strtolower($e->getMessage())) {
            // we get that on a socket timeout
            case 'error fetching http headers':
                throw new SigningUnavailableException("PDF-AS didn't answer in time! Please try again later.");
            // we get that if the webserver responds with an 503 error
            case 'service unavailable':
                throw new SigningUnavailableException('PDF-AS service unavailable! Please try again later.');
            default:
                throw new SigningException('General SOAP error: '.$e->getMessage());
        }
    }

    /**
     * @throws SigningException
     */
    public function doVerifyRequest(string $data, string $requestId = ''): VerifyResponse
    {
        if ($requestId === '') {
            $requestId = Tools::generateRequestId();
        }

        try {
            $wsUri = $this->qualifiedUrl.'/services/wsverify';
            $client = new PDFASVerificationImplService($wsUri);
            $request = new VerifyRequest($data, $requestId);
            $request->setVerificationLevel(VerificationLevel::intOnly());
            $request->setSignatureIndex(-1);
            $request->setPreprocessorArguments(new PropertyMap([]));

            return $client->verify($request, self::PDF_AS_TIMEOUT);
        } catch (SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
        }
    }

    /**
     * @param mixed $data
     */
    private function log(string $message, $data = null)
    {
        $this->logger->log('PdfAs', $message, $data);
    }

    protected function getQualifiedlySignedDocumentUrl(string $requestId): string
    {
        return $this->qualifiedUrl.uri_template('/PDFData;jsessionid={requestId}', [
            'requestId' => $requestId,
        ]);
    }

    /**
     * @throws SigningException
     */
    public function fetchQualifiedlySignedDocument(string $requestId): string
    {
        $client = new Client();
        $url = $this->getQualifiedlySignedDocumentUrl($requestId);

//        dump($url);

        // fetch PDF from PDF-AS
        try {
            $response = $client->request('GET', $url);
//            dump($response);
            $signedPdfData = (string) $response->getBody();

            if ($response->getHeader('Content-Type')[0] !== 'application/pdf') {
                // PDF-AS doesn't use 404 status code when document wasn't found
                if (strpos($signedPdfData, '<p>No signed pdf document available.</p>') !== false) {
                    throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' was not found!", $requestId));
                }

                throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded!", $requestId));
            }
        } catch (RequestException $e) {
            switch ($e->getCode()) {
                case 403:
                    throw new SigningException(sprintf("Access to QualifiedlySignedDocument with id '%s' is not allowed!", $requestId));
            }

            throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded! Message: %s", $requestId, $e->getMessage()));
        }

        $this->log('PDF was qualifiedly signed', ['request_id' => $requestId, 'signed_content_size' => strlen($signedPdfData)]);

        return $signedPdfData;
    }

    /**
     * Convert binary data to a data url.
     */
    public static function getDataURI(string $data, string $mime = 'application/pdf'): string
    {
        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
