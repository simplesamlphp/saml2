<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\Chunk;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
final class DiscoHints extends AbstractMduiElement
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var \SAML2\XML\Chunk[]|null
     */
    protected $children = null;

    /**
     * The IPHint, as an array of strings.
     *
     * @var string[]|null
     */
    protected $IPHint = null;

    /**
     * The DomainHint, as an array of strings.
     *
     * @var string[]|null
     */
    protected $DomainHint = null;

    /**
     * The GeolocationHint, as an array of strings.
     *
     * @var string[]|null
     */
    protected $GeolocationHint = null;


    /**
     * Create a DiscoHints element.
     *
     * @param \SAML2\XML\Chunk[]|null $children
     * @param string[]|null $IPHint
     * @param string[]|null $DomainHint
     * @param string[]|null $GeolocationHint
     */
    public function __construct(
        array $children = null,
        array $IPHint = null,
        array $DomainHint = null,
        array $GeolocationHint = null
    ) {
        $this->setChildren($children);
        $this->setIPHint($IPHint);
        $this->setDomainHint($DomainHint);
        $this->setGeolocationHint($GeolocationHint);
    }


    /**
     * Collect the value of the IPHint-property
     *
     * @return string[]|null
     */
    public function getIPHint(): ?array
    {
        return $this->IPHint;
    }


    /**
     * Set the value of the IPHint-property
     *
     * @param string[]|null $hints
     * @return void
     */
    private function setIPHint(?array $hints): void
    {
        $this->IPHint = $hints;
    }


    /**
     * Collect the value of the DomainHint-property
     *
     * @return string[]|null
     */
    public function getDomainHint(): ?array
    {
        return $this->DomainHint;
    }


    /**
     * Set the value of the DomainHint-property
     *
     * @param string[]|null $hints
     * @return void
     */
    private function setDomainHint(?array $hints): void
    {
        $this->DomainHint = $hints;
    }


    /**
     * Collect the value of the GeolocationHint-property
     *
     * @return string[]|null
     */
    public function getGeolocationHint(): ?array
    {
        return $this->GeolocationHint;
    }


    /**
     * Set the value of the GeolocationHint-property
     *
     * @param string[]|null $hints
     * @return void
     */
    private function setGeolocationHint(?array $hints): void
    {
        $this->GeolocationHint = $hints;
    }


    /**
     * Collect the value of the children-property
     *
     * @return \SAML2\XML\Chunk[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen-property
     *
     * @param array|null $children
     * @return void
     */
    private function setChildren(?array $children): void
    {
        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SAML2\XML\Chunk $child
     * @return void
     */
    public function addChild(Chunk $child): void
    {
        $this->children[] = $child;
    }


    /**
     * Convert XML into a DiscoHints
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        $IPHint = Utils::extractStrings($xml, DiscoHints::NS, 'IPHint');
        $DomainHint = Utils::extractStrings($xml, DiscoHints::NS, 'DomainHint');
        $GeolocationHint = Utils::extractStrings($xml, DiscoHints::NS, 'GeolocationHint');
        $children = [];

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, "./*[namespace-uri()!='" . DiscoHints::NS . "']") as $node) {
            $children[] = new Chunk($node);
        }

        return new self($children ?: null, $IPHint ?: null, $DomainHint ?: null, $GeolocationHint ?: null);
    }


    /**
     * Convert this DiscoHints to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement|null
     */
    public function toXML(DOMElement $parent = null): ?DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if (
            !empty($this->IPHint)
            || !empty($this->DomainHint)
            || !empty($this->GeolocationHint)
            || !empty($this->children)
        ) {
            $e = $this->instantiateParentElement($parent);

            if (!empty($this->children)) {
                foreach ($this->children as $child) {
                    $child->toXML($e);
                }
            }

            if (!empty($this->IPHint)) {
                Utils::addStrings($e, DiscoHints::NS, 'mdui:IPHint', false, $this->IPHint);
            }

            if (!empty($this->DomainHint)) {
                Utils::addStrings($e, DiscoHints::NS, 'mdui:DomainHint', false, $this->DomainHint);
            }

            if (!empty($this->GeolocationHint)) {
                Utils::addStrings($e, DiscoHints::NS, 'mdui:GeolocationHint', false, $this->GeolocationHint);
            }

            return $e;
        }

        return $e;
    }
}
