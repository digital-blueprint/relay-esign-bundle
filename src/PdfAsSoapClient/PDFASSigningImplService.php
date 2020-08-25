<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class PDFASSigningImplService extends PDFASBaseService
{
    /**
     * @var array The defined classes
     */
    private static $classmap = [
      'SignRequest' => __NAMESPACE__.'\\SignRequest',
      'SignParameters' => __NAMESPACE__.'\\SignParameters',
      'PropertyMap' => __NAMESPACE__.'\\PropertyMap',
      'PropertyEntry' => __NAMESPACE__.'\\PropertyEntry',
      'SignResponse' => __NAMESPACE__.'\\SignResponse',
      'VerificationResponse' => __NAMESPACE__.'\\VerificationResponse',
      'BulkSignRequest' => __NAMESPACE__.'\\BulkSignRequest',
      'BulkSignResponse' => __NAMESPACE__.'\\BulkSignResponse',
    ];

    /**
     * @param string $location           The service location
     * @param int    $connection_timeout Connection timeout in seconds
     *
     * @throws \SoapFault
     */
    public function __construct(string $location, int $connection_timeout = -1)
    {
        $options = [];
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }

        if ($connection_timeout >= 0) {
            $options['connection_timeout'] = $connection_timeout;
        }

        $wsdl_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'wsdl.sign.xml';
        $wsdl_uri = 'file://'.implode('/', array_map('rawurlencode', explode('/', $wsdl_path)));

        $options = array_merge([
          'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
          'cache_wsdl' => WSDL_CACHE_NONE,
          'location' => $location,
      ], $options);
        \SoapClient::__construct($wsdl_uri, $options);
    }

    /**
     * @throws \SoapFault (e.g. "looks like we got no XML document")
     *
     * @param int $timeout Timeout in seconds
     *
     * @return SignResponse
     */
    public function signSingle(SignRequest $signRequest, int $timeout = -1)
    {
        return $this->callWithTimeout('signSingle', [$signRequest], $timeout);
    }

    /**
     * @param int $timeout Timeout in seconds
     *
     * @return BulkSignResponse
     */
    public function signBulk(BulkSignRequest $signBulkRequest, int $timeout = -1)
    {
        return $this->callWithTimeout('signBulk', [$signBulkRequest], $timeout);
    }
}
