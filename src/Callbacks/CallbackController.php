<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Callbacks;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CallbackController extends AbstractController
{
    /**
     * @Route("/esign/_success", name="esign_callback_success")
     */
    public function success(): Response
    {
        return new BinaryFileResponse(__DIR__.'/success.html');
    }

    /**
     * @Route("/esign/_error", name="esign_callback_error")
     */
    public function error(): Response
    {
        return new BinaryFileResponse(__DIR__.'/error.html');
    }
}
