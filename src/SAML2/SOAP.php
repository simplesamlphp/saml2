<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\ecp\Response as ECPResponse;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SAML2\XML\samlp\Response as SAML2_Response;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\SOAP11\XML\env\Body;
use SimpleSAML\SOAP11\XML\env\Envelope;
use SimpleSAML\SOAP11\XML\env\Header;
use SimpleSAML\XML\DOMDocumentFactory;

use function file_get_contents;
use function header;
use function sprintf;

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
        $header = new Header();

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
        }

        $env = new Envelope(
            new Body([$message]),
            $header,
        );
        return $env->toXML()->ownerDocument?->saveXML();
    }


    /**
     * Send a SAML 2 message using the SOAP binding.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message we should send.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(AbstractMessage $message): ResponseInterface
    {
        $xml = $this->getOutputToSend($message);
        Utils::getContainer()->debugMessage($xml, 'out');

        return new Response(200, ['Content-Type' => 'text/xml'], $xml);
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SimpleSAML\SAML2\XML\samlp\AnstractMessage The received message.
     *
     * @throws \Exception If unable to receive the message
     */
    public function receive(/** @scrutinizer ignore-unused */ServerRequestInterface $request): AbstractMessage
    {
        $postText = $this->getInputStream();

        if (empty($postText)) {
            throw new UnsupportedBindingException('Invalid message received at AssertionConsumerService endpoint.');
        }

        $document = DOMDocumentFactory::fromString($postText);
        /** @var \DOMNode $xml */
        $xml = $document->firstChild;
        Utils::getContainer()->debugMessage($document->documentElement, 'in');

        $xpCache = XPath::getXPath($document->documentElement);
        /** @var \DOMElement[] $results */
        $results = XPath::xpQuery($xml, '/soap-env:Envelope/soap-env:Body/*[1]', $xpCache);

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
