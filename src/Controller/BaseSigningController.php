<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Controller;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\Service\UserDefinedText;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseSigningController extends AbstractController
{
    /**
     * Request::get() is internal starting with Symfony 5.4, so we duplicate a subset of the logic we need here.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public static function requestGet(Request $request, string $key, $default = null)
    {
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }

        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }

        return $default;
    }

    /**
     * @return UserDefinedText[]
     */
    public function parseUserText(string $data): array
    {
        // Parse and validate the basics
        try {
            $parsed = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, 'invalid JSON');
        }
        if (!is_array($parsed)) {
            throw new ApiError(Response::HTTP_BAD_REQUEST, 'invalid content');
        }
        foreach ($parsed as $entry) {
            if (!is_array($entry) || ($entry['description'] ?? '') === '' || ($entry['value'] ?? '') === '') {
                throw new ApiError(Response::HTTP_BAD_REQUEST, 'invalid content');
            }
        }

        $userText = [];
        foreach ($parsed as $entry) {
            $description = $entry['description'];
            $value = $entry['value'];
            $userText[] = new UserDefinedText($description, $value);
        }

        return $userText;
    }
}
