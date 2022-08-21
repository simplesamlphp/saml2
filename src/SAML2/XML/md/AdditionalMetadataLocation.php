<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\XMLURIElementTrait;

use function trim;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocation extends AbstractMdElement
{
    use XMLURIElementTrait;

    /**
     * The namespace of this metadata.
     *
     * @var string
     */
    protected string $namespace;


    /**
     * Create a new instance of AdditionalMetadataLocation
     *
     * @param string $namespace
     * @param string $location
     */
    public function __construct(string $namespace, string $location)
    {
        $this->setNamespace($namespace);
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
     * Set the value of the namespace-property
     *
     * @param string $namespace
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setNamespace(string $namespace): void
    {
        Assert::validURI($namespace, SchemaViolationException::class); // Covers the empty string
        $this->namespace = $namespace;
    }


    /**
     * Initialize an AdditionalMetadataLocation element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AdditionalMetadataLocation', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AdditionalMetadataLocation::NS, InvalidDOMElementException::class);

        $namespace = self::getAttribute($xml, 'namespace');

        return new self($namespace, trim($xml->textContent));
    }


    /**
     * Convert this AdditionalMetadataLocation to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement This AdditionalMetadataLocation-element.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->content;
        $e->setAttribute('namespace', $this->namespace);

        return $e;
    }
}
