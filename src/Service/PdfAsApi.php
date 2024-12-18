<?php

declare(strict_types=1);
/**
 * PDF AS service.
 */

namespace Dbp\Relay\EsignBundle\Service;

use Dbp\Relay\EsignBundle\Configuration\AdvancedProfile;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\Configuration\Profile;
use Dbp\Relay\EsignBundle\Configuration\QualifiedProfile;
use Dbp\Relay\EsignBundle\Helpers\Tools;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\Connector;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASSigningImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PDFASVerificationImplService;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyEntry;
use Dbp\Relay\EsignBundle\PdfAsSoapClient\PropertyMap;
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

class PdfAsApi implements SignatureProviderInterface, LoggerAwareInterface
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

    public function getCallbackUrl(): string
    {
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig !== null && $qualifiedConfig->getCallbackUrl() !== null) {
            return $qualifiedConfig->getCallbackUrl();
        }

        return $this->router->generate('esign_callback_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getErrorCallbackUrl(): string
    {
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig !== null && $qualifiedConfig->getErrorCallbackUrl() !== null) {
            return $qualifiedConfig->getErrorCallbackUrl();
        }

        return $this->router->generate('esign_callback_error', [], UrlGeneratorInterface::ABSOLUTE_URL);
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

    public function checkCallbackUrls()
    {
        $client = new Client();
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig !== null) {
            // Only check if it's external, since we might not be deployed/public ourselves
            if ($qualifiedConfig->getCallbackUrl() !== null) {
                $client->request('GET', $qualifiedConfig->getCallbackUrl());
            }
            if ($qualifiedConfig->getErrorCallbackUrl() !== null) {
                $client->request('GET', $qualifiedConfig->getErrorCallbackUrl());
            }
        }
    }

    /**
     * @throws SigningException
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $profileName, string $requestId, array $positionData = [], array $userText = []): string
    {
        $profile = $this->getQualifiedProfileData($profileName);
        $qualifiedConfig = $this->bundleConfig->getQualified();
        assert($qualifiedConfig !== null);

        try {
            $service = new PDFASSigningImplService($qualifiedConfig->getServerUrl().'/services/wssign', self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            throw new SigningException('Signing soap call failed, wsdl URI cannot be loaded!');
        }

        $params = new SignParameters(Connector::mobilebku());
        $params->setProfile($profile->getProfileId());

        // Add custom user defined text if needed
        $overrides = $this->buildUserTextConfigOverride($profile, $userText);
        if (count($overrides) > 0) {
            $configurationOverrides = new PropertyMap($overrides);
            $params->setConfigurationOverrides($configurationOverrides);
        }

        $params->setInvokeUrl($this->getCallbackUrl());
        // it's important to add the port "443", PDF-AS has a bug that will set the port to "-1" if it isn't set
        $params->setInvokeErrorUrl(Tools::getUriWithPort($this->getErrorCallbackUrl()));

        // add signature position data if there is any
        if (count($positionData) !== 0) {
            array_walk($positionData, function (&$item, $key) { $item = "$key:$item"; });
            $params->setPosition(implode(';', $positionData));
        }

        $request = new SignRequest($data, $params, $requestId);

        $event = $this->stopwatch->start('pdf-as.sign-qualified', 'esign');
        try {
            // can and will throw a SoapFault "looks like we got no XML document"
            $response = $service->signSingle($request, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
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

    private function getQualifiedProfileData(string $profileName): QualifiedProfile
    {
        $profile = null;
        $qualifiedConfig = $this->bundleConfig->getQualified();
        if ($qualifiedConfig !== null) {
            $profile = $qualifiedConfig->getProfile($profileName);
        }
        if ($profile === null) {
            throw new SigningException('Unknown profile');
        }

        return $profile;
    }

    private function getAdvancedProfileData(string $profileName): AdvancedProfile
    {
        $profile = null;
        $advancedConfig = $this->bundleConfig->getAdvanced();
        if ($advancedConfig !== null) {
            $profile = $advancedConfig->getProfile($profileName);
        }
        if ($profile === null) {
            throw new SigningException('Unknown profile');
        }

        return $profile;
    }

    /**
     * @param UserDefinedText[] $userText
     *
     * @return PropertyEntry[]
     */
    public function buildUserTextConfigOverride(Profile $profile, array $userText): array
    {
        $userTextConfig = $profile->getUserText();
        if ($userTextConfig === null) {
            throw new SigningException('user_text not available/implemented for this profile');
        }

        $profileId = $profile->getProfileId();

        // User text specific placement config
        $userTable = $userTextConfig->getTable();
        $userRow = $userTextConfig->getRow();
        $attachParent = $userTextConfig->getAttachParent();
        $attachChild = $userTextConfig->getAttachChild();
        $attachRow = $userTextConfig->getAttachRow() ?? 1;

        $checkID = function ($name) {
            return preg_match('/[^.-]*/', $name) && $name !== '';
        };

        // validate the config, so we don't write out invalid config lines
        if (!$checkID($userTable)) {
            throw new \RuntimeException('invalid table id');
        }
        if ($attachChild !== null && !$checkID($attachChild)) {
            throw new \RuntimeException('invalid child id');
        }
        if ($attachParent !== null && !$checkID($attachParent)) {
            throw new \RuntimeException('invalid parent id');
        }
        if ($userRow <= 0 || $attachRow <= 0) {
            throw new \RuntimeException('invalid table row');
        }
        if (!$checkID($profileId)) {
            throw new \RuntimeException('invalid profile id');
        }

        // First we insert the user content into the table
        $overrides = [];
        foreach ($userText as $entry) {
            $desc = $entry->getDescription();
            $value = $entry->getValue();

            $entryId = 'SIG_USER_TEXT_'.$userTable.'_'.$userRow;
            $overrides[] = new PropertyEntry("sig_obj.$profileId.key.$entryId", $desc);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.value.$entryId", $value);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.table.$userTable.$userRow", $entryId.'-cv');
            ++$userRow;
        }

        // In case we added something we optionally attach a "child" table to a "parent" one at a specific "row"
        // This can be the table we filled above, or some parent table.
        // This is needed because pdf-as doesn't allow empty tables and we need to attach it only when it has at least
        // one row. But it also allows us to show extra images for example if there are >0 extra rows
        if ($attachParent !== null && $attachChild !== null && count($overrides) > 0) {
            $overrides[] = new PropertyEntry(
                "sig_obj.$profileId.table.$attachParent.$attachRow", 'TABLE-'.$attachChild);
        }

        return $overrides;
    }

    /**
     * Signs $data.
     *
     * @throws SigningException
     */
    public function advancedlySignPdfData(string $data, string $profileName, string $requestId = '', array $positionData = [], array $userText = []): string
    {
        $profile = $this->getAdvancedProfileData($profileName);
        $advancedConfig = $this->bundleConfig->getAdvanced();
        assert($advancedConfig !== null);

        if ($requestId === '') {
            $requestId = Tools::generateRequestId();
        }

        try {
            $service = new PDFASSigningImplService($advancedConfig->getServerUrl().'/services/wssign', self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            throw new SigningException('Signing soap call failed, wsdl URI cannot be loaded!');
        }

        $params = new SignParameters(Connector::jks());
        $params->setKeyIdentifier($profile->getKeyId());
        $params->setProfile($profile->getProfileId());

        // Add custom user defined text if needed
        $overrides = $this->buildUserTextConfigOverride($profile, $userText);
        if (count($overrides) > 0) {
            $configurationOverrides = new PropertyMap($overrides);
            $params->setConfigurationOverrides($configurationOverrides);
        }

        // add signature position data if there is any
        if (count($positionData) !== 0) {
            array_walk($positionData, function (&$item, $key) { $item = "$key:$item"; });
            $params->setPosition(implode(';', $positionData));
        }

        $event = $this->stopwatch->start('pdf-as.sign-advanced', 'esign');
        $request = new SignRequest($data, $params, $requestId);
        try {
            // can and will throw a SoapFault "looks like we got no XML document"
            $response = $service->signSingle($request, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
        } finally {
            $event->stop();
        }

        if ($response->getError() !== null) {
            throw new SigningException('Signing failed!');
        }

        $signedPdfData = $response->getSignedPDF();
        if ($signedPdfData === null) {
            // This likely means pdf-as failed uncontrolled (check the logs)
            throw new SigningException('Signing failed!');
        }
        $contentSize = strlen($signedPdfData);

        $this->log('PDF was signed', ['request_id' => $requestId, 'signed_content_size' => $contentSize]);

        return $signedPdfData;
    }

    /**
     * @throws SigningException
     * @throws SigningUnavailableException
     */
    private function handleSoapFault(\SoapFault $e)
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
        $request->setVerificationLevel(VerificationLevel::intOnly());
        $request->setSignatureIndex(-1);
        $request->setPreprocessorArguments(new PropertyMap([]));

        $event = $this->stopwatch->start('pdf-as.verify', 'esign');
        try {
            return $client->verify($request, self::PDF_AS_TIMEOUT);
        } catch (\SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
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
     * @throws SigningException
     */
    public function fetchQualifiedlySignedDocument(string $sessionId): string
    {
        $stack = HandlerStack::create();
        $stack->push(Tools::createStopwatchMiddleware($this->stopwatch, 'pdf-as.fetch-qualified', 'esign'));
        $client = new Client(['handler' => $stack]);

        $url = $this->getQualifiedlySignedDocumentUrl($sessionId);

        try {
            $response = $client->request('GET', $url);
            $signedPdfData = (string) $response->getBody();

            if ($response->getHeader('Content-Type')[0] !== 'application/pdf') {
                // PDF-AS doesn't use 404 status code when document wasn't found
                if (strpos($signedPdfData, '<p>No signed pdf document available.</p>') !== false) {
                    throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' was not found!", $sessionId));
                }

                throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded!", $sessionId));
            }
        } catch (RequestException $e) {
            switch ($e->getCode()) {
                case 403:
                    throw new SigningException(sprintf("Access to QualifiedlySignedDocument with id '%s' is not allowed!", $sessionId));
            }

            throw new SigningException(sprintf("QualifiedlySignedDocument with id '%s' could not be loaded! Message: %s", $sessionId, $e->getMessage()));
        }

        $this->log('PDF was qualifiedly signed', ['session_id' => $sessionId, 'signed_content_size' => strlen($signedPdfData)]);

        return $signedPdfData;
    }

    public function getAdvancedlySignRequiredRole(string $profileName): string
    {
        $profile = $this->getAdvancedProfileData($profileName);
        if ($profile->getRole() === null) {
            throw new SigningException('No role set');
        }

        return $profile->getRole();
    }

    public function getQualifiedlySignRequiredRole(string $profileName): string
    {
        $profile = $this->getQualifiedProfileData($profileName);
        if ($profile->getRole() === null) {
            throw new SigningException('No role set');
        }

        return $profile->getRole();
    }
}
