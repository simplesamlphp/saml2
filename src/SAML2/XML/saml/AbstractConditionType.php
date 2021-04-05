<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;

/**
 * Abstract class to be implemented by all the conditions in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractConditionType extends AbstractSamlElement
{
    /** @var string */
    protected string $value;


    /**
     * Initialize a saml:Condition from scratch
     *
     * @param string $value
     */
    protected function __construct(string $value)
    {
        $this->setValue($value);
    }


    /**
     * Get the string value of this Condition.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Set the string value of this Condition.
     *
     * @param string $value
     */
    protected function setValue(string $value): void
    {
        Assert::notWhitespaceOnly($value);
        $this->value = $value;
    }


    /**
     * Convert this Condition to XML.
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
