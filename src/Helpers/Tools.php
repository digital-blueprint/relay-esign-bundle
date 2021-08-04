<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\Helpers;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Uid\Uuid;

class Tools
{
    /**
     * Like json_decode but throws on invalid json data.
     *
     * @throws \JsonException
     *
     * @return mixed
     */
    public static function decodeJSON(string $json, bool $assoc = false)
    {
        $result = json_decode($json, $assoc);
        $json_error = json_last_error();
        if ($json_error !== JSON_ERROR_NONE) {
            throw new \JsonException(sprintf('%s: "%s"', json_last_error_msg(), print_r($json, true)));
        }

        return $result;
    }

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

        return $result;
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
        $uuid = Uuid::v4();

        return $uuid->toRfc4122();
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
        } else {
            $prefix = '';
            if (count($prevName) > 0) {
                $prefix = implode('.', $prevName).'.';
            }
            if (!empty($ext)) {
                $ext = '.'.$ext;
            }

            return $prefix.$name.'-sig'.$ext;
        }
    }
}
