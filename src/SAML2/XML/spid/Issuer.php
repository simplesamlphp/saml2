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
     * @param string $NameQualifier
     */
    public function __construct(
        string $value,
        string $NameQualifier = null
    ) {
        parent::__construct($value, $NameQualifier, null, Constants::NAMEID_ENTITY);
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

        return new self($xml->textContent, self::getAttribute($xml, 'NameQualifier'));
    }
}
