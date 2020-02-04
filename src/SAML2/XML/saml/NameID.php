<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * Class representing the saml:NameID element.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package SimpleSAMLphp
 */
final class NameID extends NameIDType
{
    /**
     * Convert XML into an NameID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\NameID
     * @throws \InvalidArgumentException
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'NameID');
        Assert::same($xml->namespaceURI, NameID::NS);

        $NameQualifier = self::getAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);
        $Format = self::getAttribute($xml, 'Format', null);
        $SPProvidedID = self::getAttribute($xml, 'SPProvidedID', null);

        return new self($xml->textContent, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }
}
