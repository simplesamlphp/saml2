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
     * @param \DOMElement $value
     */
    public function __construct(DOMElement $value)
    {
        parent::__construct(self::XSI_TYPE);

        $this->setValue($value);
    }


    /**
     * Get the value of this Statement.
     *
     * @return \DOMElement
     */
    public function getValue(): DOMElement
    {
        return $this->value;
    }


    /**
     * Set the value of this Statement.
     *
     * @param \DOMElement $value
     * @return void
     */
    protected function setValue(DOMElement $value): void
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
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), 'CustomStatement');

        return new self($xml->ownerDocument->documentElement);
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
        $e->appendChild($e->ownerDocument->importNode($this->getValue(), true));
        return $e;
    }
}
