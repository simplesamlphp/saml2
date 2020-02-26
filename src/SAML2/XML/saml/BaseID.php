<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * SAML BaseID data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class BaseID extends AbstractBaseIDType
{
    /**
     * Convert XML into an BaseID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\BaseID
     * @throws \InvalidArgumentException
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID');
        Assert::same($xml->namespaceURI, BaseID::NS);

        $NameQualifier = self::getAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);

        return new self($NameQualifier, $SPNameQualifier);
    }
}
