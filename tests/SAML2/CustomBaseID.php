<?php

namespace SAML2;

use DOMElement;
use SAML2\XML\saml\BaseID;
use Webmozart\Assert\Assert;

class CustomBaseID extends BaseID
{
    public function __construct(float $value, string $NameQualifier = null, string $SPNameQualifier = null)
    {
        parent::__construct('CustomBaseID', strval($value), $NameQualifier, $SPNameQualifier);
    }


    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), 'CustomBaseID');

        $baseID = BaseID::fromXML($xml);
        return new self(floatval($xml->textContent), $baseID->getNameQualifier(), $baseID->getSPNameQualifier());
    }
}
