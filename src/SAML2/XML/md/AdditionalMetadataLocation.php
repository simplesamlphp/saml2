<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\XMLStringElementTrait;

use function trim;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package simplesamlphp/saml2
 */
final class AdditionalMetadataLocation extends AbstractMdElement
{
    use XMLStringElementTrait;

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
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(/** @scrutinizer ignore-unused */ string $content): void
    {
        Assert::notEmpty($content);
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
        Assert::notEmpty($namespace, 'The namespace in AdditionalMetadataLocation must be a URI.');
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
        $e->textContent = $this->getContent();
        $e->setAttribute('namespace', $this->namespace);
        return $e;
    }
}
