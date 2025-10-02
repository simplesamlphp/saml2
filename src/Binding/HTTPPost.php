<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Binding;

use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Binding;
use SimpleSAML\SAML2\Binding\RelayStateTrait;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\XML\DOMDocumentFactory;

use function array_key_exists;
use function base64_decode;
use function base64_encode;

/**
 * Class which implements the HTTP-POST binding.
 *
 * @package simplesamlphp/saml2
 */
class HTTPPost extends Binding implements AsynchronousBindingInterface, RelayStateInterface
{
    use RelayStateTrait;


    /**
     * Send a SAML 2 message using the HTTP-POST binding.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message we should send.
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    public function send(AbstractMessage $message): ResponseInterface
    {
        if ($this->destination === null) {
            $destination = $message->getDestination()?->getValue();
            if ($destination === null) {
                throw new Exception('Cannot send message, no destination set.');
            }
        } else {
            $destination = $this->destination;
        }
        $relayState = $this->getRelayState();

        $msgStr = $message->toXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        $msgStr = $msgStr->ownerDocument?->saveXML($msgStr);

        $msgStr = base64_encode($msgStr);

        if ($message instanceof AbstractRequest) {
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
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The received message.
     * @throws \Exception
     */
    public function receive(ServerRequestInterface $request): AbstractMessage
    {
        $query = $request->getParsedBody();
        if (array_key_exists('SAMLRequest', $query)) {
            $msgStr = $query['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $query)) {
            $msgStr = $query['SAMLResponse'];
        } else {
            throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
        }

        $msgStr = base64_decode($msgStr, true);
        $msgStr = DOMDocumentFactory::fromString($msgStr)->saveXML();

        $document = DOMDocumentFactory::fromString($msgStr);
        Utils::getContainer()->debugMessage($document->documentElement, 'in');

        $msg = MessageFactory::fromXML($document->documentElement);

        /**
         * 3.5.5.2 - SAML Bindings
         *
         * If the message is signed, the Destination XML attribute in the root SAML element of the protocol
         * message MUST contain the URL to which the sender has instructed the user agent to deliver the
         * message.
         */
        if ($msg->isSigned()) {
            Assert::notNull($msg->getDestination(), ProtocolViolationException::class);
            // Validation of the Destination must be done upstream
        }

        if (array_key_exists('RelayState', $query)) {
            $this->setRelayState($query['RelayState']);
        }

        return $msg;
    }
}
