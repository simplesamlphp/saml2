<?php

declare(strict_types=1);

namespace SAML2\XML\spid;

use DOMElement;
use SAML2\XML\saml\NameIDType;
use Webmozart\Assert\Assert;

/**
 * Class representing the saml:Issuer element compliant with SPID spefication.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class Issuer extends NameIDType
{
    /**
     * Convert XML into an Issuer
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\Issuer
     * @throws \InvalidArgumentException
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Issuer');
        Assert::same($xml->namespaceURI, Issuer::NS);

        $Format = self::getAttribute($xml, 'Format', null);
        $SPProvidedID = self::getAttribute($xml, 'SPProvidedID', null);
        $NameQualifier = self::getAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);

        return new self($xml->textContent, $Format, $SPProvidedID, $NameQualifier, $SPNameQualifier);
    }
}
