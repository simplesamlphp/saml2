<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use Dom;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\aslo\SupportsAsynchronousTrait;
use SimpleSAML\XMLSchema\Type\BooleanValue;

/**
 * SingleLogoutService element of type EndpointType
 *
 * @package simplesamlphp/saml2
 */
final class SingleLogoutService extends AbstractEndpointType
{
    use SupportsAsynchronousTrait;


    /**
     * SingleLogoutService constructor.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $responseLocation
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue $supportsAsynchronous
     * @param \SimpleSAML\XML\ElementInterface[] $children
     * @param array<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        SAMLAnyURIValue $binding,
        SAMLAnyURIValue $location,
        ?SAMLAnyURIValue $responseLocation = null,
        protected ?BooleanValue $supportsAsynchronous = null,
        array $children = [],
        array $attributes = [],
    ) {
        $this->setSupportsAsynchronous($supportsAsynchronous);

        parent::__construct($binding, $location, $responseLocation, $children, $attributes);
    }


    /**
     * Initialize a SingleLogoutService.
     *
     * @param \Dom\Element $xml The XML element we should load.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(Dom\Element $xml): static
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.',
            InvalidDOMElementException::class,
        );

        return new static(
            self::getAttribute($xml, 'Binding', SAMLAnyURIValue::class),
            self::getAttribute($xml, 'Location', SAMLAnyURIValue::class),
            self::getOptionalAttribute($xml, 'ResponseLocation', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'supportsAsynchronous', BooleanValue::class, null),
            self::getChildElementsFromXML($xml),
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Add this endpoint to an XML element.
     *
     * @param \Dom\Element $parent The element we should append this endpoint to.
     */
    public function toXML(?Dom\Element $parent = null): Dom\Element
    {
        $e = parent::toXML($parent);

        if ($this->getSupportsAsynchronous() !== null) {
            $e->setAttribute('supportsAsynchronous', $this->getSupportsAsynchronous()->getValue());
        }

        return $e;
    }
}
