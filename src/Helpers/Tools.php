<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\Helpers;

use GuzzleHttp\Psr7\Uri;

class Tools
{
    /**
     * Returns the same uri but with the default port included
     * unless it already contains a port, then it is returned unchanged.
     */
    public static function getUriWithPort(string $uri_string): string
    {
        $uri = new Uri($uri_string);
        if ($uri->getPort() !== null) {
            return (string) $uri;
        }
        if ($uri->getScheme() === 'https') {
            $replace = '443';
        } elseif ($uri->getScheme() === 'http') {
            $replace = '80';
        } else {
            throw new \RuntimeException('Unsupported scheme');
        }
        $dummy = $uri->withPort(1234);
        $result = preg_replace('/:1234/', ':'.$replace, (string) $dummy, 1);
        if ($result === null) {
            throw new \RuntimeException('preg failed');
        }

        return (string) $result;
    }

    /**
     * Convert binary data to a data url.
     */
    public static function getDataURI(string $data, string $mime): string
    {
        return 'data:'.$mime.';base64,'.base64_encode($data);
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
}
