<?php

namespace SAML2;

/**
 * Class which implements the HTTP-Redirect binding.
 *
 * @package SimpleSAMLphp
 */
class HTTPRedirect extends Binding
{
    const DEFLATE = 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE';

    /**
     * Create the redirect URL for a message.
     *
     * @param  \SAML2\Message $message The message.
     * @return string        The URL the user should be redirected to in order to send a message.
     */
    public function getRedirectURL(Message $message)
    {
        if ($this->destination === NULL) {
            $destination = $message->getDestination();
        } else {
            $destination = $this->destination;
        }

        $relayState = $message->getRelayState();

        $key = $message->getSignatureKey();

        $msgStr = $message->toUnsignedXML();
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);

        Utils::getContainer()->debugMessage($msgStr, 'out');

        $msgStr = gzdeflate($msgStr);
        $msgStr = base64_encode($msgStr);

        /* Build the query string. */

        if ($message instanceof Request) {
            $msg = 'SAMLRequest=';
        } else {
            $msg = 'SAMLResponse=';
        }
        $msg .= urlencode($msgStr);

        if ($relayState !== NULL) {
            $msg .= '&RelayState=' . urlencode($relayState);
        }

        if ($key !== NULL) {
            /* Add the signature. */
            $msg .= '&SigAlg=' . urlencode($key->type);

            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }

        if (strpos($destination, '?') === FALSE) {
            $destination .= '?' . $msg;
        } else {
            $destination .= '&' . $msg;
        }

        return $destination;
    }

    /**
     * Send a SAML 2 message using the HTTP-Redirect binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     */
    public function send(Message $message)
    {
        $destination = $this->getRedirectURL($message);
        Utils::getContainer()->getLogger()->debug('Redirect to ' . strlen($destination) . ' byte URL: ' . $destination);
        Utils::getContainer()->redirect($destination);
    }

    /**
     * Receive a SAML 2 message sent using the HTTP-Redirect binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @return \SAML2\Message The received message.
     * @throws Exception
     */
    public function receive()
    {
        $data = self::parseQuery();

        if (array_key_exists('SAMLRequest', $data)) {
            $msg = $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            $msg = $data['SAMLResponse'];
        } else {
            throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
        }

        if (array_key_exists('SAMLEncoding', $data)) {
            $encoding = $data['SAMLEncoding'];
        } else {
            $encoding = self::DEFLATE;
        }

        $msg = base64_decode($msg);
        switch ($encoding) {
            case self::DEFLATE:
                $msg = gzinflate($msg);
                break;
            default:
                throw new Exception('Unknown SAMLEncoding: ' . var_export($encoding, TRUE));
        }

        Utils::getContainer()->debugMessage($msg, 'in');

        $document = DOMDocumentFactory::fromString($msg);
        $xml = $document->firstChild;

        $msg = Message::fromXML($xml);

        if (array_key_exists('RelayState', $data)) {
            $msg->setRelayState($data['RelayState']);
        }

        if (array_key_exists('Signature', $data)) {
            if (!array_key_exists('SigAlg', $data)) {
                throw new Exception('Missing signature algorithm.');
            }

            $signData = array(
                'Signature' => $data['Signature'],
                'SigAlg' => $data['SigAlg'],
                'Query' => $data['SignedQuery'],
            );
            $msg->addValidator(array(get_class($this), 'validateSignature'), $signData);
        }

        return $msg;
    }

    /**
     * Helper function to parse query data.
     *
     * This function returns the query string split into key=>value pairs.
     * It also adds a new parameter, SignedQuery, which contains the data that is
     * signed.
     *
     * @return string The query data that is signed.
     */
    private static function parseQuery()
    {
        /*
         * Parse the query string. We need to do this ourself, so that we get access
         * to the raw (urlencoded) values. This is required because different software
         * can urlencode to different values.
         */
        $data = array();
        $relayState = '';
        $sigAlg = '';
        $sigQuery = '';
        foreach (explode('&', $_SERVER['QUERY_STRING']) as $e) {
            $tmp = explode('=', $e, 2);
            $name = $tmp[0];
            if (count($tmp) === 2) {
                $value = $tmp[1];
            } else {
                /* No value for this paramter. */
                $value = '';
            }
            $name = urldecode($name);
            $data[$name] = urldecode($value);

            switch ($name) {
                case 'SAMLRequest':
                case 'SAMLResponse':
                    $sigQuery = $name . '=' . $value;
                    break;
                case 'RelayState':
                    $relayState = '&RelayState=' . $value;
                    break;
                case 'SigAlg':
                    $sigAlg = '&SigAlg=' . $value;
                    break;
            }
        }

        $data['SignedQuery'] = $sigQuery . $relayState . $sigAlg;

        return $data;
    }

    /**
     * Validate the signature on a HTTP-Redirect message.
     *
     * Throws an exception if we are unable to validate the signature.
     *
     * @param array          $data The data we need to validate the query string.
     * @param XMLSecurityKey $key  The key we should validate the query against.
     * @throws Exception
     */
    public static function validateSignature(array $data, XMLSecurityKey $key)
    {
        assert('array_key_exists("Query", $data)');
        assert('array_key_exists("SigAlg", $data)');
        assert('array_key_exists("Signature", $data)');

        $query = $data['Query'];
        $sigAlg = $data['SigAlg'];
        $signature = $data['Signature'];

        $signature = base64_decode($signature);

        if ($key->type !== XMLSecurityKey::RSA_SHA1) {
            throw new Exception('Invalid key type for validating signature on query string.');
        }
        if ($key->type !== $sigAlg) {
            $key = Utils::castKey($key, $sigAlg);
        }

        if (!$key->verifySignature($query, $signature)) {
            throw new Exception('Unable to validate signature on query string.');
        }
    }

}
