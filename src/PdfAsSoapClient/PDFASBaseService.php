<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

use SoapFault;

class PDFASBaseService extends \SoapClient
{
    // For mocking in tests
    public function __doParentRequest($req, $location, $action, $version, $one_way)
    {
        return parent::__doRequest($req, $location, $action, $version, $one_way);
    }

    /**
     * @return mixed
     */
    protected function callWithTimeout(string $function_name, array $arguments, int $timeout)
    {
        $socketTimeout = ini_get('default_socket_timeout');
        if ($timeout >= 0) {
            ini_set('default_socket_timeout', (string) $timeout);
        }
        try {
            return $this->__soapCall($function_name, $arguments);
        } finally {
            ini_set('default_socket_timeout', $socketTimeout);
        }
    }

    /**
     * @param string $req
     * @param string $location
     * @param string $action
     * @param int    $version
     * @param int    $one_way
     *
     * @return string
     *
     * @throws SoapResponseParserError
     * @throws SoapFault
     */
    public function __doRequest($req, $location, $action, $version = SOAP_1_1, $one_way = 0)
    {
        $response = $this->__doParentRequest($req, $location, $action, $version, $one_way);

        // happens for example if the request is denied by the server or a timeout happens
        if ($response === null) {
            throw new SoapFault('no-data-returned', 'No data returned by SOAP request!');
        }

        // Sometimes soap errors get sent as XML already
        if (substr($response, 0, strlen('<soap')) === '<soap') {
            return $response;
        }

        $parser = new SoapResponseParser();
        $xml = $parser->parse($response);

        return $xml;
    }
}
