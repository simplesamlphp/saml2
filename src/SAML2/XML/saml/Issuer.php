<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * Class representing the saml:Issuer element.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
final class Issuer extends NameIDType
{
    /**
     * Initialize a saml:Issuer
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
        /**
         * The format of this NameIDType.
         *
         * Defaults to urn:oasis:names:tc:SAML:2.0:nameid-format:entity:
         *
         * Indicates that the content of the element is the identifier of an entity that provides SAML-based services
         * (such as a SAML authority, requester, or responder) or is a participant in SAML profiles (such as a service
         * provider supporting the browser SSO profile). Such an identifier can be used in the <Issuer> element to
         * identify the issuer of a SAML request, response, or assertion, or within the <NameID> element to make
         * assertions about system entities that can issue SAML requests, responses, and assertions. It can also be
         * used in other elements and attributes whose purpose is to identify a system entity in various protocol
         * exchanges.
         *
         * The syntax of such an identifier is a URI of not more than 1024 characters in length. It is RECOMMENDED that
         * a system entity use a URL containing its own domain name to identify itself.
         *
         * @see saml-core-2.0-os
         *
         * From saml-core-2.0-os 8.3.6, when the entity Format is used: "The NameQualifier, SPNameQualifier, and
         * SPProvidedID attributes MUST be omitted."
         *
         * @var string
         */
        if ($Format === Constants::NAMEID_ENTITY) {
            Assert::allNull([$SPProvidedID, $NameQualifier, $SPNameQualifier], 'Illegal combination of attributes being used');
        }

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

        return new self($xml->textContent, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }
}
