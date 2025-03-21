<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\XML\URIElementTrait;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

use function trim;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocation extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use URIElementTrait;


    /**
     * Create a new instance of AdditionalMetadataLocation
     *
     * @param string $namespace
     * @param string $location
     */
    public function __construct(
        protected string $namespace,
        string $location,
    ) {
        SAMLAssert::validURI($namespace);
        $this->setContent($location);
    }


    /**
     * Collect the value of the namespace-property
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }


    /**
     * Initialize an AdditionalMetadataLocation element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AdditionalMetadataLocation', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AdditionalMetadataLocation::NS, InvalidDOMElementException::class);

        $namespace = self::getAttribute($xml, 'namespace');

        return new static($namespace, trim($xml->textContent));
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
        $e->textContent = $this->getContent();
        $e->setAttribute('namespace', $this->getNamespace());

        return $e;
    }
}
