<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Binding\{HTTPArtifact, HTTPPost, HTTPRedirect, SOAP};
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;

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
            case C::BINDING_HTTP_POST:
            case C::BINDING_HOK_SSO:
                return new HTTPPost();
            case C::BINDING_HTTP_REDIRECT:
                return new HTTPRedirect();
            case C::BINDING_HTTP_ARTIFACT:
                return new HTTPArtifact();
            // ECP ACS is defined with the PAOS binding, but as the IdP, we
            // talk to the ECP using SOAP -- if support for ECP as an SP is
            // implemented, this logic may need to change
            case C::BINDING_PAOS:
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
        $method = $request->getMethod();

        switch ($method) {
            case 'GET':
                $query = $request->getQueryParams();
                if (array_key_exists('SAMLRequest', $query) || array_key_exists('SAMLResponse', $query)) {
                    return new Binding\HTTPRedirect();
                } elseif (array_key_exists('SAMLart', $query)) {
                    return new Binding\HTTPArtifact();
                }
                break;

            case 'POST':
                $contentType = null;
                if ($request->hasHeader('Content-Type')) {
                    $contentType = $request->getHeader('Content-Type')[0];
                    $contentType = explode(';', $contentType);
                    $contentType = $contentType[0]; /* Remove charset. */
                }

                $query = $request->getParsedBody();
                if (array_key_exists('SAMLRequest', $query) || array_key_exists('SAMLResponse', $query)) {
                    return new Binding\HTTPPost();
                } elseif (array_key_exists('SAMLart', $query)) {
                    return new Binding\HTTPArtifact();
                } else {
                    /**
                     * The registration information for text/xml is in all respects the same
                     * as that given for application/xml (RFC 7303 - Section 9.1)
                     */
                    if (
                        ($contentType === 'text/xml' || $contentType === 'application/xml')
                        // See paragraph 3.2.3 of Binding for SAML2 (OASIS)
                        || ($request->hasHeader('SOAPAction')
                            && $request->getHeader('SOAPAction')[0] === 'http://www.oasis-open.org/committees/security')
                    ) {
                        return new Binding\SOAP();
                    }
                }
                break;
        }

        $logger = Utils::getContainer()->getLogger();
        $logger->warning('Unable to find the SAML 2 binding used for this request.');
        $logger->warning('Request method: ' . var_export($method, true));

        if (!empty($query)) {
            $logger->warning(
                $method . " parameters: '" . implode("', '", array_map('addslashes', array_keys($query))) . "'",
            );
        }

        if ($request->hasHeader('Content-Type')) {
            $logger->warning('Content-Type: ' . var_export($request->getHeader('Content-Type')[0], true));
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
    public function setDestination(?string $destination = null): void
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    abstract public function send(AbstractMessage $message): ResponseInterface;


    /**
     * Receive a SAML 2 message.
     *
     * This function will extract the message from the current request.
     * An exception will be thrown if we are unable to process the message.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The received message.
     */
    abstract public function receive(ServerRequestInterface $request): AbstractMessage;
}
