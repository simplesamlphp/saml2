<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class representing SAML 2 metadata AdditionalMetadataLocation element.
 *
 * @package SimpleSAMLphp
 */
class AdditionalMetadataLocation
{
    /**
     * The namespace of this metadata.
     *
     * @var string
     */
    private string $namespace;

    /**
     * The URI where the metadata is located.
     *
     * @var string
     */
    private string $location;


    /**
     * Initialize an AdditionalMetadataLocation element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('namespace')) {
            throw new MissingAttributeException('Missing namespace attribute on AdditionalMetadataLocation element.');
        }
        $this->setNamespace($xml->getAttribute('namespace'));

        $this->setLocation($xml->textContent);
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
     */
    public function setNamespace(string $namespace): void
    {
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
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }


    /**
     * Convert this AdditionalMetadataLocation to XML.
     *
     * @param  \DOMElement $parent The element we should append to.
     * @return \DOMElement This AdditionalMetadataLocation-element.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $e = XMLUtils::addString($parent, C::NS_MD, 'md:AdditionalMetadataLocation', $this->getLocation());
        $e->setAttribute('namespace', $this->getNamespace());

        return $e;
    }
}
