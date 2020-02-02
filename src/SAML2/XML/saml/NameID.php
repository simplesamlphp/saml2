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
class NameID extends NameIDType
{
    /**
     * Convert XML into a NameID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\NameID
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'NameID');
        Assert::same($xml->namespaceURI, NameID::NS);

        $Format = $xml->hasAttribute('Format') ? $xml->getAttribute('Format') : null;
        $SPProvidedID = $xml->hasAttribute('SPProvidedID') ? $xml->getAttribute('SPProvidedID') : null;
        $NameQualifier = $xml->hasAttribute('NameQualifier') ? $xml->getAttribute('NameQualifier') : null;
        $SPNameQualifier = $xml->hasAttribute('SPNameQualifier') ? $xml->getAttribute('SPNameQualifier') : null;

        return new self(
            $xml,
            $Format,
            $SPProvidedID,
            $NameQualifier,
            $SPNameQualifier
        );

    }
}
