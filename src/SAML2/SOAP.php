<?php

namespace SAML2;

/**
 * Class which implements the SOAP binding.
 *
 * @package SimpleSAMLphp
 */
class SOAP extends Binding
{
    public function getOutputToSend(Message $message)
    {
        $outputFromIdp = '<?xml version="1.0" encoding="UTF-8"?>';
        $outputFromIdp .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
        $outputFromIdp .= '<SOAP-ENV:Body>';
        $xmlMessage = $message->toSignedXML();
        Utils::getContainer()->debugMessage($xmlMessage, 'out');
        $tempOutputFromIdp = $xmlMessage->ownerDocument->saveXML($xmlMessage);
        $outputFromIdp .= $tempOutputFromIdp;
        $outputFromIdp .= '</SOAP-ENV:Body>';
        $outputFromIdp .= '</SOAP-ENV:Envelope>';

        return $outputFromIdp;
    }

    /**
     * Send a SAML 2 message using the SOAP binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     */
    public function send(Message $message)
    {
        header('Content-Type: text/xml', true);
        print($this->getOutputToSend($message));
        exit(0);
    }

    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @return \SAML2\Message The received message.
     * @throws \Exception
     */
    public function receive()
    {
        $postText = $this->getInputStream();

        if (empty($postText)) {
            throw new \Exception('Invalid message received to AssertionConsumerService endpoint.');
        }

        $document = DOMDocumentFactory::fromString($postText);
        $xml = $document->firstChild;
        Utils::getContainer()->debugMessage($xml, 'in');
        $results = Utils::xpQuery($xml, '/soap-env:Envelope/soap-env:Body/*[1]');

        return Message::fromXML($results[0]);
    }

    protected function getInputStream()
    {
        return file_get_contents('php://input');
    }
}
