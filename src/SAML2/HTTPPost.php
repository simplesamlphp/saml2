<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use DOMElement;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function array_key_exists;
use function base64_decode;
use function base64_encode;

/**
 * Class which implements the HTTP-POST binding.
 *
 * @package SimpleSAMLphp
 */
class HTTPPost extends Binding
{
    /**
     * Send a SAML 2 message using the HTTP-POST binding.
     *
     * @param \SimpleSAML\SAML2\Message $message The message we should send.
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    public function send(Message $message): ResponseInterface
    {
        if ($this->destination === null) {
            $destination = $message->getDestination();
            if ($destination === null) {
                throw new MissingAttributeException('Cannot send message, no destination set.');
            }
        } else {
            $destination = $this->destination;
        }
        $relayState = $message->getRelayState();

        $msgStr = $message->toSignedXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);

        $msgStr = base64_encode($msgStr);

        if ($message instanceof Request) {
            $msgType = 'SAMLRequest';
        } else {
            $msgType = 'SAMLResponse';
        }

        $post = [];
        $post[$msgType] = $msgStr;

        if ($relayState !== null) {
            $post['RelayState'] = $relayState;
        }

        $container = Utils::getContainer();
        return new Response(303, ['Location' => $container->getPOSTRedirectURL($destination, $post)]);
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-POST binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SimpleSAML\SAML2\Message The received message.
     * @throws \Exception
     */
    public function receive(ServerRequestInterface $request): Message
    {
        $query = $request->getParsedBody();
        if (array_key_exists('SAMLRequest', $query)) {
            $msgStr = $query['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $query)) {
            $msgStr = $query['SAMLResponse'];
        } else {
            throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
        }

        $msgStr = base64_decode($msgStr);

        $xml = new DOMDocument();
        $xml->loadXML($msgStr);
        $msgStr = $xml->saveXML();

        $document = DOMDocumentFactory::fromString($msgStr);
        Utils::getContainer()->debugMessage($document->documentElement, 'in');
        if (!$document->firstChild instanceof DOMElement) {
            throw new Exception('Malformed SAML message received.');
        }

        $msg = Message::fromXML($document->firstChild);

        /**
         * 3.5.5.2 - SAML Bindings
         *
         * If the message is signed, the Destination XML attribute in the root SAML element of the protocol
         * message MUST contain the URL to which the sender has instructed the user agent to deliver the
         * message.
         */
        if ($msg->isMessageConstructedWithSignature()) {
            Assert::notNull($msg->getDestination()); // Validation of the value must be done upstream
        }

        if (array_key_exists('RelayState', $query)) {
            $msg->setRelayState($query['RelayState']);
        }

        return $msg;
    }
}
