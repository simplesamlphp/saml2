<?php

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use Webmozart\Assert\Assert;

class MessageFactory
{
    /**
     * Convert an XML element into a message.
     *
     * @param \DOMElement $xml The root XML element
     * @throws \Exception
     * @return \SAML2\XML\samlp\AbstractMessage The message
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same(
            $xml->namespaceURI,
            Constants::NS_SAMLP,
            'Unknown namespace of SAML message: ' . var_export($xml->namespaceURI, true)
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
                throw new \Exception('Unknown SAML message: ' . var_export($xml->localName, true));
        }
    }
}
