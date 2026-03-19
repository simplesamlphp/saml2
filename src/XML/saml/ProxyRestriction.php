<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue;

/**
 * @package simplesamlphp/saml2
 */
final class ProxyRestriction extends AbstractConditionType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * ProxyRestriction constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     * @param \SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue|null $count
     */
    public function __construct(
        protected array $audience = [],
        protected ?NonNegativeIntegerValue $count = null,
    ) {
        Assert::maxCount($audience, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($audience, Audience::class);
    }


    /**
     * Get the value of the count-attribute.
     *
     * @return \SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue|null
     */
    public function getCount(): ?NonNegativeIntegerValue
    {
        return $this->count;
    }


    /**
     * Get the value of the audience-attribute.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Audience[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ProxyRestriction', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ProxyRestriction::NS, InvalidDOMElementException::class);

        $count = self::getOptionalAttribute($xml, 'Count', NonNegativeIntegerValue::class, null);
        $audience = Audience::getChildrenOfClass($xml);

        return new static($audience, $count);
    }


    /**
     * Convert this Condition to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getCount() !== null) {
            $e->setAttribute('Count', $this->getCount()->getValue());
        }

        foreach ($this->getAudience() as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
