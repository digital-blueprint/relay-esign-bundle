<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Callbacks;

use Dbp\Relay\EsignBundle\PdfAsApi\CallbackTokenService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class CallbackController
{
    public function __construct(private CallbackTokenService $callbackTokenService)
    {
    }

    #[Route(path: '/esign/_success', name: 'esign_callback_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $id = $request->query->getString('_dbpRelayEsignId');
        $token = $request->query->getString('_dbpRelayEsignToken');
        $expires = $request->query->getInt('_dbpRelayEsignExpires');
        $this->callbackTokenService->verifyToken($token, $id, $expires);

        return new BinaryFileResponse(__DIR__.'/success.html');
    }

    #[Route(path: '/esign/_error', name: 'esign_callback_error', methods: ['GET'])]
    public function error(): Response
    {
        return new BinaryFileResponse(__DIR__.'/error.html');
    }
}
