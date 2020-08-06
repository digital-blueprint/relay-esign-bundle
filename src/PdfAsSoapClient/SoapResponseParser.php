<?php

namespace DBP\API\ESignBundle\PdfAsSoapClient;

// https://www.w3.org/TR/soap12-mtom/
// https://en.wikipedia.org/wiki/Message_Transmission_Optimization_Mechanism

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
        $parts = $this->parseMultiPart($inputData);

        $byID = [];
        foreach ($parts as $part) {
            if (!array_key_exists('content-id', $part) || !array_key_exists('content', $part)) {
                throw new SoapResponseParserError('Missing content-id/content');
            }
            $byID[$part['content-id']] = $part['content'];
        }

        $rootID = '<root.message@cxf.apache.org>';
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
            $cid = '<'.$cid[1].'>';

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
     * Splits up the response into parts and parses them, returns a list of parsed parts.
     */
    private function parseMultiPart(string $inputData): array
    {
        $fp = fopen('php://memory', 'r+');
        fputs($fp, $inputData);
        rewind($fp);

        $parts = [];
        $current = [];
        $startBoundary = null;
        $endBoundary = null;
        while ($line = fgets($fp)) {
            $stripped = rtrim($line);
            if ($startBoundary === null) {
                $startBoundary = $stripped;
                $endBoundary = $startBoundary.'--';
                continue;
            }

            if ($stripped === $startBoundary || $stripped === $endBoundary) {
                $parts[] = $this->parsePart($current);
                $current = [];
            }

            if ($stripped === $startBoundary) {
                continue;
            }

            if ($stripped === $endBoundary) {
                break;
            }

            $current[] = $line;
        }
        fclose($fp);

        if (count($current)) {
            throw new SoapResponseParserError('missing end boundary');
        }

        return $parts;
    }

    /**
     * Parses a part and returns an array with the headers and a special "content" key with the content.
     *
     * @param string[] $lines
     */
    private function parsePart(array $lines): array
    {
        $headers = [];
        $body = '';
        $inHeader = true;
        foreach ($lines as $line) {
            if ($inHeader && rtrim($line) === '') {
                $inHeader = false;
                continue;
            }

            if ($inHeader) {
                $headerParts = explode(':', $line, 2);
                if (count($headerParts) !== 2) {
                    throw new SoapResponseParserError('Invalid header: '.$line);
                }
                [$key, $value] = $headerParts;
                $value = trim($value);
                $key = strtolower($key);
                $headers[$key] = $value;
            } else {
                $body .= $line;
            }
        }

        $headers['content'] = $body;

        return $headers;
    }
}
