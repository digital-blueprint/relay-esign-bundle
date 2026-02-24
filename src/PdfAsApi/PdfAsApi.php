<?php

declare(strict_types=1);
/**
 * PDF AS service.
 */

namespace Dbp\Relay\EsignBundle\PdfAsApi;

use Dbp\Relay\EsignBundle\Configuration\AdvancedProfile;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\Configuration\Profile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\BulkSignRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\GetMultipleRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASSigningImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASVerificationImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyEntry;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyMap;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignMultipleFile;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignMultipleRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignParameters;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\SignRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerificationLevel;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerifyRequest;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\VerifyResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use League\Uri\Contracts\UriException;
use League\Uri\UriTemplate;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class PdfAsApi implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const PDF_AS_TIMEOUT = 40;

    private $stopwatch;
    private $router;

    public function __construct(Stopwatch $stopwatch, UrlGeneratorInterface $router, private BundleConfig $bundleConfig)
    {
        $this->stopwatch = $stopwatch;
        $this->router = $router;
    }

    public function getCallbackUrl(string $requestId): string
    {
        return $this->router->generate('esign_callback_success', ['_dbpRelayEsignId' => $requestId], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getErrorCallbackUrl(string $requestId): string
    {
        return $this->router->generate('esign_callback_error', ['_dbpRelayEsignId' => $requestId], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function checkPdfAsConnection()
    {
        $client = new Client();

        $checkWSDL = function (string $url) use ($client) {
            $response = $client->request('GET', $url);
            $contentType = $response->getHeader('Content-Type')[0] ?? '';
            if (!str_starts_with($contentType, 'text/xml')) {
                throw new \RuntimeException('wrong content type for WSDL response');
            }
        };

        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig !== null) {
            $serverUrl = $qualifiedConfig->getServerUrl();
            $checkWSDL($serverUrl.'/services/wsverify?wsdl');
            $checkWSDL($serverUrl.'/services/wssign?wsdl');
        }

        $advancedConfig = $this->bundleConfig->getAdvanced();
        if ($advancedConfig !== null) {
            $serverUrl = $advancedConfig->getServerUrl();
            $checkWSDL($serverUrl.'/services/wsverify?wsdl');
            $checkWSDL($serverUrl.'/services/wssign?wsdl');
        }
    }

    /**
     * @param SigningRequest[] $requests
     *
     * @throws SigningException
     */
    public function createQualifiedSigningRequestsRedirectUrl(string $requestId, array $requests): string
    {
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig === null) {
            throw new SigningException('Unknown profile');
        }

        $multiRequest = new SignMultipleRequest($requestId, Connector::mobilebku, []);
        $multiRequest->setInvokeUrl($this->getCallbackUrl($requestId));
        $multiRequest->setInvokeErrorUrl(Utils::getUriWithPort($this->getErrorCallbackUrl($requestId)));

        foreach ($requests as $request) {
            $profile = $qualifiedConfig->getProfile($request->getProfileName());
            if ($profile === null) {
                throw new SigningException('Unknown profile');
            }
            $subRequest = new SignMultipleFile($request->getData(), $request->getRequestId());
            $subRequest->setPosition($request->getSignatureBlockPosition()->toPdfAsFormat());
            $subRequest->setProfile($profile->getProfileId());
            $multiRequest->addDocument($subRequest);
        }

        $event = $this->stopwatch->start('pdf-as.sign-qualified', 'esign');
        try {
            $service = new PDFASSigningImplService($qualifiedConfig->getServerUrl().'/services/wssign', self::PDF_AS_TIMEOUT);
            $response = $service->signMultiple($multiRequest, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
        } finally {
            $event->stop();
        }

        if ($response->getError() !== null) {
            throw new SigningException('Failed fetching redirectURL: '.$response->getError());
        }

        // get the redirect url
        $redirectUrl = $response->getRedirectUrl();

        // Sometimes pdf-as just returns nothing and also no error
        if ($redirectUrl === null) {
            throw new SigningException('Invalid signing response');
        }

        $this->log('QualifiedSigningRequest redirectUrl was created', ['request_id' => $requestId]);

        return $redirectUrl;
    }

    public static function buildConfigurationOverrides(Profile $profile, SigningRequest $request): PropertyMap
    {
        // Add custom user defined text if needed
        $userText = $request->getUserText();
        if ($userText !== []) {
            $overrides = UserText::buildUserTextConfigOverride($profile, $userText);
        } else {
            $overrides = [];
        }

        // Add the custom signature image
        $userImageData = $request->getUserImageData();
        if ($userImageData !== null) {
            $overrides[] = UserText::buildUserImageConfigOverride($profile, $userImageData);
        }

        $invisible = $request->isInvisible();
        if ($invisible) {
            $overrides[] = self::buildInvisibleOverride($profile, $invisible);
        }

        return new PropertyMap($overrides);
    }

    /**
     * @throws SigningException
     */
    public function createQualifiedSigningRequestRedirectUrl(SigningRequest $request): string
    {
        $profile = null;
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig !== null) {
            $profile = $qualifiedConfig->getProfile($request->getProfileName());
        }
        if ($qualifiedConfig === null || $profile === null) {
            throw new SigningException('Unknown profile');
        }

        $params = new SignParameters(Connector::mobilebku);
        $params->setProfile($profile->getProfileId());
        $params->setConfigurationOverrides(self::buildConfigurationOverrides($profile, $request));
        $params->setPosition($request->getSignatureBlockPosition()->toPdfAsFormat());

        $requestId = $request->getRequestId();
        $params->setInvokeUrl($this->getCallbackUrl($requestId));
        // it's important to add the port "443", PDF-AS has a bug that will set the port to "-1" if it isn't set
        $params->setInvokeErrorUrl(Utils::getUriWithPort($this->getErrorCallbackUrl($requestId)));

        $request = new SignRequest($request->getData(), $params, $requestId);

        $event = $this->stopwatch->start('pdf-as.sign-qualified', 'esign');
        try {
            $service = new PDFASSigningImplService($qualifiedConfig->getServerUrl().'/services/wssign', self::PDF_AS_TIMEOUT);
            // can and will throw a SoapFault "looks like we got no XML document"
            $response = $service->signSingle($request, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
        } finally {
            $event->stop();
        }

        if ($response->getError() !== null) {
            throw new SigningException('Failed fetching redirectURL: '.$response->getError());
        }

        // get the redirect url
        $redirectUrl = $response->getRedirectUrl();

        // Sometimes pdf-as just returns nothing and also no error
        if ($redirectUrl === null) {
            throw new SigningException('Invalid signing response');
        }

        $this->log('QualifiedSigningRequest redirectUrl was created', ['request_id' => $requestId]);

        // return html of extracted form
        return $redirectUrl;
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
    public function verifyPdfData($data, $requestId)
    {
        $response = $this->doVerifyRequest($data, $requestId);

        $results = $response->getVerifyResults();
        $this->log('PDF was verified', ['request_id' => $requestId]);

        return $results;
    }

    public static function buildInvisibleOverride(Profile $profile, bool $invisible): PropertyEntry
    {
        $profileId = $profile->getProfileId();

        $checkID = function ($name) {
            return preg_match('/[^.-]*/', $name) && $name !== '';
        };
        if (!$checkID($profileId)) {
            throw new \RuntimeException('invalid profile id');
        }

        return new PropertyEntry("sig_obj.$profileId.isvisible", !$invisible ? 'true' : 'false');
    }

    /**
     * @param SigningRequest[] $requests
     *
     * @return SigningResponse[]
     */
    public function advancedlySignPdfMultiple(array $requests): array
    {
        $advancedConfig = $this->bundleConfig->getAdvanced();
        if ($advancedConfig === null) {
            throw new SigningException('Unknown profile');
        }

        $signRequests = [];
        foreach ($requests as $request) {
            $profile = null;

            $profile = $advancedConfig->getProfile($request->getProfileName());
            if ($profile === null) {
                throw new SigningException('Unknown profile');
            }

            $params = new SignParameters(Connector::jks);
            $params->setKeyIdentifier($profile->getKeyId());
            $params->setProfile($profile->getProfileId());
            $params->setConfigurationOverrides(self::buildConfigurationOverrides($profile, $request));
            $params->setPosition($request->getSignatureBlockPosition()->toPdfAsFormat());

            $requestId = $request->getRequestId();
            $signRequest = new SignRequest($request->getData(), $params, $requestId);
            $signRequests[] = $signRequest;
        }

        $event = $this->stopwatch->start('pdf-as.sign-advanced', 'esign');
        $bulkRequest = new BulkSignRequest($signRequests);
        try {
            $service = new PDFASSigningImplService($advancedConfig->getServerUrl().'/services/wssign', self::PDF_AS_TIMEOUT);
            // can and will throw a SoapFault "looks like we got no XML document"
            $bulkResponse = $service->signBulk($bulkRequest, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
        } finally {
            $event->stop();
        }

        $responses = $bulkResponse->getSignResponses();
        $pdfDataResponses = [];
        foreach ($responses as $response) {
            $pdfDataResponses[] = SigningResponse::fromSoapSignResponse($response);

            $contentSize = strlen($response->getSignedPDF());
            $requestId = $response->getRequestID();
            $this->log('PDF was signed', ['request_id' => $requestId, 'signed_content_size' => $contentSize]);
        }

        return $pdfDataResponses;
    }

    /**
     * Signs $data.
     *
     * @throws SigningException
     */
    public function advancedlySignPdf(SigningRequest $request): SigningResponse
    {
        return $this->advancedlySignPdfMultiple([$request])[0];
    }

    /**
     * @throws SigningException
     * @throws SigningUnavailableException
     */
    private function handleSoapFault(\SoapFault $e): never
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
    public function doVerifyRequest(string $data, string $requestId): VerifyResponse
    {
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig === null) {
            throw new SigningException();
        }
        $wsUri = $qualifiedConfig->getServerUrl().'/services/wsverify';
        $client = new PDFASVerificationImplService($wsUri);
        $request = new VerifyRequest($data, $requestId);
        $request->setVerificationLevel(VerificationLevel::intOnly);
        $request->setSignatureIndex(-1);
        $request->setPreprocessorArguments(new PropertyMap([]));

        $event = $this->stopwatch->start('pdf-as.verify', 'esign');
        try {
            return $client->verify($request, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
        } finally {
            $event->stop();
        }
    }

    /**
     * @param mixed[] $context
     */
    private function log(string $message, array $context = [])
    {
        $context['service'] = 'PdfAs';
        if ($this->logger !== null) {
            $this->logger->notice('[{service}] '.$message, $context);
        }
    }

    /**
     * @throws UriException
     */
    protected function getQualifiedlySignedDocumentUrl(string $sessionId): string
    {
        if (str_starts_with($sessionId, 'sid_')) {
            $sessionId = substr($sessionId, strlen('sid_'));
        }
        // We used to allow plain session IDs here, so avoid breaking that

        $uriTemplate = new UriTemplate('/PDFData;jsessionid={sessionId}');
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig === null) {
            throw new SigningException();
        }

        return $qualifiedConfig->getServerUrl().$uriTemplate->expand([
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * @return SigningResponse[]
     */
    public function fetchQualifiedlySignedDocuments(string $token): array
    {
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig === null) {
            throw new SigningException();
        }

        if (str_starts_with($token, 'sid_')) {
            // XXX: in case a SignMultipleRequest is started with only one document, the redirect
            // only gives us a session ID for the single API (tokens are UUID v4, session IDs are tomcat session IDs).
            // To keep the API simple for the user, allow these also here and use the single API and wrap the result.
            $sessionId = substr($token, strlen('sid_'));

            return [$this->fetchQualifiedlySignedDocument($sessionId)];
        } elseif (str_starts_with($token, 'tok_')) {
            $token = substr($token, strlen('tok_'));
        } else {
            throw new SigningException('invalid token');
        }

        $request = new GetMultipleRequest($token);
        $event = $this->stopwatch->start('pdf-as.fetch-qualified', 'esign');
        try {
            $service = new PDFASSigningImplService($qualifiedConfig->getServerUrl().'/services/wssign', self::PDF_AS_TIMEOUT);
            $response = $service->getMultiple($request, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
        } finally {
            $event->stop();
        }

        return SigningResponse::fromSoapSignMultipleResponse($response);
    }

    /**
     * @throws SigningException
     */
    public function fetchQualifiedlySignedDocument(string $sessionId, ?callable $handler = null): SigningResponse
    {
        $stack = HandlerStack::create($handler);
        $stack->push(Utils::createStopwatchMiddleware($this->stopwatch, 'pdf-as.fetch-qualified', 'esign'));
        $client = new Client(['handler' => $stack]);

        $url = $this->getQualifiedlySignedDocumentUrl($sessionId);

        try {
            $response = $client->request('GET', $url);
        } catch (RequestException $e) {
            switch ($e->getCode()) {
                case 403:
                    throw new SigningException(sprintf("Access to QualifiedlySignedDocument with id '%s' is not allowed!", $sessionId));
            }

            throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded! Message: %s", $sessionId, $e->getMessage()));
        }

        $pdfResponse = SigningResponse::fromPdfDataResponse($response, $sessionId);
        $signedPdfData = $pdfResponse->getSignedPDF();

        $this->log('PDF was qualifiedly signed', ['session_id' => $sessionId, 'signed_content_size' => strlen($signedPdfData)]);

        return $pdfResponse;
    }

    public function createPreviewImage(string $profileName, int $resolution): string
    {
        $profile = $this->bundleConfig->getProfile($profileName);
        if ($profile === null) {
            throw new SigningException('Unknown profile');
        }
        if ($profile instanceof AdvancedProfile) {
            $serverUrl = $this->bundleConfig->getAdvanced()->getServerUrl();
        } else {
            $serverUrl = $this->bundleConfig->getQualified()->getServerUrl();
        }

        $uriTemplate = new UriTemplate('/visblock{?r,p}');
        $uri = rtrim($serverUrl, '/').$uriTemplate->expand([
            'p' => $profile->getProfileId(),
            'r' => $resolution, // 16-512 is possible
        ]);

        $client = new Client();
        $response = $client->get($uri);
        $contentType = $response->getHeaderLine('Content-Type');
        if ($contentType !== 'image/png') {
            throw new \RuntimeException('invalid content type: '.$contentType);
        }

        return (string) $response->getBody();
    }
}
