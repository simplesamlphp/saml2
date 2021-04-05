<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class representing a saml:Audience element.
 *
 * @package simplesaml/saml2
 */
final class Audience extends AbstractSamlElement
{
    /** @var string */
    protected string $value;


    /**
     * Initialize an Audience element.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->setValue($value);
    }


    /**
     * Get the string value of this Audience
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Set the string value of this Audience
     *
     * @param string $value
     */
    protected function setValue(string $value): void
    {
        Assert::notWhitespaceOnly($value);
        $this->value = $value;
    }


    /**
     * Convert XML into an Audience
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Audience', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Audience::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }


    /**
     * Convert this Audience to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = $this->instantiateParentElement($parent);
        $element->textContent = $this->value;

        return $element;
    }
}

