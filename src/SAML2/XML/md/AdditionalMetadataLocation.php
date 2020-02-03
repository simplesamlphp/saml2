<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package SimpleSAMLphp
 */
final class AdditionalMetadataLocation extends AbstractMdElement
{
    /**
     * The namespace of this metadata.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The URI where the metadata is located.
     *
     * @var string
     */
    protected $location;


    /**
     * Create a new instance of AdditionalMetadataLocation
     *
     * @param string $namespace
     * @param string $location
     */
    public function __construct(string $namespace, string $location)
    {
        $this->setNamespace($namespace);
        $this->setLocation($location);
    }


    /**
     * Initialize an AdditionalMetadataLocation element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AdditionalMetadataLocation');
        Assert::same($xml->namespaceURI, AdditionalMetadataLocation::NS);
        Assert::true(
            $xml->hasAttribute('namespace'),
            'Missing namespace attribute on AdditionalMetadataLocation element.'
        );
        return new self($xml->getAttribute('namespace'), trim($xml->textContent));
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
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setNamespace(string $namespace): void
    {
        Assert::notEmpty($namespace, 'The namespace in AdditionalMetadataLocation must be a URI.');
        $this->namespace = $namespace;
    }


    /**
     * Collect the value of the location-property
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }


    /**
     * Set the value of the location-property
     *
     * @param string $location
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setLocation(string $location): void
    {
        Assert::notEmpty($location, 'AdditionalMetadataLocation must contain a URI.');
        $this->location = $location;
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
        $e->textContent = $this->location;
        $e->setAttribute('namespace', $this->namespace);
        return $e;
    }
}
