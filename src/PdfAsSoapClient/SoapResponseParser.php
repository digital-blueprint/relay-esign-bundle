<?php

declare(strict_types=1);

namespace Dbp\Relay\EsignBundle\PdfAsSoapClient;

// https://en.wikipedia.org/wiki/Message_Transmission_Optimization_Mechanism
// https://www.w3.org/TR/SOAP-attachments/#RFC2557
// https://www.ietf.org/rfc/rfc2045.txt

use ZBateson\MailMimeParser\MailMimeParser;

class SoapResponseParser
{
    /**
     * This only handles what we get from pdf-as and not everything the standards specify... but we try to be strict
     * with what we support at least, so there will be errors if things change.
     *
     * Returns valid XML which can be handled by the PHP SoapClient.
     *
     * @throws SoapResponseParserError
     */
    public function parse(string $inputData): string
    {
        $rootID = 'root.message@cxf.apache.org';

        // First we try to create a valid mime multipart input and parse it
        $inputData = $this->fixupHeader($inputData, $rootID);
        $mailParser = new MailMimeParser();
        $result = $mailParser->parse($inputData);
        $parts = [];
        foreach ($result->getChildParts() as $part) {
            $parsed_part = [
                'content-type' => $part->getContentType(),
                'content-transfer-encoding' => $part->getContentTransferEncoding(),
                'content-id' => $part->getContentId(),
                'content' => $part->getBinaryContentStream()->getContents(),
            ];
            $parts[] = $parsed_part;
        }

        // Then we inject the attachments into the main XML response
        $byID = [];
        foreach ($parts as $part) {
            if (!array_key_exists('content-id', $part) || !array_key_exists('content', $part)) {
                throw new SoapResponseParserError('Missing content-id/content');
            }
            $byID[$part['content-id']] = $part['content'];
        }

        if (!array_key_exists($rootID, $byID)) {
            throw new SoapResponseParserError('Missing root ID: '.$rootID);
        }
        $baseXML = $byID[$rootID];
        $xop_elements = [];
        $res = preg_match_all('/<xop[\s\S]*?\/>/', $baseXML, $xop_elements);
        if ($res === false) {
            throw new SoapResponseParserError('Failed to find xop elements');
        }
        $xop_elements = reset($xop_elements);

        foreach ($xop_elements as $xop_element) {
            $cid = [];
            $res = preg_match('/cid:([^\'"]+)/', $xop_element, $cid);
            if ($res === false || $res === 0) {
                throw new SoapResponseParserError('Failed to find cid');
            }
            $cid = $cid[1];

            if (!array_key_exists($cid, $byID)) {
                throw new SoapResponseParserError('Missing ID: '.$cid);
            }
            $binary = $byID[$cid];
            $binary = base64_encode($binary);

            $baseXML = str_replace($xop_element, $binary, $baseXML);
        }

        return $baseXML;
    }

    /**
     * PHP doesn't give us the MIME header, so try to generate one.
     *
     * @throws SoapResponseParserError
     */
    private function fixupHeader(string $inputData, string $startId): string
    {
        $boundary = $this->findBoundary($inputData);

        $new = "MIME-Version: 1.0\r\n";
        $new .= 'Content-Type: Multipart/Related; boundary='.$boundary.';type=text/xml;start="<'.$startId.">\"\r\n";
        $new .= "\r\n";
        $new .= $inputData;

        return $new;
    }

    /**
     * Try to figure out a boundary ID, or null if not found.
     *
     * @throws SoapResponseParserError
     */
    private function findBoundary(string $inputData): string
    {
        $sub = strstr($inputData, "\r\n", true);
        if ($sub === false) {
            throw new SoapResponseParserError('Failed to find boundary');
        }

        return trim($sub, '-');
    }
}
