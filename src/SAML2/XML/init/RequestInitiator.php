<?php

declare(strict_types=1);

namespace SAML2\XML\init;

use DOMElement;
use SAML2\XML\md\AbstractEndpointType;
use Webmozart\Assert\Assert;

/**
 * Class for handling the init:RequestInitiator element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-request-initiation-cs-01.pdf
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
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
     * @param string      $location
     * @param string|null $responseLocation
     * @param array       $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $location,
        ?string $responseLocation = null,
        array $attributes = []
    ) {
        parent::__construct(self::NS, $location, $responseLocation, $attributes);
    }


    /**
     * Initialize an EndpointType.
     *
     * Note: this method cannot be used when extending this class, if the constructor has a different signature.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.'
        );

        Assert::eq(
            /** @var string $binding */
            self::getAttribute($xml, 'Binding'),
            self::NS,
            "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'."
        );

        /** @var string $location */
        $location = self::getAttribute($xml, 'Location');

        return new static(
            $location,
            self::getAttribute($xml, 'ResponseLocation', null),
            self::getAttributesNSFromXML($xml)
        );
    }
}
