<?php

declare(strict_types=1);

namespace SAML2\XML\mdattr;

use DOMElement;
use SAML2\Utils\XPath;
use SAML2\XML\saml\Attribute;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;

/**
 * Class for handling the EntityAttributes metadata extension.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-metadata-attr-cs-01.pdf
 * @package SimpleSAMLphp
 */
class EntityAttributes
{
    /**
     * The namespace used for the EntityAttributes extension.
     */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:attribute';

    /**
     * Array with child elements.
     *
     * The elements can be \SAML2\XML\saml\Attribute or \SimpleSAML\XML\Chunk elements.
     *
     * @var (\SAML2\XML\saml\Attribute|\SimpleSAML\XML\Chunk)[]
     */
    private array $children = [];


    /**
     * Create a EntityAttributes element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($xml, './saml_assertion:Attribute|./saml_assertion:Assertion', $xpCache) as $node) {
            if ($node->localName === 'Attribute') {
                $this->children[] = new Attribute($node);
            } else {
                $this->children[] = new Chunk($node);
            }
        }
    }


    /**
     * Collect the value of the children-property
     *
     * @return (\SimpleSAML\XML\Chunk|\SAML2\XML\saml\Attribute)[]
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
     * @param \SimpleSAML\XML\Chunk|\SAML2\XML\saml\Attribute $child
     * @return void
     */
    public function addChildren($child): void
    {
        Assert::isInstanceOfAny($child, [Chunk::class, Attribute::class]);
        $this->children[] = $child;
    }


    /**
     * Convert this EntityAttributes to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(EntityAttributes::NS, 'mdattr:EntityAttributes');
        $parent->appendChild($e);

        /** @var \SAML2\XML\saml\Attribute|\SimpleSAML\XML\Chunk $child */
        foreach ($this->children as $child) {
            $child->toXML($e);
        }

        return $e;
    }
}
