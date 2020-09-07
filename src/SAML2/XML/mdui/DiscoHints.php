<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class DiscoHints extends AbstractMduiElement
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var \SimpleSAML\XML\Chunk[]
     */
    protected array $children = [];

    /**
     * The IPHint, as an array of strings.
     *
     * @var string[]
     */
    protected array $IPHint = [];

    /**
     * The DomainHint, as an array of strings.
     *
     * @var string[]
     */
    protected array $DomainHint = [];

    /**
     * The GeolocationHint, as an array of strings.
     *
     * @var string[]
     */
    protected array $GeolocationHint = [];


    /**
     * Create a DiscoHints element.
     *
     * @param \SimpleSAML\XML\Chunk[] $children
     * @param string[] $IPHint
     * @param string[] $DomainHint
     * @param string[] $GeolocationHint
     */
    public function __construct(
        array $children = [],
        array $IPHint = [],
        array $DomainHint = [],
        array $GeolocationHint = []
    ) {
        $this->setChildren($children);
        $this->setIPHint($IPHint);
        $this->setDomainHint($DomainHint);
        $this->setGeolocationHint($GeolocationHint);
    }


    /**
     * Collect the value of the IPHint-property
     *
     * @return string[]
     */
    public function getIPHint(): array
    {
        return $this->IPHint;
    }


    /**
     * Set the value of the IPHint-property
     *
     * @param string[] $hints
     * @return void
     */
    private function setIPHint(array $hints): void
    {
        Assert::allStringNotEmpty($hints);

        $this->IPHint = $hints;
    }


    /**
     * Collect the value of the DomainHint-property
     *
     * @return string[]
     */
    public function getDomainHint(): array
    {
        return $this->DomainHint;
    }


    /**
     * Set the value of the DomainHint-property
     *
     * @param string[] $hints
     * @return void
     */
    private function setDomainHint(array $hints): void
    {
        Assert::allStringNotEmpty($hints);

        $this->DomainHint = $hints;
    }


    /**
     * Collect the value of the GeolocationHint-property
     *
     * @return string[]
     */
    public function getGeolocationHint(): array
    {
        return $this->GeolocationHint;
    }


    /**
     * Set the value of the GeolocationHint-property
     *
     * @param string[] $hints
     * @return void
     */
    private function setGeolocationHint(array $hints): void
    {
        $this->GeolocationHint = $hints;
    }


    /**
     * Collect the value of the children-property
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen-property
     *
     * @param array $children
     * @return void
     */
    private function setChildren(array $children): void
    {
        Assert::allIsInstanceOf($children, Chunk::class);

        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SimpleSAML\XML\Chunk $child
     * @return void
     */
    public function addChild(Chunk $child): void
    {
        $this->children[] = $child;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->children)
            && empty($this->IPHint)
            && empty($this->DomainHint)
            && empty($this->GeolocationHint)
        );
    }


    /**
     * Convert XML into a DiscoHints
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'DiscoHints', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, DiscoHints::NS, InvalidDOMElementException::class);

        $IPHint = XMLUtils::extractStrings($xml, DiscoHints::NS, 'IPHint');
        $DomainHint = XMLUtils::extractStrings($xml, DiscoHints::NS, 'DomainHint');
        $GeolocationHint = XMLUtils::extractStrings($xml, DiscoHints::NS, 'GeolocationHint');
        $children = [];

        /** @var \DOMElement $node */
        foreach (XMLUtils::xpQuery($xml, "./*[namespace-uri()!='" . DiscoHints::NS . "']") as $node) {
            $children[] = new Chunk($node);
        }

        return new self($children, $IPHint, $DomainHint, $GeolocationHint);
    }


    /**
     * Convert this DiscoHints to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->children as $child) {
            $child->toXML($e);
        }

        XMLUtils::addStrings($e, DiscoHints::NS, 'mdui:IPHint', false, $this->IPHint);
        XMLUtils::addStrings($e, DiscoHints::NS, 'mdui:DomainHint', false, $this->DomainHint);
        XMLUtils::addStrings($e, DiscoHints::NS, 'mdui:GeolocationHint', false, $this->GeolocationHint);

        return $e;
    }
}
