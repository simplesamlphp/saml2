<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Utils\Security;

use function array_key_exists;
use function base64_decode;
use function base64_encode;
use function gzdeflate;
use function gzinflate;
use function sprintf;
use function strlen;
use function str_contains;
use function urlencode;

/**
 * Class which implements the HTTP-Redirect binding.
 *
 * @package simplesamlphp/saml2
 */
class HTTPRedirect extends Binding
{
    /**
     * Create the redirect URL for a message.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message.
     * @return string The URL the user should be redirected to in order to send a message.
     */
    public function getRedirectURL(AbstractMessage $message): string
    {
        if ($this->destination === null) {
            $destination = $message->getDestination();
            if ($destination === null) {
                throw new Exception('Cannot build a redirect URL, no destination set.');
            }
        } else {
            $destination = $this->destination;
        }

        $relayState = $message->getRelayState();
        $msgStr = $message->toXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        $msgStr = $msgStr->ownerDocument?->saveXML($msgStr);

        $msgStr = gzdeflate($msgStr);
        $msgStr = base64_encode($msgStr);

        /* Build the query string. */

        if ($message instanceof AbstractRequest) {
            $msg = 'SAMLRequest=';
        } else {
            $msg = 'SAMLResponse=';
        }
        $msg .= urlencode($msgStr);

        if ($relayState !== null) {
            $msg .= '&RelayState=' . urlencode($relayState);
        }

        $signature = $message->getSignature();
        if ($signature !== null) { // add the signature
            $msg .= '&SigAlg=' . urlencode($signature->getSignedInfo()->getSignatureMethod()->getAlgorithm());
            $msg .= '&Signature=' . urlencode($signature->getSignatureValue()->getContent());
        }

        if (str_contains($destination, '?')) {
            $destination .= '&' . $msg;
        } else {
            $destination .= '?' . $msg;
        }

        return $destination;
    }


    /**
     * Send a SAML 2 message using the HTTP-Redirect binding.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(AbstractMessage $message): ResponseInterface
    {
        $destination = $this->getRedirectURL($message);
        Utils::getContainer()->getLogger()->debug(
            'Redirect to ' . strlen($destination) . ' byte URL: ' . $destination
        );
        return new Response(303, ['Location' => $destination]);
    }


    /**
     * Receive a SAML 2 message sent using the HTTP-Redirect binding.
     *
     * Throws an exception if it is unable receive the message.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The received message.
     * @throws \Exception
     *
     * NPath is currently too high but solving that just moves code around.
     */
    public function receive(ServerRequestInterface $request): AbstractMessage
    {
        $query = $request->getQueryParams();

        if (array_key_exists('SAMLRequest', $query)) {
            $message = $query['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $query)) {
            $message = $query['SAMLResponse'];
        } else {
            throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
        }

        if (isset($query['SAMLEncoding']) && $query['SAMLEncoding'] !== C::BINDING_HTTP_REDIRECT_DEFLATE) {
            throw new Exception(sprintf('Unknown SAMLEncoding: %s', $query['SAMLEncoding']));
        }

        $message = base64_decode($message);
        if ($message === false) {
            throw new Exception('Error while base64 decoding SAML message.');
        }

        $message = gzinflate($message);
        if ($message === false) {
            throw new Exception('Error while inflating SAML message.');
        }

        $document = DOMDocumentFactory::fromString($message);
        Utils::getContainer()->debugMessage($document->documentElement, 'in');

        $msg = MessageFactory::fromXML($document->documentElement);
        if (array_key_exists('RelayState', $query)) {
            $msg->setRelayState($query['RelayState']);
        }
        return $msg;
    }
}
