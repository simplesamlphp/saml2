<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use DOMElement;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
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
class HTTPPost extends Binding
{
    /**
     * Send a SAML 2 message using the HTTP-POST binding.
     *
     * Note: This function never returns.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message we should send.
     */
    public function send(AbstractMessage $message): void
    {
        if ($this->destination === null) {
            $destination = $message->getDestination();
            if ($destination === null) {
                throw new Exception('Cannot send message, no destination set.');
            }
        } else {
            $destination = $this->destination;
        }
        $relayState = $message->getRelayState();

        $msgStr = $message->toXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);

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

        Utils::getContainer()->postRedirect($destination, $post);
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

        $msgStr = base64_decode($msgStr);

        $xml = new DOMDocument();
        $xml->loadXML($msgStr);
        $msgStr = $xml->saveXML();

        $document = DOMDocumentFactory::fromString($msgStr);
        Utils::getContainer()->debugMessage($document->documentElement, 'in');

        $msg = MessageFactory::fromXML($document->documentElement);

        if (array_key_exists('RelayState', $query)) {
            $msg->setRelayState($query['RelayState']);
        }

        return $msg;
    }
}
