<?php

declare(strict_types=1);
/**
 * PDF AS service.
 */

namespace DBP\API\ESignBundle\Service;

use DBP\API\ESignBundle\Helpers\Tools;
use DBP\API\ESignBundle\PdfAsSoapClient\Connector;
use DBP\API\ESignBundle\PdfAsSoapClient\PDFASSigningImplService;
use DBP\API\ESignBundle\PdfAsSoapClient\PDFASVerificationImplService;
use DBP\API\ESignBundle\PdfAsSoapClient\PropertyEntry;
use DBP\API\ESignBundle\PdfAsSoapClient\PropertyMap;
use DBP\API\ESignBundle\PdfAsSoapClient\SignParameters;
use DBP\API\ESignBundle\PdfAsSoapClient\SignRequest;
use DBP\API\ESignBundle\PdfAsSoapClient\VerificationLevel;
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyRequest;
use DBP\API\ESignBundle\PdfAsSoapClient\VerifyResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use League\Uri\Contracts\UriException;
use League\Uri\UriTemplate;
use Psr\Log\LoggerInterface;
use SoapFault;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PdfAsApi implements SignatureProviderInterface
{
    private $logger;

    private const PDF_AS_TIMEOUT = 40;

    private $advancedUrl;
    private $qualifiedUrl;
    private $qualifiedStaticUrl;
    private $advancedProfiles;
    private $qualifiedProfile;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->logger = $logger;

        $config = $container->getParameter('dbp_api.esign.config');
        $this->advancedUrl = $config['advanced_url'] ?? '';
        $this->qualifiedUrl = $config['qualified_url'] ?? '';
        $this->qualifiedStaticUrl = $config['qualified_static_url'] ?? '';
        $this->qualifiedProfile = $config['qualified_profile'] ?? [];
        $this->advancedProfiles = $config['advanced_profiles'] ?? [];
    }

    /**
     * @throws SigningException
     */
    public function createQualifiedSigningRequestRedirectUrl(string $data, string $requestId = '', array $positionData = [], array $userText = []): string
    {
        if ($requestId === '') {
            $requestId = Tools::generateRequestId();
        }

        try {
            $service = new PDFASSigningImplService($this->qualifiedUrl.'/services/wssign', self::PDF_AS_TIMEOUT);
        } catch (SoapFault $e) {
            throw new SigningException('Signing soap call failed, wsdl URI cannot be loaded!');
        }

        $profile = $this->qualifiedProfile;
        $params = new SignParameters(Connector::mobilebku());
        $params->setProfile($profile['profile_id'] ?? '');

        // Add custom user defined text if needed
        $overrides = $this->buildUserTextConfigOverride($profile, $userText);
        if (count($overrides) > 0) {
            $configurationOverrides = new PropertyMap($overrides);
            $params->setConfigurationOverrides($configurationOverrides);
        }

        $staticUri = $this->qualifiedStaticUrl;
        $params->setInvokeurl($staticUri.'/callback.html');
        // it's important to add the port "443", PDF-AS has a bug that will set the port to "-1" if it isn't set
        $params->setInvokeerrorurl(Tools::getUriWithPort($staticUri.'/error.html'));

        // add signature position data if there is any
        if (count($positionData) !== 0) {
            array_walk($positionData, function (&$item, $key) { $item = "$key:$item"; });
            $params->setPosition(implode(';', $positionData));
        }

        $request = new SignRequest($data, $params, $requestId);

        try {
            // can and will throw a SoapFault "looks like we got no XML document"
            $response = $service->signSingle($request, self::PDF_AS_TIMEOUT);
        } catch (SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
        }

        if ($response->getError() !== null) {
            throw new SigningException('Failed fetching redirectURL: '.$response->getError());
        }

        // get the redirect url
        $redirectUrl = $response->getRedirectUrl();

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
    public function verifyPdfData($data, $requestId = '')
    {
        $response = $this->doVerifyRequest($data, $requestId);

        $results = $response->getVerifyResults();
        $this->log('PDF was verified', ['request_id' => $requestId]);

        return $results;
    }

    private function getProfileData(string $profileName)
    {
        foreach ($this->advancedProfiles as $profile) {
            if ($profile['name'] === $profileName) {
                return $profile;
            }
        }
        throw new SigningException('Unknown profile');
    }

    /**
     * @param UserDefinedText[] $userText
     *
     * @return PropertyEntry[]
     */
    public function buildUserTextConfigOverride(array $profile, array $userText): array
    {
        if (count($userText) === 0) {
            return [];
        }

        $parentTable = $profile['profile_user_text_parent_table'] ?? '';
        /* @var int $parentTableRow */
        $parentTableRow = $profile['profile_user_text_parent_table_row'] ?? 0;
        $table = $profile['profile_user_text_table'] ?? '';
        $profileId = $profile['profile_id'];

        if ($table === '') {
            throw new SigningException('user_text not available/implemented for this profile');
        }

        // validate the config, so we don't write out invalid config lines
        if (!preg_match('/[^.-]+/', $table) || !preg_match('/[^.-]+/', $parentTable)) {
            throw new \RuntimeException('invalid table id');
        }
        if ($parentTableRow < 0) {
            throw new \RuntimeException('invalid table row');
        }
        if (!preg_match('/[^.-]+/', $profileId)) {
            throw new \RuntimeException('invalid profile id');
        }

        // If the parent and user text table are the same, we just append rows to it at the end,
        // otherwise we append rows to the user table and append the user table to the parent one at the end
        $isSameTable = ($table === $parentTable);

        $overrides = [];
        $tableRow = $isSameTable ? $parentTableRow : 1;
        foreach ($userText as $entry) {
            $desc = $entry->getDescription();
            $value = $entry->getValue();

            $entryId = 'SIG_USER_TEXT_'.$table.'_'.$tableRow;
            $overrides[] = new PropertyEntry("sig_obj.$profileId.key.$entryId", $desc);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.value.$entryId", $value);
            $overrides[] = new PropertyEntry("sig_obj.$profileId.table.$table.$tableRow", $entryId.'-cv');
            ++$tableRow;
        }

        if (count($overrides) && !$isSameTable) {
            // pdf-as fails on empty tables, so we have to attach it only when there are rows to add
            $overrides[] = new PropertyEntry("sig_obj.$profileId.table.$parentTable.$parentTableRow", 'TABLE-'.$table);
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
        $profile = $this->getProfileData($profileName);

        if ($requestId === '') {
            $requestId = Tools::generateRequestId();
        }

        try {
            $service = new PDFASSigningImplService($this->advancedUrl.'/services/wssign', self::PDF_AS_TIMEOUT);
        } catch (SoapFault $e) {
            throw new SigningException('Signing soap call failed, wsdl URI cannot be loaded!');
        }

        $params = new SignParameters(Connector::jks());
        $params->setKeyIdentifier($profile['key_id']);
        $params->setProfile($profile['profile_id']);

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

        $request = new SignRequest($data, $params, $requestId);
        try {
            // can and will throw a SoapFault "looks like we got no XML document"
            $response = $service->signSingle($request, self::PDF_AS_TIMEOUT);
        } catch (SoapFault $e) {
            $this->handleSoapFault($e);
            throw new SigningException();
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
     * @param mixed[] $context
     */
    private function log(string $message, array $context = [])
    {
        $context['service'] = 'PdfAs';
        $this->logger->notice('[{service}] '.$message, $context);
    }

    /**
     * @throws UriException
     */
    protected function getQualifiedlySignedDocumentUrl(string $requestId): string
    {
        $uriTemplate = new UriTemplate('/PDFData;jsessionid={requestId}');

        return $this->qualifiedUrl.$uriTemplate->expand([
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
}
