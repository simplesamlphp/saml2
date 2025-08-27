<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocation extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create a new instance of AdditionalMetadataLocation
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $namespace
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     */
    public function __construct(
        protected SAMLAnyURIValue $namespace,
        protected SAMLAnyURIValue $location,
    ) {
    }


    /**
     * Collect the value of the namespace-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getNamespace(): SAMLAnyURIValue
    {
        return $this->namespace;
    }


    /**
     * Collect the value of the location-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getLocation(): SAMLAnyURIValue
    {
        return $this->location;
    }


    /**
     * Initialize an AdditionalMetadataLocation element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AdditionalMetadataLocation', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AdditionalMetadataLocation::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'namespace', SAMLAnyURIValue::class),
            SAMLAnyURIValue::fromString($xml->textContent),
        );
    }


    /**
     * Convert this AdditionalMetadataLocation to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement This AdditionalMetadataLocation-element.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->getLocation()->getValue();
        $e->setAttribute('namespace', $this->getNamespace()->getValue());

        return $e;
    }
}
