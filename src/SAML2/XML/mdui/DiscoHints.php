<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableElementTrait;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class DiscoHints extends AbstractMduiElement
{
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const NAMESPACE = C::XS_ANY_NS_OTHER;

    /**
     * The IPHint, as an array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\mdui\IPHint[]
     */
    protected array $IPHint = [];

    /**
     * The DomainHint, as an array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\mdui\DomainHint[]
     */
    protected array $DomainHint = [];

    /**
     * The GeolocationHint, as an array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\mdui\GeolocationHint[]
     */
    protected array $GeolocationHint = [];


    /**
     * Create a DiscoHints element.
     *
     * @param \SimpleSAML\XML\Chunk[] $children
     * @param \SimpleSAML\SAML2\XML\mdui\IPHint[] $IPHint
     * @param \SimpleSAML\SAML2\XML\mdui\DomainHint[] $DomainHint
     * @param \SimpleSAML\SAML2\XML\mdui\GeolocationHint[] $GeolocationHint
     */
    public function __construct(
        array $children = [],
        array $IPHint = [],
        array $DomainHint = [],
        array $GeolocationHint = []
    ) {
        $this->setElements($children);
        $this->setIPHint($IPHint);
        $this->setDomainHint($DomainHint);
        $this->setGeolocationHint($GeolocationHint);
    }


    /**
     * Collect the value of the IPHint-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\IPHint[]
     */
    public function getIPHint(): array
    {
        return $this->IPHint;
    }


    /**
     * Set the value of the IPHint-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\IPHint[] $hints
     */
    private function setIPHint(array $hints): void
    {
        Assert::allIsInstanceOf($hints, IPHint::class);

        $this->IPHint = $hints;
    }


    /**
     * Collect the value of the DomainHint-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\DomainHint[]
     */
    public function getDomainHint(): array
    {
        return $this->DomainHint;
    }


    /**
     * Set the value of the DomainHint-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\DomainHint[] $hints
     */
    private function setDomainHint(array $hints): void
    {
        Assert::allIsInstanceOf($hints, DomainHint::class);

        $this->DomainHint = $hints;
    }


    /**
     * Collect the value of the GeolocationHint-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\GeolocationHint[]
     */
    public function getGeolocationHint(): array
    {
        return $this->GeolocationHint;
    }


    /**
     * Set the value of the GeolocationHint-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\GeolocationHint[] $hints
     */
    private function setGeolocationHint(array $hints): void
    {
        Assert::allIsInstanceOf($hints, GeolocationHint::class);

        $this->GeolocationHint = $hints;
    }


    /**
     * Add the value to the elements-property
     *
     * @param \SimpleSAML\XML\Chunk $child
     */
    public function addChild(Chunk $child): void
    {
        $this->elements[] = $child;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->elements)
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
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'DiscoHints', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, DiscoHints::NS, InvalidDOMElementException::class);

        $IPHint = IPHint::getChildrenOfClass($xml);
        $DomainHint = DomainHint::getChildrenOfClass($xml);
        $GeolocationHint = GeolocationHint::getChildrenOfClass($xml);
        $children = [];

        /** @var \DOMElement[] $nodes */
        $nodes = XPath::xpQuery($xml, "./*[namespace-uri()!='" . DiscoHints::NS . "']", XPath::getXPath($xml));
        foreach ($nodes as $node) {
            $children[] = new Chunk($node);
        }

        return new static($children, $IPHint, $DomainHint, $GeolocationHint);
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

        foreach ($this->getElements() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getIPHint() as $hint) {
            $hint->toXML($e);
        }

        foreach ($this->getDomainHint() as $hint) {
            $hint->toXML($e);
        }

        foreach ($this->getGeolocationHint() as $hint) {
            $hint->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): static
    {
        $IPHint = [];
        foreach ($data['IPHint'] as $hint) {
            $IPHint[] = new IPHint($hint);
        }

        $DomainHint = [];
        foreach ($data['DomainHint'] as $hint) {
            $DomainHint[] = new DomainHint($hint);
        }

        $GeolocationHint = [];
        foreach ($data['GeolocationHint'] as $hint) {
            $GeolocationHint[] = new GeolocationHint($hint);
        }

        return new static([], $IPHint, $DomainHint, $GeolocationHint);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'IPHint' => [],
            'DomainHint' => [],
            'GeolocationHint' => [],
        ];

        foreach ($this->getIPHint() as $hint) {
            $data['IPHint'][] = $hint->getContent();
        }

        foreach ($this->getDomainHint() as $hint) {
            $data['DomainHint'][] = $hint->getContent();
        }

        foreach ($this->getGeolocationHint() as $hint) {
            $data['GeolocationHint'][] = $hint->getContent();
        }

        return $data;
    }
}
