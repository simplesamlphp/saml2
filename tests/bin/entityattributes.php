#!/usr/bin/env php
<?php

namespace SimpleSAML;

use DateTimeImmutable;
use DateTimeZone;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$unsignedAssertion = new Assertion(
    issuer: new Issuer(SAMLStringValue::fromString('urn:x-simplesamlphp:issuer')),
    issueInstant: SAMLDateTimeValue::fromDateTime(new DateTimeImmutable('now', new DateTimeZone('Z'))),
    id: IDValue::fromString('_93af655219464fb403b34436cfb0c5cb1d9a5502'),
    subject: new Subject(new NameID(
        value: SAMLStringValue::fromString('some:entity'),
        Format: SAMLAnyURIValue::fromString(C::NAMEID_ENTITY),
    )),
    conditions: new Conditions(
        condition: [],
        audienceRestriction: [
            new AudienceRestriction([
                new Audience(SAMLAnyURIValue::fromString('https://simplesamlphp.org/idp/metadata')),
                new Audience(SAMLAnyURIValue::fromString('urn:x-simplesamlphp:phpunit:entity')),
            ]),
        ],
    ),
    statements: [new AttributeStatement([
        new Attribute(
            name: SAMLStringValue::fromString('urn:mace:dir:attribute-def:uid'),
            nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            attributeValue: [
                new AttributeValue(SAMLStringValue::fromString('student2')),
            ],
        ),
        new Attribute(
            name: SAMLStringValue::fromString('urn:mace:terena.org:attribute-def:schacHomeOrganization'),
            nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            attributeValue: [
                new AttributeValue(SAMLStringValue::fromString('university.example.org')),
                new AttributeValue(SAMLStringValue::fromString('bbb.cc')),
            ],
        ),
        new Attribute(
            name: SAMLStringValue::fromString('urn:schac:attribute-def:schacPersonalUniqueCode'),
            nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            attributeValue: [
                new AttributeValue(
                    SAMLStringValue::fromString('urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020'),
                ),
                new AttributeValue(
                    SAMLStringValue::fromString('urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345'),
                ),
            ],
        ),
        new Attribute(
            name: SAMLStringValue::fromString('urn:mace:dir:attribute-def:eduPersonAffiliation'),
            nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            attributeValue: [
                new AttributeValue(SAMLStringValue::fromString('member')),
                new AttributeValue(SAMLStringValue::fromString('student')),
            ],
        ),
    ])],
);
$unsignedAssertion->sign($signer);
$signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());
$entityAttributes = new EntityAttributes([
    new Attribute(
        name: SAMLStringValue::fromString('attrib1'),
        nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_BASIC),
        attributeValue: [
            new AttributeValue(SAMLStringValue::fromString('is')),
            new AttributeValue(SAMLStringValue::fromString('really')),
            new AttributeValue(SAMLStringValue::fromString('cool')),
        ],
    ),
    $signedAssertion,
    new Attribute(
        name: SAMLStringValue::fromString('foo'),
        nameFormat: SAMLAnyURIValue::fromString('urn:simplesamlphp:v1:simplesamlphp'),
        attributeValue: [
            new AttributeValue(SAMLStringValue::fromString('is')),
            new AttributeValue(SAMLStringValue::fromString('really')),
            new AttributeValue(SAMLStringValue::fromString('cool')),
        ],
    ),
]);

echo $entityAttributes->toXML()->ownerDocument?->saveXML();
