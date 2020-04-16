<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use Webmozart\Assert\Assert;

/**
 * Abstract class to be implemented by all the conditions in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractConditionType extends AbstractSamlElement
{
    /** @var string */
    protected $value;


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
     * @return void
     */
    protected function setValue(string $value): void
    {
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
