<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Utils\Security;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

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
use function urlencode;
use function urldecode;
use function var_export;

/**
 * Class which implements the HTTP-Redirect binding.
 *
 * @package simplesamlphp/saml2
 */
class HTTPRedirect extends Binding
{
    public const DEFLATE = 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE';

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

        $key = $message->getSigningKey();

        $msgStr = $message->toXML();

        Utils::getContainer()->debugMessage($msgStr, 'out');
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);

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
     * Note: This function never returns.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message we should send.
     */
    public function send(AbstractMessage $message): void
    {
        $destination = $this->getRedirectURL($message);
        Utils::getContainer()->getLogger()->debug('Redirect to ' . strlen($destination) . ' byte URL: ' . $destination);
        Utils::getContainer()->redirect($destination);
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
        $message = MessageFactory::fromXML($document->documentElement);

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
        $signedQuery .= '&SigAlg=' . urlencode($query['SigAlg']);

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
     * @param array $data The data we need to validate the query string.
     * @param \SimpleSAML\XMLSecurity\XMLSecurityKey $key  The key we should validate the query against.
     *
     * @throws \Exception
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public static function validateSignature(array $data, XMLSecurityKey $key): void
    {
        Assert::keyExists($data, "Query");
        Assert::keyExists($data, "SigAlg");
        Assert::keyExists($data, "Signature");
        Assert::same($key->type, XMLSecurityKey::RSA_SHA256, 'Invalid key type for validating signature on query string.');

        $query = $data['Query'];
        $sigAlg = $data['SigAlg'];
        $signature = $data['Signature'];

        $signature = base64_decode($signature);

        if ($key->type !== $sigAlg) {
            $key = Security::castKey($key, $sigAlg);
        }

        if ($key->verifySignature($query, $signature) !== 1) {
            throw new Exception('Unable to validate signature on query string.');
        }
    }
}
