<?php

declare(strict_types=1);

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class PDFASVerificationImplService extends PDFASBaseService
{
    /**
     * @var array The defined classes
     */
    private static $classmap = [
      'VerifyRequest' => __NAMESPACE__.'\\VerifyRequest',
      'PropertyMap' => __NAMESPACE__.'\\PropertyMap',
      'PropertyEntry' => __NAMESPACE__.'\\PropertyEntry',
      'VerifyResponse' => __NAMESPACE__.'\\VerifyResponse',
      'VerifyResult' => __NAMESPACE__.'\\VerifyResult',
    ];

    /**
     * @param string $location The service location
     *
     * @throws \SoapFault
     */
    public function __construct(string $location)
    {
        $options = [];
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }

        $wsdl_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'wsdl.verify.xml';
        $wsdl_uri = 'file://'.implode('/', array_map('rawurlencode', explode('/', $wsdl_path)));

        $options = array_merge([
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'location' => $location,
        ], $options);
        \SoapClient::__construct($wsdl_uri, $options);
    }

    /**
     * @throws \SoapFault
     *
     * @return VerifyResponse
     */
    public function verify(VerifyRequest $verifyRequest)
    {
        return $this->__soapCall('verify', [$verifyRequest]);
    }
}
