<?php

namespace SAML2;

use DOMElement;
use SAML2\XML\saml\Statement;
use SimpleSAML\Assert\Assert;

final class CustomStatement extends Statement
{
    protected const XSI_TYPE = 'CustomStatement';

    /** @var \DOMElement */
    protected $value;


    /**
     * CustomStatement constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        parent::__construct(self::XSI_TYPE);

        $this->setValue($value);
    }


    /**
     * Get the value of this Statement.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Set the value of this Statement.
     *
     * @param string $value
     * @return void
     */
    protected function setValue(string $value): void
    {
        $this->value = $value;
    }


    /**
     * @inheritDoc
     */
    public static function getXsiType(): string
    {
        return self::XSI_TYPE;
    }


    /**
     * Convert XML into an CustomStatement
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), 'CustomStatement');

        return new self($xml->textContent);
    }


    /**
     * Convert this Statement to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);
        $e->textContent = $this->getValue();

        return $e;
    }
}
