<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdattr;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class for handling the EntityAttributes metadata extension.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-metadata-attr-cs-01.pdf
 * @package simplesamlphp/saml2
 */
final class EntityAttributes extends AbstractMdattrElement
{
    /**
     * Array with child elements.
     *
     * The elements can be \SimpleSAML\SAML2\XML\saml\Attribute or \SimpleSAML\XML\Chunk elements.
     *
     * @var (\SimpleSAML\SAML2\XML\saml\Attribute|\SimpleSAML\XML\Chunk)[]
     */
    protected array $children = [];


    /**
     * Create a EntityAttributes element.
     *
     * @param (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\saml\Attribute)[] $children
     */
    public function __construct(array $children)
    {
        $this->setChildren($children);
    }


    /**
     * Collect the value of the children-property
     *
     * @return (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\saml\Attribute)[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen-property
     *
     * @param (\SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\saml\Attribute)[] $children
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    private function setChildren(array $children): void
    {
        Assert::allIsInstanceOfAny($children, [Chunk::class, Attribute::class]);

        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SimpleSAML\XML\Chunk|\SimpleSAML\SAML2\XML\saml\Attribute $child
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function addChild($child): void
    {
        Assert::isInstanceOfAny($child, [Chunk::class, Attribute::class]);

        $this->children[] = $child;
    }


    /**
     * Convert XML into a EntityAttributes
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EntityAttributes', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EntityAttributes::NS, InvalidDOMElementException::class);

        $children = [];
        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($xml, './saml_assertion:Attribute|./saml_assertion:Assertion', $xpCache) as $node) {
            if ($node->localName === 'Attribute') {
                $children[] = Attribute::fromXML($node);
            } else {
                $children[] = new Chunk($node);
            }
        }

        return new self($children);
    }


    /**
     * Convert this EntityAttributes to XML.
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

        return $e;
    }
}
