<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use Exception;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ecp\Response as ECPResponse;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class which implements the SOAP binding.
 *
 * @package simplesamlphp/saml2
 */
class SOAP extends Binding
{
    /**
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message
     * @throws \Exception
     * @return string|false The XML or false on error
     */
    public function getOutputToSend(AbstractMessage $message)
    {
        $envelope = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="%s">
    <SOAP-ENV:Header />
    <SOAP-ENV:Body />
</SOAP-ENV:Envelope>
SOAP;
        $envelope = sprintf($envelope, Constants::NS_SOAP);

        $doc = new DOMDocument();
        $doc->loadXML($envelope);

        // In the Artifact Resolution profile, this will be an ArtifactResolve
        // containing another message (e.g. a Response), however in the ECP
        // profile, this is the Response itself.
        if ($message instanceof Response) {
            /** @var \DOMElement $header */
            $header = $doc->getElementsByTagNameNS(Constants::NS_SOAP, 'Header')->item(0);

            $requestAuthenticated = new RequestAuthenticated();
            $header->appendChild($header->ownerDocument->importNode($requestAuthenticated->toXML(), true));

            $destination = $this->destination ?: $message->getDestination();
            if ($destination === null) {
                throw new Exception('No destination available for SOAP message.');
            }
            $response = new ECPResponse($destination);
            $response->toXML($header);
        }

        /** @var \DOMElement $body */
        $body = $doc->getElementsByTagNameNs(Constants::NS_SOAP, 'Body')->item(0);

        $body->appendChild($doc->importNode($message->toXML(), true));

        return $doc->saveXML();
    }


    /**
     * Send a SAML 2 message using the SOAP binding.
     *
     * Note: This function never returns.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message we should send.
     */
    public function send(AbstractMessage $message): void
    {
        header('Content-Type: text/xml', true);

        $xml = $this->getOutputToSend($message);
        if ($xml !== false) {
            Utils::getContainer()->debugMessage($xml, 'out');
            echo $xml;
        }

        // DOMDocument::saveXML() returned false. Something is seriously wrong here. Not much we can do.
        throw new Exception('Error while generating XML for SAML message.');
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * @throws \Exception If unable to receive the message
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The received message.
     */
    public function receive(): AbstractMessage
    {
        $postText = $this->getInputStream();

        if ($postText === false) {
            throw new Exception('Invalid message received to AssertionConsumerService endpoint.');
        }

        $document = DOMDocumentFactory::fromString($postText);
        /** @var \DOMNode $xml */
        $xml = $document->firstChild;
        Utils::getContainer()->debugMessage($document->documentElement, 'in');
        /** @var \DOMElement[] $results */
        $results = XMLUtils::xpQuery($xml, '/soap-env:Envelope/soap-env:Body/*[1]');

        return MessageFactory::fromXML($results[0]);
    }

    /**
     * @return string|false
     */
    protected function getInputStream()
    {
        return file_get_contents('php://input');
    }
}
