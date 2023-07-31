<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function strval;

/**
 * @package simplesamlphp/saml2
 */
final class ProxyRestriction extends AbstractConditionType
{
    /**
     * ProxyRestriction constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     * @param int|null $count
     */
    public function __construct(
        protected array $audience = [],
        protected ?int $count = null,
    ) {
        Assert::nullOrNatural($count, 'Count must be a non-negative integer.');
        Assert::maxCount($audience, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($audience, Audience::class);
    }


    /**
     * Get the value of the count-attribute.
     *
     * @return int|null
     */
    public function getCount(): ?int
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
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ProxyRestriction', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ProxyRestriction::NS, InvalidDOMElementException::class);

        $count = self::getOptionalIntegerAttribute($xml, 'Count', null);
        $audience = Audience::getChildrenOfClass($xml);

        return new static($audience, $count);
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getCount() !== null) {
            $e->setAttribute('Count', strval($this->getCount()));
        }

        foreach ($this->getAudience() as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
