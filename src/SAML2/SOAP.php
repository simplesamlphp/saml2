<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use Exception;
use SimpleSAML\SAML2\XML\ecp\Response as ECPResponse;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SAML2\XML\samlp\Response;

/**
 * Class which implements the SOAP binding.
 *
 * @package SimpleSAMLphp
 */
class SOAP extends Binding
{
    /**
     * @param \SAML2\XML\samlp\AbstractMessage $message
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

            $destination = $this->destination ?: $message->getDestination();
            if ($destination === null) {
                throw new Exception('No destination available for SOAP message.');
            }
            $response = new ECPResponse($destination);

            $response->toXML($header);

            // TODO We SHOULD add ecp:RequestAuthenticated SOAP header if we
            // authenticated the AuthnRequest. It may make sense to have a
            // standardized way for Message objects to contain (optional) SOAP
            // headers for use with the SOAP binding.
            //
            // https://docs.oasis-open.org/security/saml/Post2.0/saml-ecp/v2.0/cs01/saml-ecp-v2.0-cs01.html#_Toc366664733
            // See Section 2.3.6.1
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
     * @param \SAML2\XML\samlp\AbstractMessage $message The message we should send.
     * @return void
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
     * @return \SAML2\XML\samlp\AbstractMessage The received message.
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
        $results = Utils::xpQuery($xml, '/soap-env:Envelope/soap-env:Body/*[1]');

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
