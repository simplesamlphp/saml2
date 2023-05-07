<?php

declare(strict_types=1);

namespace SAML2;

use DOMElement;
use Exception;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;

use function array_key_exists;
use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function get_class;
use function gzdeflate;
use function gzinflate;
use function strlen;
use function strpos;
use function urldecode;
use function urlencode;
use function var_export;

/**
 * Class which implements the HTTP-Redirect binding.
 *
 * @package SimpleSAMLphp
 */
class HTTPRedirect extends Binding
{
    public const DEFLATE = 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE';

    /**
     * Create the redirect URL for a message.
     *
     * @param \SAML2\Message $message The message.
     * @return string The URL the user should be redirected to in order to send a message.
     */
    public function getRedirectURL(Message $message): string
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

        $key = $message->getSignatureKey();

        $msgStr = $message->toUnsignedXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);

        $msgStr = gzdeflate($msgStr);
        $msgStr = base64_encode($msgStr);

        /* Build the query string. */

        if ($message instanceof Request) {
            $msg = 'SAMLRequest=';
        } else {
            $msg = 'SAMLResponse=';
        }
        $msg .= urlencode($msgStr);

        if ($relayState !== null) {
            $msg .= '&RelayState=' . urlencode($relayState);
        }

        if ($key !== null) { // add the signature
            /** @psalm-suppress PossiblyInvalidArgument */
            $msg .= '&SigAlg=' . urlencode($key->type);

            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }

        if (strpos($destination, '?') === false) {
            $destination .= '?' . $msg;
        } else {
            $destination .= '&' . $msg;
        }

        return $destination;
    }


    /**
     * Send a SAML 2 message using the HTTP-Redirect binding.
     *
     * @param \SAML2\Message $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(Message $message): ResponseInterface
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
     * @return \SAML2\Message The received message.
     * @throws \Exception
     *
     * NPath is currently too high but solving that just moves code around.
     */
    public function receive(ServerRequestInterface $request): Message
    {
        $query = $request->getQueryParams();
        if (array_key_exists('SAMLRequest', $query)) {
            $message = $query['SAMLRequest'];
            $signedQuery = 'SAMLRequest=' . urlencode($query['SAMLRequest']);
        } elseif (array_key_exists('SAMLResponse', $query)) {
            $message = $query['SAMLResponse'];
            $signedQuery = 'SAMLResponse=' . urlencode($query['SAMLResponse']);
        } else {
            throw new Exception('Missing SAMLRequest or SAMLResponse parameter.');
        }

        if (isset($query['SAMLEncoding']) && $query['SAMLEncoding'] !== self::DEFLATE) {
            throw new Exception('Unknown SAMLEncoding: ' . var_export($query['SAMLEncoding'], true));
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
        if (!$document->firstChild instanceof DOMElement) {
            throw new Exception('Malformed SAML message received.');
        }
        $message = Message::fromXML($document->firstChild);

        if (array_key_exists('RelayState', $query)) {
            $message->setRelayState($query['RelayState']);
            $signedQuery .= '&RelayState=' . urlencode($query['RelayState']);
        }

        if (!array_key_exists('Signature', $query)) {
            return $message;
        }

        if (!array_key_exists('SigAlg', $query)) {
            throw new Exception('Missing signature algorithm.');
        }

        $signData = [
            'Signature' => $query['Signature'],
            'SigAlg'    => $query['SigAlg'],
            'Query'     => $signedQuery,
        ];

        $message->addValidator([get_class($this), 'validateSignature'], $signData);

        return $message;
    }


    /**
     * Validate the signature on a HTTP-Redirect message.
     *
     * Throws an exception if we are unable to validate the signature.
     *
     * @param array          $data The data we need to validate the query string.
     * @param XMLSecurityKey $key  The key we should validate the query against.
     * @throws \Exception
     * @return void
     */
    public static function validateSignature(array $data, XMLSecurityKey $key): void
    {
        Assert::keyExists($data, "Query");
        Assert::keyExists($data, "SigAlg");
        Assert::keyExists($data, "Signature");

        $query = $data['Query'];
        $sigAlg = $data['SigAlg'];
        $signature = $data['Signature'];

        $signature = base64_decode($signature);

        if ($key->type !== XMLSecurityKey::RSA_SHA256) {
            throw new Exception('Invalid key type for validating signature on query string.');
        }
        if ($key->type !== $sigAlg) {
            $key = Utils::castKey($key, $sigAlg);
        }

        if ($key->verifySignature($query, $signature) !== 1) {
            throw new SignatureVerificationFailedException('Unable to validate signature on query string.');
        }
    }
}
