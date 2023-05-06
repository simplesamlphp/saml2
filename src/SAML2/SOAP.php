<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use Exception;
use SAML2\Exception\Protocol\UnsupportedBindingException;
use SAML2\XML\ecp\RequestAuthenticated;
use SAML2\XML\ecp\Response as ECPResponse;
use SimpleSAML\SOAP\Constants as SOAPC;
use SimpleSAML\SOAP11\Utils\XPath;
use SimpleSAML\SOAP11\XML\env\Body;
use SimpleSAML\SOAP11\XML\env\Envelope;
use SimpleSAML\SOAP11\XML\env\Header;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

use function file_get_contents;

/**
 * Class which implements the SOAP binding.
 *
 * @package SimpleSAMLphp
 */
class SOAP extends Binding
{
    /**
     * @param Message $message
     * @throws \Exception
     * @return string|false The XML or false on error
     */
    public function getOutputToSend(Message $message): string|false
    {
        // In the Artifact Resolution profile, this will be an ArtifactResolve
        // containing another message (e.g. a Response), however in the ECP
        // profile, this is the Response itself.
        if ($message instanceof Response) {
            $requestAuthenticated = new RequestAuthenticated(1);

            $destination = $this->destination ?: $message->getDestination();
            if ($destination === null) {
                throw new Exception('No destination available for SOAP message.');
            }
            $response = new ECPResponse($destination);
            $header = new Header([$requestAuthenticated, $response]);
        } else {
            $header = new Header();
        }

        $env = new Envelope(
            new Body([new Chunk($message->toUnsignedXML())]),
            $header,
        );

        $elt = $env->toXML();
        /** @psalm-var \DOMDocument $elt->ownerDocument */
        return $elt->ownerDocument->saveXML();
    }


    /**
     * Send a SAML 2 message using the SOAP binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     * @return void
     */
    public function send(Message $message): void
    {
        header('Content-Type: text/xml', true);

        $xml = $this->getOutputToSend($message);
        if ($xml !== false) {
            Utils::getContainer()->debugMessage($xml, 'out');
            echo $xml;
        }

        // DOMDocument::saveXML() returned false. Something is seriously wrong here. Not much we can do.
        exit(0);
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * @throws \Exception If unable to receive the message
     * @return \SAML2\Message The received message.
     */
    public function receive(): Message
    {
        $postText = $this->getInputStream();

        if (empty($postText)) {
            throw new UnsupportedBindingException('Invalid message received at AssertionConsumerService endpoint.');
        }

        $document = DOMDocumentFactory::fromString($postText);
        /** @var \DOMNode $xml */
        $xml = $document->firstChild;
        Utils::getContainer()->debugMessage($document->documentElement, 'in');

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement[] $results */
        $results = XPath::xpQuery($xml, '/env:Envelope/env:Body/*[1]', $xpCache);

        return Message::fromXML($results[0]);
    }

    /**
     * @return string|false
     */
    protected function getInputStream()
    {
        return file_get_contents('php://input');
    }
}
