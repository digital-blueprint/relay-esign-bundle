<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

class PDFASSigningImplService extends PDFASBaseService
{
    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
      'SignRequest' => __NAMESPACE__ . '\\SignRequest',
      'SignParameters' => __NAMESPACE__ . '\\SignParameters',
      'PropertyMap' => __NAMESPACE__ . '\\PropertyMap',
      'PropertyEntry' => __NAMESPACE__ . '\\PropertyEntry',
      'SignResponse' => __NAMESPACE__ . '\\SignResponse',
      'VerificationResponse' => __NAMESPACE__ . '\\VerificationResponse',
      'BulkSignRequest' => __NAMESPACE__ . '\\BulkSignRequest',
      'BulkSignResponse' => __NAMESPACE__ . '\\BulkSignResponse',
    );

    /**
     * @param string $location The service location
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

      $wsdl_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wsdl.sign.xml';
      $wsdl_uri = 'file://' . implode('/', array_map('rawurlencode', explode('/', $wsdl_path)));

      $options = array_merge(array (
          'features' =>  SOAP_SINGLE_ELEMENT_ARRAYS,
          'cache_wsdl' => WSDL_CACHE_NONE,
          'location' => $location,
      ), $options);
      \SoapClient::__construct($wsdl_uri, $options);
    }

    /**
     * @param SignRequest $signRequest
     * @throws \SoapFault (e.g. "looks like we got no XML document")
     * @return SignResponse
     */
    public function signSingle(SignRequest $signRequest)
    {
      return $this->__soapCall('signSingle', array($signRequest));
    }

    /**
     * @param BulkSignRequest $signBulkRequest
     * @return BulkSignResponse
     */
    public function signBulk(BulkSignRequest $signBulkRequest)
    {
      return $this->__soapCall('signBulk', array($signBulkRequest));
    }

}
