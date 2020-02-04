<?php

declare(strict_types=1);

namespace SAML2\XML\spid;

use DOMElement;
use SAML2\Constants;
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
     * Initialize a saml:Issuer conforming to SPID specification
     *
     * @param string $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     * @param string|null $Format
     * @param string|null $SPProvidedID
     */
    public function __construct(
        string $value,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null,
        ?string $Format = null,
        ?string $SPProvidedID = null
    ) {
        Assert::same(
            $Format,
            Constants::NAMEID_ENTITY,
            'Invalid Format; must be \'' . Constants::NAMEID_ENTITY . '\''
        );
        Assert::notNull($NameQualifier, 'Missing mandatory NameQualifier attribute');
        Assert::allNull([$SPProvidedID, $SPNameQualifier], 'Illegal combination of attributes being used');

        parent::__construct($value, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }


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

        Assert::allNull([$SPProvidedID, $SPNameQualifier], 'Illegal combination of attributes being used');
        return new self($xml->textContent, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }
}
