<?php
/**
 * PDF AS service.
 */

namespace DBP\API\ESignBundle\Service;

use DBP\API\CoreBundle\Service\AuditLogger;
use DBP\API\ESignBundle\Entity\QualifiedlySignedDocument;
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
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\uri_template;
use SoapFault;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PdfAsApi
{
    private $signingService = null;

    private $phpErrorReporting;

    private $lastErrorMessage = '';

    private $lastErrorStatusCode = 0;

    private $logger;

    // Signature types
    const SIG_TYPE_OFFICIALLY = 1;
    const SIG_TYPE_QUALIFIEDLY = 2;

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

    private function freezePhpNoticeErrorReporting()
    {
        $this->phpErrorReporting = error_reporting();
        error_reporting($this->phpErrorReporting & ~E_NOTICE);
    }

    private function unfreezePhpErrorReporting()
    {
        error_reporting($this->phpErrorReporting);
    }

    public function hasLastError()
    {
        return $this->lastErrorMessage != '';
    }

    public function lastErrorMessage(): string
    {
        return $this->lastErrorMessage;
    }

    public function lastErrorStatusCode(): int
    {
        return $this->lastErrorStatusCode;
    }

    public function resetLastError()
    {
        $this->lastErrorMessage = '';
        $this->lastErrorStatusCode = 0;
    }

    private function returnWithErrorMessage($message, int $statusCode = 424)
    {
        $this->lastErrorMessage = $message;
        $this->lastErrorStatusCode = $statusCode;
        $this->unfreezePhpErrorReporting();

        return '';
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
        } elseif ($sigType == self::SIG_TYPE_QUALIFIEDLY) {
            $wsBaseUri = $this->qualifiedUrl;
        } else {
            throw new \RuntimeException('invalid type');
        }
        if ($this->signingService === null) {
            $this->signingService = new PDFASSigningImplService($wsBaseUri.'/services/wssign');
        }

        return $this->signingService;
    }

    /**
     * @param array $positionData
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $requestId = '', $positionData = []): string
    {
        // fetch the redirectUrl
        $response = $this->doSingleSignRequest($data, self::SIG_TYPE_QUALIFIEDLY, $requestId, $positionData);

        if ($this->hasLastError()) {
            return '';
        }

        if ($response->getError() !== null) {
            return $this->returnWithErrorMessage('Failed fetching redirectURL: '.$response->getError());
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
     * @return VerifyResult[]
     */
    public function verifyPdfData($data, $requestId = '')
    {
        $response = $this->doVerifyRequest($data, $requestId);

        if ($this->hasLastError()) {
            return [];
        }

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
     * @return string
     */
    public function officiallySignPdfData($data, $requestId = '', $positionData = [])
    {
        $response = $this->doSingleSignRequest($data, self::SIG_TYPE_OFFICIALLY, $requestId, $positionData);

        if ($this->hasLastError()) {
            return '';
        }

        if ($response->getError() !== null) {
            return $this->returnWithErrorMessage('Signing failed!');
        }

        $signedPdfData = $response->getSignedPDF();
        $contentSize = strlen($signedPdfData);

        // the happens for example if you sign already signed files
        if ($contentSize == 0) {
            return $this->returnWithErrorMessage('Signing of this file is not possible! Maybe it was already signed?');
        }

        $this->log('PDF was officially signed', ['request_id' => $requestId, 'signed_content_size' => $contentSize]);

        return $signedPdfData;
    }

    /**
     * Throwing exceptions in this method causes an exception:.
     *
     * Uncaught Exception: Malformed UTF-8 characters, possibly incorrectly encoded
     * {"exception":"[object] (Symfony\\Component\\Serializer\\Exception\\NotEncodableValueException(code: 0):
     * Malformed UTF-8 characters, possibly incorrectly encoded at /application/vendor/symfony/serializer/Encoder/JsonEncode.php:63,
     *
     * @param int   $sigType
     * @param array $positionData
     *
     * @return SignResponse|string
     */
    public function doSingleSignRequest(string $data, $sigType = self::SIG_TYPE_OFFICIALLY, string $requestId = '', $positionData = [])
    {
        if ($requestId == '') {
            $requestId = self::generateRequestId();
        }

        $this->resetLastError();

        try {
            $service = $this->getService($sigType);
        } catch (SoapFault $e) {
            return $this->returnWithErrorMessage('Signing soap call failed, wsdl URI cannot be loaded!');
        }

        // choose the connector
        $connector = $sigType == self::SIG_TYPE_OFFICIALLY ? Connector::jks() : Connector::mobilebku();

        try {
            $params = new SignParameters($connector);

            // add the callback url for the qualified signature process
            if ($sigType == self::SIG_TYPE_QUALIFIEDLY) {
                $staticUri = $this->qualifiedStaticUrl;
                $params->setInvokeurl($staticUri.'/callback.html');
                // it's important to add the port "443", PDF-AS has a bug that will set the port to "-1" if it isn't set
                $params->setInvokeerrorurl(Tools::getUriWithPort($staticUri.'/error.html'));
            }

            // add signature position data if there is any
            if (count($positionData) != 0) {
                array_walk($positionData, function (&$item, $key) { $item = "$key:$item"; });
                $params->setPosition(implode(';', $positionData));
            }

            $request = new SignRequest($data, $params, $requestId);

            $socketTimeout = ini_get('default_socket_timeout');
            // let's stay well below 60s browser timeouts, so we can catch timeouts ourselves
            ini_set('default_socket_timeout', 40);

            // can and will throw a SoapFault "looks like we got no XML document"
            $response = $service->signSingle($request);

            // restore old timeout
            ini_set('default_socket_timeout', $socketTimeout);

            return $response;
        } catch (SoapFault $e) {
            switch (strtolower($e->getMessage())) {
                // we get that on a socket timeout
                case 'error fetching http headers':
                    return $this->returnWithErrorMessage("PDF-AS didn't answer in time! Please try again later.", 503);
                // we get that if the webserver responds with an 503 error
                case 'service unavailable':
                    return $this->returnWithErrorMessage('PDF-AS service unavailable! Please try again later.', 503);
                default:
                    return $this->returnWithErrorMessage('General SOAP error: '.$e->getMessage());
            }
        }
    }

    /**
     * Throwing exceptions in this method causes an exception:.
     *
     * Uncaught Exception: Malformed UTF-8 characters, possibly incorrectly encoded
     * {"exception":"[object] (Symfony\\Component\\Serializer\\Exception\\NotEncodableValueException(code: 0):
     * Malformed UTF-8 characters, possibly incorrectly encoded at /application/vendor/symfony/serializer/Encoder/JsonEncode.php:63,
     *
     * @return VerifyResponse|string
     */
    public function doVerifyRequest(string $data, string $requestId = '')
    {
        if ($requestId == '') {
            $requestId = self::generateRequestId();
        }

        $this->resetLastError();

        try {
            $socketTimeout = ini_get('default_socket_timeout');
            // let's stay well below 60s browser timeouts, so we can catch timeouts ourselves
            ini_set('default_socket_timeout', 40);

            $wsUri = $this->qualifiedUrl.'/services/wsverify';
            $client = new PDFASVerificationImplService($wsUri);
            $request = new VerifyRequest($data, $requestId);
            $request->setVerificationLevel(VerificationLevel::intOnly());
            $request->setSignatureIndex(-1);
            $request->setPreprocessorArguments(new PropertyMap([]));
            $response = $client->verify($request);

            // restore old timeout
            ini_set('default_socket_timeout', $socketTimeout);

            return $response;
        } catch (SoapFault $e) {
            switch (strtolower($e->getMessage())) {
                // we get that on a socket timeout
                case 'error fetching http headers':
                    return $this->returnWithErrorMessage("PDF-AS didn't answer in time! Please try again later.", 503);
                // we get that if the webserver responds with an 503 error
                case 'service unavailable':
                    return $this->returnWithErrorMessage('PDF-AS service unavailable! Please try again later.', 503);
                default:
                    return $this->returnWithErrorMessage('General SOAP error: '.$e->getMessage());
            }
        }
    }

    public static function generateRequestId(): string
    {
        return uniqid();
    }

    public static function generateSignedFileName(string $fileName): string
    {
        $pathInfo = pathinfo($fileName);
        $ext = isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '';

        // squash .sig extension
        return str_replace('.sig', '', $pathInfo['filename']).'.sig'.$ext;
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
     * @throws PdfAsException
     */
    public function fetchQualifiedlySignedDocument(string $requestId, string $fileName = ''): QualifiedlySignedDocument
    {
        $client = new Client();
        $url = $this->getQualifiedlySignedDocumentUrl($requestId);

//        dump($url);

        // fetch PDF from PDF-AS
        try {
            $response = $client->request('GET', $url);
//            dump($response);
            $signedPdfData = (string) $response->getBody();

            if ($response->getHeader('Content-Type')[0] != 'application/pdf') {
                // PDF-AS doesn't use 404 status code when document wasn't found
                if (strpos($signedPdfData, '<p>No signed pdf document available.</p>') !== false) {
                    throw new PdfAsException(sprintf("QualifiedlySignedDocument with id '%s' was not found!", $requestId));
                }

                throw new PdfAsException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded!", $requestId));
            }
        } catch (RequestException $e) {
            switch ($e->getCode()) {
                case 403:
                    throw new PdfAsException(sprintf("Access to QualifiedlySignedDocument with id '%s' is not allowed!", $requestId));
            }

            throw new PdfAsException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded! Message: %s", $requestId, $e->getMessage()));
        }

//        dump($signedPdfData);

        $signedFileName = self::generateSignedFileName($fileName == '' ? 'document.pdf' : $fileName);
        $signedPdfDataSize = strlen($signedPdfData);

        $this->log('PDF was qualifiedly signed', ['request_id' => $requestId, 'signed_content_size' => $signedPdfDataSize]);

        $document = new QualifiedlySignedDocument();
        $document->setIdentifier($requestId);
        $document->setContentUrl(self::getDataURI($signedPdfData));
        $document->setName($signedFileName);
        $document->setContentSize($signedPdfDataSize);

        return $document;
    }

    /**
     * Convert binary data to a data url.
     */
    public static function getDataURI(string $data, string $mime = 'application/pdf'): string
    {
        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
