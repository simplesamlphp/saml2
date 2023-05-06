<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SAML2\Exception\Protocol\UnsupportedBindingException;
use SAML2\Response as SAML2_Response;
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
        if ($message instanceof SAML2_Response) {
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
     * @param \SAML2\Message $message The message we should send.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(Message $message): ResponseInterface
    {
        $xml = $this->getOutputToSend($message);
        Utils::getContainer()->debugMessage($xml, 'out');

        return new Response(200, ['Content-Type' => 'text/xml'], $xml);
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SAML2\Message The received message.
     *
     * @throws \Exception If unable to receive the message
     */
    public function receive(ServerRequestInterface $request): Message
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
