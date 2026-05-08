<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\BasePersonBundle\API\PersonProviderInterface;
use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Authorization\AuthorizationService;
use Dbp\Relay\EsignBundle\Configuration\BundleConfig;
use Dbp\Relay\EsignBundle\PdfAsApi\PdfAsApi;
use Dbp\Relay\EsignBundle\PdfAsApi\SignatureBlockPosition;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningException;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningRequest;
use Dbp\Relay\EsignBundle\PdfAsApi\SigningUnavailableException;
use Dbp\Relay\EsignBundle\PdfAsApi\SystemDefinedText;
use Dbp\Relay\EsignBundle\PdfAsApi\Utils as PdfAsApiUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class CreateAdvancedlySignedDocumentAction
{
    private $api;

    public function __construct(
        PdfAsApi $api,
        private readonly AuthorizationService $authorizationService,
        private readonly BundleConfig $config,
        private TranslatorInterface $translator,
        private readonly ?PersonProviderInterface $personProvider = null,
    ) {
        $this->api = $api;
    }

    /**
     * @throws HttpException
     */
    public function __invoke(Request $request): AdvancedlySignedDocument
    {
        $this->authorizationService->checkCanSign();

        $profileName = Utils::requestGet($request, 'profile');
        if ($profileName === null) {
            throw new BadRequestHttpException('Missing "profile"');
        }

        $this->authorizationService->checkCanSignWithProfile($profileName);

        $fullname = null;
        if ($this->personProvider !== null && $this->config->getProfile($profileName) !== null && $this->config->getProfile($profileName)->getIncludeUsername()) {
            $fullname = $this->personProvider->getCurrentPerson()->getGivenName().' '.$this->personProvider->getCurrentPerson()->getFamilyName();
        }

        /** @var ?UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $uploadedFile = Utils::validateUploadedFile($uploadedFile);

        $hasPositionParams = false;

        $x = SignatureBlockPosition::AUTO;
        if (Utils::requestGet($request, 'x', '') !== '') {
            $x = (float) Utils::requestGet($request, 'x');
            $hasPositionParams = true;
        }

        $y = SignatureBlockPosition::AUTO;
        if (Utils::requestGet($request, 'y', '') !== '') {
            $y = (float) Utils::requestGet($request, 'y');
            $hasPositionParams = true;
        }

        $width = SignatureBlockPosition::AUTO;
        if ($request->request->has('width')) {
            $width = $request->request->filter('width', filter: \FILTER_VALIDATE_FLOAT);
            $hasPositionParams = true;
        } elseif (Utils::requestGet($request, 'w', '') !== '') {
            $width = (float) Utils::requestGet($request, 'w');
            $hasPositionParams = true;
        }

        $rotation = 0.0;
        if ($request->request->has('rotation')) {
            $rotation = $request->request->filter('rotation', filter: \FILTER_VALIDATE_FLOAT);
            $hasPositionParams = true;
        } elseif (Utils::requestGet($request, 'r', '') !== '') {
            $rotation = (float) Utils::requestGet($request, 'r');
            $hasPositionParams = true;
        }

        $page = SignatureBlockPosition::AUTO;
        if ($request->request->has('page')) {
            $page = $request->request->getInt('page');
            $hasPositionParams = true;
        } elseif (Utils::requestGet($request, 'p', '') !== '') {
            $page = (int) Utils::requestGet($request, 'p');
            $hasPositionParams = true;
        }

        $userText = [];
        if ($request->request->has('user_text')) {
            $data = $request->request->all()['user_text'];
            $userText = Utils::parseUserText($data);
        }
        $systemText = [];
        if ($fullname !== null) {
            $desc = $this->translator->trans('table_contents.signer', domain: 'dbp_relay_esign_bundle', locale: $this->config->getProfile($profileName)->getLanguage());
            $systemText = [new SystemDefinedText($desc, $fullname)];
        }

        $invisible = $request->request->getBoolean('invisible');
        if ($invisible && $hasPositionParams) {
            throw new BadRequestHttpException('Position parameters are not allowed in case the signature block is set to invisible');
        }

        $data = $uploadedFile->getContent();

        // sign the pdf data
        $requestId = PdfAsApiUtils::generateRequestId();

        // if for whatever reason a negative value appears where it shouldnt be
        // then fallback to auto placement
        if ($hasPositionParams && ($x < 0 || $y < 0 || $width < 0)) {
            $x = SignatureBlockPosition::AUTO;
            $y = SignatureBlockPosition::AUTO;
            $page = SignatureBlockPosition::AUTO;
            $width = SignatureBlockPosition::AUTO;
            $rotation = 0.0;
        }

        $blockPosition = new SignatureBlockPosition(x: $x, y: $y, width : $width, rotation: $rotation, page: $page);
        $request = new SigningRequest($data, $profileName, $requestId, $blockPosition, $userText, invisible: $invisible, systemText: $systemText);

        try {
            $result = $this->api->advancedlySignPdf($request);
        } catch (SigningUnavailableException $e) {
            throw new ServiceUnavailableHttpException(100, $e->getMessage());
        } catch (SigningException $e) {
            throw new ApiError(Response::HTTP_BAD_GATEWAY, $e->getMessage());
        }

        // add some suffix for signed documents
        $signedFileName = Utils::generateSignedFileName($uploadedFile->getClientOriginalName());
        $signedPdfData = $result->getSignedPDF();

        $document = new AdvancedlySignedDocument();
        $document->setIdentifier($requestId);
        $document->setContentUrl(Utils::getDataURI($signedPdfData, 'application/pdf'));
        $document->setName($signedFileName);
        $document->setContentSize(strlen($signedPdfData));

        return $document;
    }
}
