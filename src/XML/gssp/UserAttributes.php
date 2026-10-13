<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\gssp;

use Dom;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;

/**
 * Class representing a GSSP UserAttributes-element
 *
 * @package simplesamlphp/saml2
 */
class UserAttributes extends AbstractGsspElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * UserAttributes constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     */
    final public function __construct(
        protected array $attributes = [],
    ) {
        Assert::minCount($attributes, 1, MissingElementException::class);
        Assert::maxCount($attributes, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($attributes, Attribute::class);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(Dom\Element $xml): static
    {
        Assert::same($xml->localName, 'UserAttributes', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, UserAttributes::NS, InvalidDOMElementException::class);

        return new static(
            Attribute::getChildrenOfClass($xml),
        );
    }


    /**
     * Convert this UserAttributes to XML.
     */
    public function toXML(?Dom\Element $parent = null): Dom\Element
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAttributes() as $attribute) {
            $attribute->toXML($e);
        }

        return $e;
    }
}
