<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class representing the saml:Issuer element.
 *
 * @package simplesamlphp/saml2
 */
final class Issuer extends NameIDType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize a saml:Issuer
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $value
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $NameQualifier
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPNameQualifier
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $Format
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPProvidedID
     */
    public function __construct(
        SAMLStringValue $value,
        ?SAMLStringValue $NameQualifier = null,
        ?SAMLStringValue $SPNameQualifier = null,
        ?SAMLAnyURIValue $Format = null,
        ?SAMLStringValue $SPProvidedID = null,
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
         */
        if ($Format === null || $Format->getValue() === C::NAMEID_ENTITY) {
            Assert::allNull(
                [$NameQualifier, $SPNameQualifier, $SPProvidedID],
                'Illegal combination of attributes being used',
            );

            Assert::validEntityID($value->getValue(), ProtocolViolationException::class);
        }

        parent::__construct($value, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }
}
