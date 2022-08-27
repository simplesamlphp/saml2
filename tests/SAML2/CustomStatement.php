<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\XML\saml\Statement;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Assert\Assert;

/**
 * @covers \SimpleSAML\Test\SAML2\CustomStatement
 * @package simplesamlphp\saml2
 */
final class CustomStatement extends Statement
{
    /** @var string */
    protected const NS_XSI_TYPE_NAME = 'CustomStatement';

    /** @var string */
    protected const NS_XSI_TYPE_NAMESPACE = C::NAMESPACE;

    /** @var string */
    protected const NS_XSI_TYPE_PREFIX = 'ssp';

    /** @var string */
    protected string $value;


    /**
     * CustomStatement constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        parent::__construct(self::getXsiType());
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
     */
    protected function setValue(string $value): void
    {
        $this->value = $value;
    }


    /**
     * Convert XML into an CustomStatement
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(C::NS_XSI, 'type'), 'ssp:CustomStatement');

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
