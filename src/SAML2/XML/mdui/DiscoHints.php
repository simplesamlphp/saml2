<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class DiscoHints
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SimpleSAML\SAML2\XML\mdui\* elements.
     *
     * @var \SimpleSAML\XML\Chunk[]
     */
    private array $children = [];

    /**
     * The IPHint, as an array of strings.
     *
     * @var string[]
     */
    private array $IPHint = [];

    /**
     * The DomainHint, as an array of strings.
     *
     * @var string[]
     */
    private array $DomainHint = [];

    /**
     * The GeolocationHint, as an array of strings.
     *
     * @var string[]
     */
    private array $GeolocationHint = [];


    /**
     * Create a DiscoHints element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->IPHint = XMLUtils::extractStrings($xml, C::NS_MDUI, 'IPHint');
        $this->DomainHint = XMLUtils::extractStrings($xml, C::NS_MDUI, 'DomainHint');
        $this->GeolocationHint = XMLUtils::extractStrings($xml, C::NS_MDUI, 'GeolocationHint');

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($xml, "./*[namespace-uri()!='" . C::NS_MDUI . "']", $xpCache) as $node) {
            $this->children[] = new Chunk($node);
        }
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
    public function setIPHint(array $hints): void
    {
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
    public function setDomainHint(array $hints): void
    {
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
    public function setGeolocationHint(array $hints): void
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
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SimpleSAML\XML\Chunk $child
     * @return void
     */
    public function addChildren(Chunk $child): void
    {
        $this->children[] = $child;
    }


    /**
     * Convert this DiscoHints to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement|null
     */
    public function toXML(DOMElement $parent): ?DOMElement
    {
        if (
            !empty($this->IPHint)
            || !empty($this->DomainHint)
            || !empty($this->GeolocationHint)
            || !empty($this->children)
        ) {
            $doc = $parent->ownerDocument;

            $e = $doc->createElementNS(C::NS_MDUI, 'mdui:DiscoHints');
            $parent->appendChild($e);

            foreach ($this->getChildren() as $child) {
                $child->toXML($e);
            }

            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:IPHint', false, $this->IPHint);
            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:DomainHint', false, $this->DomainHint);
            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:GeolocationHint', false, $this->GeolocationHint);

            return $e;
        }

        return null;
    }
}
