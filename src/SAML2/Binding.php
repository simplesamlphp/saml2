<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException;

use function array_key_exists;
use function array_keys;
use function array_map;
use function explode;
use function implode;
use function var_export;

/**
 * Base class for SAML 2 bindings.
 *
 * @package simplesamlphp/saml2
 */
abstract class Binding
{
    /**
     * The destination of messages.
     *
     * This can be null, in which case the destination in the message is used.
     * @var string|null
     */
    protected ?string $destination = null;


    /**
     * Retrieve a binding with the given URN.
     *
     * Will throw an exception if it is unable to locate the binding.
     *
     * @param string $urn The URN of the binding.
     * @throws \SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException
     * @return \SimpleSAML\SAML2\Binding The binding.
     */
    public static function getBinding(string $urn): Binding
    {
        switch ($urn) {
            case Constants::BINDING_HTTP_POST:
            case Constants::BINDING_HOK_SSO:
                return new HTTPPost();
            case Constants::BINDING_HTTP_REDIRECT:
                return new HTTPRedirect();
            case Constants::BINDING_HTTP_ARTIFACT:
                return new HTTPArtifact();
            // ECP ACS is defined with the PAOS binding, but as the IdP, we
            // talk to the ECP using SOAP -- if support for ECP as an SP is
            // implemented, this logic may need to change
            case Constants::BINDING_PAOS:
                return new SOAP();
            default:
                throw new UnsupportedBindingException('Unsupported binding: ' . var_export($urn, true));
        }
    }


    /**
     * Guess the current binding.
     *
     * This function guesses the current binding and creates an instance
     * of \SimpleSAML\SAML2\Binding matching that binding.
     *
     * An exception will be thrown if it is unable to guess the binding.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @throws \SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException
     * @return \SimpleSAML\SAML2\Binding The binding.
     */
    public static function getCurrentBinding(ServerRequestInterface $request): Binding
    {
        $headers = $request->getHeaders();
        $method = $request->getMethod();

        switch ($method) {
            case 'GET':
                $query = $request->getQueryParams();
                if (array_key_exists('SAMLRequest', $query) || array_key_exists('SAMLResponse', $query)) {
                    return new HTTPRedirect();
                } elseif (array_key_exists('SAMLart', $query)) {
                    return new HTTPArtifact();
                }
                break;

            case 'POST':
                if (isset($headers['CONTENT_TYPE'])) {
                    $contentType = $headers['CONTENT_TYPE'][0];
                    $contentType = explode(';', $contentType);
                    $contentType = $contentType[0]; /* Remove charset. */
                } else {
                    $contentType = null;
                }

                $query = $request->getParsedBody();
                if (array_key_exists('SAMLRequest', $query) || array_key_exists('SAMLResponse', $query)) {
                    return new HTTPPost();
                } elseif (array_key_exists('SAMLart', $query)) {
                    return new HTTPArtifact();
                } elseif ($contentType === 'text/xml' || $contentType === 'application/soap+xml') {
                    return new SOAP();
                }
                break;
        }

        $logger = Utils::getContainer()->getLogger();
        $logger->warning('Unable to find the SAML 2 binding used for this request.');
        $logger->warning('Request method: ' . var_export($method, true));
        if (!empty($query)) {
            $logger->warning($method . " parameters: '" . implode("', '", array_map('addslashes', array_keys($query))) . "'");
        }
        if (isset($headers['CONTENT_TYPE'])) {
            $logger->warning('Content-Type: ' . var_export($headers['CONTENT_TYPE'], true));
        }

        throw new UnsupportedBindingException('Unable to find the SAML 2 binding used for this request.');
    }


    /**
     * Retrieve the destination of a message.
     *
     * @return string|null $destination The destination the message will be delivered to.
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }


    /**
     * Override the destination of a message.
     *
     * Set to null to use the destination set in the message.
     *
     * @param string|null $destination The destination the message should be delivered to.
     */
    public function setDestination(string $destination = null): void
    {
        $this->destination = $destination;
    }


    /**
     * Send a SAML 2 message.
     *
     * This function will send a message using the specified binding.
     * The message will be delivered to the destination set in the message.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage $message The message which should be sent.
     */
    abstract public function send(AbstractMessage $message): void;


    /**
     * Receive a SAML 2 message.
     *
     * This function will extract the message from the current request.
     * An exception will be thrown if we are unable to process the message.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The received message.
     */
    abstract public function receive(): AbstractMessage;
}
