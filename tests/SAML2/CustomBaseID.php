<?php

namespace SAML2;

use DOMElement;
use SAML2\XML\saml\BaseID;
use SAML2\XML\saml\CustomIdentifierInterface;
use SimpleSAML\Assert\Assert;

final class CustomBaseID extends BaseID implements CustomIdentifierInterface
{
    protected const XSI_TYPE = 'CustomBaseID';


    /**
     * CustomBaseID constructor.
     *
     * @param float $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(float $value, string $NameQualifier = null, string $SPNameQualifier = null)
    {
        parent::__construct(self::XSI_TYPE, strval($value), $NameQualifier, $SPNameQualifier);
    }


    /**
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), 'CustomBaseID');

        $baseID = BaseID::fromXML($xml);
        return new self(floatval($xml->textContent), $baseID->getNameQualifier(), $baseID->getSPNameQualifier());
    }


    /**
     * @inheritDoc
     */
    public static function getXsiType(): string
    {
        return self::XSI_TYPE;
    }
}
