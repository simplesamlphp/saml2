<?php

/**
 * Class which implements the SOAP binding.
 *
 * @package SimpleSAMLphp
 */
class SAML2_SOAP extends SAML2_Binding
{

    /**
     * Send a SAML 2 message using the SOAP binding.
     *
     * Note: This function never returns.
     *
     * @param SAML2_Message $message The message we should send.
     */
    public function send(SAML2_Message $message)
    {
        
        $envelope = '<soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/">'.
                    '<soap-env:Header/><soap-env:Body /></soap-env:Envelope>';

        $doc = new DOMDocument();
        $doc->loadXML($envelope);
        $soapHeader = $doc->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Header');
        $soapBody = $doc->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body');

        if ($message->toSignedXML()->localName === 'Response') {
            $response = new SAML2_XML_ecp_Response();
            $response->AssertionConsumerServiceURL = $this->getDestination();
            $response->toXML($soapHeader->item(0));
        }

        $soapBody->item(0)->appendChild($doc->importNode($message->toSignedXML(), true));
        
        print($doc->saveXML());
        exit(0);
    }

    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @return SAML2_Message The received message.
     * @throws Exception
     */
    public function receive()
    {
        $postText = file_get_contents('php://input');

        if (empty($postText)) {
            throw new Exception('Invalid message received to AssertionConsumerService endpoint.');
        }

        $document = SAML2_DOMDocumentFactory::fromString($postText);
        $xml = $document->firstChild;
        SAML2_Utils::getContainer()->debugMessage($xml, 'in');
        $results = SAML2_Utils::xpQuery($xml, '/soap-env:Envelope/soap-env:Body/*[1]');
        $message = SAML2_Message::fromXML($results[0]);
        $this->setDestination($message->getAssertionConsumerServiceURL());

        return $message;
    }
}
