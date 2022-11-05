<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function var_export;

/**
 * Factory class for all SAML 2 messages.
 *
 * @package simplesamlphp/saml2
 */
abstract class MessageFactory
{
    /**
     * Convert an XML element into a message.
     *
     * @param \DOMElement $xml The root XML element
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage The message
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): AbstractMessage
    {
        Assert::same(
            $xml->namespaceURI,
            C::NS_SAMLP,
            'Unknown namespace of SAML message: ' . var_export($xml->namespaceURI, true),
            InvalidDOMElementException::class
        );

        switch ($xml->localName) {
            case 'AttributeQuery':
                return AttributeQuery::fromXML($xml);
            case 'AuthnRequest':
                return AuthnRequest::fromXML($xml);
            case 'LogoutResponse':
                return LogoutResponse::fromXML($xml);
            case 'LogoutRequest':
                return LogoutRequest::fromXML($xml);
            case 'Response':
                return Response::fromXML($xml);
            case 'ArtifactResponse':
                return ArtifactResponse::fromXML($xml);
            case 'ArtifactResolve':
                return ArtifactResolve::fromXML($xml);
            default:
                throw new InvalidDOMElementException('Unknown SAML message: ' . var_export($xml->localName, true));
        }
    }
}
