<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class representing a saml:OneTimeUse element.
 *
 * @package simplesaml/saml2
 */
final class OneTimeUse extends AbstractConditionType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Convert XML into an OneTimeUse
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'OneTimeUse', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, OneTimeUse::NS, InvalidDOMElementException::class);

        return new static();
    }


    /**
     * Convert this OneTimeUse to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this OneTimeUse.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        return $this->instantiateParentElement($parent);
    }
}
