<?php

declare(strict_types=1);

namespace SAML2\XML\mdattr;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\saml\Attribute;
use Webmozart\Assert\Assert;

/**
 * Class for handling the EntityAttributes metadata extension.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-metadata-attr-cs-01.pdf
 * @package SimpleSAMLphp
 */
final class EntityAttributes extends AbstractMdattrElement
{
    /**
     * Array with child elements.
     *
     * The elements can be \SAML2\XML\saml\Attribute or \SAML2\XML\Chunk elements.
     *
     * @var (\SAML2\XML\saml\Attribute|\SAML2\XML\Chunk)[]
     */
    protected $children = [];


    /**
     * Create a EntityAttributes element.
     *
     * @param (\SAML2\XML\Chunk|\SAML2\XML\saml\Attribute)[] $children
     */
    public function __construct(array $children)
    {
        $this->setChildren($children);
    }


    /**
     * Collect the value of the children-property
     *
     * @return (\SAML2\XML\Chunk|\SAML2\XML\saml\Attribute)[]
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
        Assert::allIsInstanceOfAny($children, [Chunk::class, Attribute::class]);

        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SAML2\XML\Chunk|\SAML2\XML\saml\Attribute $child
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
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
     */
    public static function fromXML(DOMElement $xml): object
    {
        $children = [];

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, './saml_assertion:Attribute|./saml_assertion:Assertion') as $node) {
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
