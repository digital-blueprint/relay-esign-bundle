<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Api;

use Dbp\Relay\CoreBundle\Exception\ApiError;
use Dbp\Relay\EsignBundle\PdfAsApi\UserDefinedText;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class Utils
{
    /**
     * Convert binary data to a data url.
     */
    public static function getDataURI(string $data, string $mime): string
    {
        return 'data:'.$mime.';base64,'.base64_encode($data);
    }

    public static function generateSignedFileName(string $fileName): string
    {
        $parts = explode('.', $fileName);

        if (count($parts) < 2) {
            [$name, $ext] = [$parts[0], ''];
        } else {
            $ext = end($parts);
            $name = prev($parts);
        }
        $parts = explode('.', $fileName);

        $prevName = array_slice($parts, 0, -2);

        if (str_ends_with($name, '-sig')) {
            return $fileName;
        }
        $prefix = '';
        if (count($prevName) > 0) {
            $prefix = implode('.', $prevName).'.';
        }
        if (!empty($ext)) {
            $ext = '.'.$ext;
        }

        return $prefix.$name.'-sig'.$ext;
    }

    /**
     * @return UserDefinedText[]
     */
    public static function parseUserText(string $data): array
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

    public static function validateUploadedFile(?UploadedFile $uploadedFile): UploadedFile
    {
        // check if there is an uploaded file
        if ($uploadedFile === null) {
            throw new BadRequestHttpException('No file with parameter key "file" was received!');
        }

        // If the upload failed, figure out why
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new BadRequestHttpException($uploadedFile->getErrorMessage());
        }

        // check if file is a pdf
        if ($uploadedFile->getMimeType() !== 'application/pdf') {
            throw new UnsupportedMediaTypeHttpException('Only PDF files can be signed!');
        }

        // check if file is empty
        if ($uploadedFile->getSize() === 0) {
            throw new BadRequestHttpException('Empty files cannot be signed!');
        }

        // check if file is too large
        $MAX_PDF_SIGN_FILE_SIZE = 32 * 1024 * 1024;
        if ($uploadedFile->getSize() > $MAX_PDF_SIGN_FILE_SIZE) {
            throw new ApiError(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'PDF file too large to sign (32MB limit)!');
        }

        return $uploadedFile;
    }
}
