<?php

namespace DBP\API\ESignBundle\Helpers;

use GuzzleHttp\Psr7\Uri;

class Tools
{
    /**
     * Returns the same uri but with the default port included
     * unless it already contains a port, then it is returned unchanged.
     *
     * @param string $uri_string
     * @return string
     */
    public static function getUriWithPort(string $uri_string): string {
        $uri = new Uri($uri_string);
        if ($uri->getPort() !== null)
            return (string)$uri;
        if ($uri->getScheme() === 'https')
            $replace = '443';
        else if ($uri->getScheme() === 'http')
            $replace = '80';
        else
            throw new \RuntimeException("Unsupported scheme");
        $dummy = $uri->withPort(1234);
        $result =  preg_replace('/:1234/', ':' . $replace, (string)$dummy, 1);
        if ($result === null)
            throw new \RuntimeException("preg failed");
        return (string)$result;
    }
}
