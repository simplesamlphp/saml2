<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * SAML AudienceRestriction data type.
 *
 * @package simplesamlphp/saml2
 */
final class AudienceRestriction extends AbstractConditionType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Initialize a saml:AudienceRestriction
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    public function __construct(
        protected array $audience,
    ) {
        Assert::minCount($audience, 1, SchemaViolationException::class);
        Assert::maxCount($audience, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($audience, Audience::class, SchemaViolationException::class);
    }


    /**
     * Collect the audience
     *
     * @return \SimpleSAML\SAML2\XML\saml\Audience[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Convert XML into an AudienceRestriction
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AudienceRestriction', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AudienceRestriction::NS, InvalidDOMElementException::class);

        $audience = Audience::getChildrenOfClass($xml);

        return new static($audience);
    }


    /**
     * Convert this Audience to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this AudienceRestriction.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAudience() as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
