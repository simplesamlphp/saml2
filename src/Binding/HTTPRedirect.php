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
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Exception\RuntimeException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function array_key_exists;
use function array_pad;
use function base64_decode;
use function base64_encode;
use function explode;
use function gzdeflate;
use function gzinflate;
use function in_array;
use function sprintf;
use function str_contains;
use function strlen;
use function urldecode;

/**
 * Class which implements the HTTP-Redirect binding.
 *
 * @package simplesamlphp/saml2
 */
class HTTPRedirect extends Binding implements AsynchronousBindingInterface, RelayStateInterface
{
    use RelayStateTrait;


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
            } else {
                $destination = $destination->getValue();
            }
        } else {
            $destination = $this->destination;
        }

        $relayState = $this->getRelayState();
        $msgStr = $message->toXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        /** @var \Dom\XMLDocument $ownerDocument */
        $ownerDocument = $msgStr->ownerDocument;
        $msgStr = $ownerDocument->saveXML($msgStr);

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
            $signatureMethod = $signature->getSignedInfo()->getSignatureMethod();
            $signatureValue = $signature->getSignatureValue();

            $msg .= '&SigAlg=' . urlencode($signatureMethod->getAlgorithm()->getValue());
            $msg .= '&Signature=' . urlencode($signatureValue->getValue()->getValue());
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
            'Redirect to ' . strlen($destination) . ' byte URL: ' . $destination,
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
     *
     * @throws \Exception
     *
     * NPath is currently too high but solving that just moves code around.
     */
    public function receive(ServerRequestInterface $request): AbstractMessage
    {
        $query = $this->getRawQueryParams($request->getUri()->getQuery());

        /**
         * Put the SAMLRequest/SAMLResponse from the actual query string into $message,
         * and assert that the result from parseQuery() in $query and the parsing of the SignedQuery in $res agree
         */
        if (array_key_exists('SAMLRequest', $query)) {
            $message = urldecode($query['SAMLRequest']);
            $signedQuery = 'SAMLRequest=' . $query['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $query)) {
            $message = urldecode($query['SAMLResponse']);
            $signedQuery = 'SAMLResponse=' . $query['SAMLResponse'];
        } else {
            throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
        }

        if (array_key_exists('SAMLRequest', $query) && array_key_exists('SAMLResponse', $query)) {
            throw new Exception('Both SAMLRequest and SAMLResponse provided.');
        }

        if (isset($query['SAMLEncoding']) && urldecode($query['SAMLEncoding']) !== C::BINDING_HTTP_REDIRECT_DEFLATE) {
            throw new Exception(sprintf('Unknown SAMLEncoding: %s', urldecode($query['SAMLEncoding'])));
        }

        $message = base64_decode($message, true);
        if ($message === false) {
            throw new Exception('Error while base64 decoding SAML message.');
        }

        $message = gzinflate($message);
        if ($message === false) {
            throw new Exception('Error while inflating SAML message.');
        }

        $document = DOMDocumentFactory::fromString($message);
        Utils::getContainer()->debugMessage($document->documentElement, 'in');
        $message = MessageFactory::fromXML($document->documentElement);

        if (array_key_exists('RelayState', $query)) {
            $this->setRelayState(urldecode($query['RelayState']));
            $signedQuery .= '&RelayState=' . $query['RelayState'];
        }

        if (!array_key_exists('Signature', $query)) {
            return $message;
        }

        /**
         * 3.4.5.2 - SAML Bindings
         *
         * If the message is signed, the Destination XML attribute in the root SAML element of the protocol
         * message MUST contain the URL to which the sender has instructed the user agent to deliver the
         * message.
         */
        Assert::notNull($message->getDestination(), ProtocolViolationException::class);
        // Validation of the Destination must be done upstream

        if (!array_key_exists('SigAlg', $query)) {
            throw new Exception('Missing signature algorithm.');
        } else {
            $signedQuery .= '&SigAlg=' . $query['SigAlg'];
        }

        $container = ContainerSingleton::getInstance();
        $blacklist = $container->getBlacklistedEncryptionAlgorithms();
        $verifier = (new SignatureAlgorithmFactory($blacklist))->getAlgorithm(
            urldecode($query['SigAlg']),
            // TODO:  Need to use the key from the metadata
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        if ($verifier->verify($signedQuery, base64_decode(urldecode($query['Signature']))) === false) {
            throw new SignatureVerificationFailedException('Failed to verify signature.');
        }

        return $message;
    }


    private function getRawQueryParams(string $query): array
    {
        if ($query === '') {
            return [];
        }

        $params = [];
        $allowed = ['SAMLRequest', 'SAMLResponse', 'SAMLEncoding', 'Signature', 'SigAlg', 'RelayState'];

        foreach (explode('&', $query) as $pair) {
            if ($pair === '') {
                continue;
            }

            [$rawKey, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $key = urldecode($rawKey);   // ← decode the name

            if (array_key_exists($key, $params)) {
                throw new RuntimeException('Duplicate parameter.');
            }

            if (in_array($key, $allowed, true)) {
                $params[$key] = $value;  // keep value raw (still encoded)
            }
        }

        return $params;
    }
}
