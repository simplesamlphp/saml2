<?php

namespace SAML2;

use DOMElement;
use SAML2\XML\saml\AbstractBaseIDType;
use SAML2\XML\saml\BaseID;
use Webmozart\Assert\Assert;

class CustomBaseID extends AbstractBaseIDType
{
    /** @var float $value */
    private $value;


    public function __construct(float $value, string $NameQualifier = null, string $SPNameQualifier = null)
    {
        parent::__construct($NameQualifier, $SPNameQualifier);
        $this->setValue($value);
    }


    public function getValue(): float
    {
        return $this->value;
    }


    public function setValue(float $value): void
    {
        $this->value = $value;
    }


    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID');
        Assert::same($xml->namespaceURI, BaseID::NS);

        Assert::true($xml->hasAttributeNS(Constants::NS_XSI, 'type'), 'Missing required xsi:type in <saml:BaseID> element.');
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), 'CustomBaseID');

        $NameQualifier = self::getAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);
        $value = floatval($xml->textContent);

        return new self($value, $NameQualifier, $SPNameQualifier);
    }


    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);
        $e->setAttributeNS(Constants::NS_XSI, 'xsi:type', 'CustomBaseID');
        $e->textContent = strval($this->value);

        return $e;
    }
}
