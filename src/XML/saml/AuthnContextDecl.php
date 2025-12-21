<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

/**
 * Class representing SAML2 AuthnContextDecl
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContextDecl extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:any element */
    public const string XS_ANY_ELT_NAMESPACE = NS::ANY;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const string XS_ANY_ATTR_NAMESPACE = NS::ANY;


    /**
     * Initialize an AuthnContextDecl.
     *
     * @param \SimpleSAML\XML\Chunk[] $elements
     * @param \SimpleSAML\XML\Attribute[] $attributes
     */
    public function __construct(array $elements = [], array $attributes = [])
    {
        $this->setElements($elements);
        $this->setAttributesNS($attributes);
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getAttributesNS())
            && empty($this->getElements());
    }


    /**
     * Convert XML into a AuthnContextDecl
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnContextDecl', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContextDecl::NS, InvalidDOMElementException::class);

        return new static(
            self::getChildElementsFromXML($xml),
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this AuthContextDecl to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        foreach ($this->getElements() as $element) {
            /** @psalm-var \SimpleSAML\XML\SerializableElementInterface $element */
            $element->toXML($e);
        }

        return $e;
    }
}
