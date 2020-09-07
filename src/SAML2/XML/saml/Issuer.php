<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

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
        if ($Format === Constants::NAMEID_ENTITY || $Format === null) {
            Assert::allNull(
                [$NameQualifier, $SPNameQualifier, $SPProvidedID],
                'Illegal combination of attributes being used'
            );
        }

        parent::__construct($value, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }


    /**
     * Convert XML into an Issuer
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Issuer
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Issuer', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Issuer::NS, InvalidDOMElementException::class);

        $Format = self::getAttribute($xml, 'Format', null);
        $SPProvidedID = self::getAttribute($xml, 'SPProvidedID', null);
        $NameQualifier = self::getAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);

        return new self($xml->textContent, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }
}
