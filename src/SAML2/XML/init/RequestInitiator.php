<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\init;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AbstractEndpointType;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class for handling the init:RequestInitiator element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-request-initiation-cs-01.pdf
 *
 * @package simplesamlphp/saml2
 */
final class RequestInitiator extends AbstractEndpointType
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:profiles:SSO:request-init';

    /** @var string */
    public const NS_PREFIX = 'init';


    /**
     * EndpointType constructor.
     *
     * @param string $location
     * @param string|null $responseLocation
     * @param array $children
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $location,
        ?string $responseLocation = null,
        array $children = [],
        array $attributes = [],
    ) {
        parent::__construct(self::NS, $location, $responseLocation, $children, $attributes);
    }


    /**
     * Initialize an RequestInitiator.
     *
     * Note: this method cannot be used when extending this class, if the constructor has a different signature.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.',
            InvalidDOMElementException::class,
        );

        Assert::eq(
            self::getAttribute($xml, 'Binding'),
            self::NS,
            "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'.",
            ProtocolViolationException::class,
        );

        $children = [];
        foreach ($xml->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            } elseif ($child->namespaceURI !== C::NS_MD) {
                $children[] = new Chunk($child);
            } // else continue
        }

        return new static(
            self::getAttribute($xml, 'Location'),
            self::getOptionalAttribute($xml, 'ResponseLocation', null),
            $children,
            self::getAttributesNSFromXML($xml),
        );
    }
}
