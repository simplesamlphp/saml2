#!/usr/bin/env php
<?php

namespace SimpleSAML;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$unsignedAssertion = new Assertion(
    issuer: new Issuer('testIssuer'),
    id: '_93af655219464fb403b34436cfb0c5cb1d9a5502',
    subject: new Subject(new NameID(
        value: 'some:entity',
        Format: C::NAMEID_ENTITY,
    )),
    conditions: new Conditions(
        condition: [],
        audienceRestriction: [
            new AudienceRestriction([
                new Audience('https://simplesamlphp.org/idp/metadata'),
                new Audience('urn:x-simplesamlphp:phpunit:entity'),
            ]),
        ],
    ),
    statements: [new AttributeStatement([
        new Attribute(
            Name: 'urn:mace:dir:attribute-def:uid',
            NameFormat: C::NAMEFORMAT_URI,
            AttibuteValues: [new AttributeValue('student2')],
        ),
        new Attribute(
            Name: 'urn:mace:terena.org:attribute-def:schacHomeOrganization',
            NameFormat: C::NAMEFORMAT_URI,
            AttributesValues: [new AttributeValue('university.example.org'), new AttributeValue('bbb.cc')],
        ),
        new Attribute(
            Name: 'urn:schac:attribute-def:schacPersonalUniqueCode',
            NameFormat: C::NAMEFORMAT_URI,
            AttributeValues: [
                new AttributeValue('urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020'),
                new AttributeValue('urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345'),
            ],
        ),
        new Attribute(
            Name: 'urn:mace:dir:attribute-def:eduPersonAffiliation',
            NameFormat: C::NAMEFORMAT_URI,
            AttributeValues: [new AttributeValue('member'), new AttributeValue('student')],
        ),
    ])],
);
$unsignedAssertion->sign($signer);
$signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());
$entityAttributes = new EntityAttributes([
    new Attribute(
        Name: 'attrib1',
        NameFormat: C::NAMEFORMAT_URI,
        AttributeValues: [new AttributeValue('is'), new AttributeValue('really'), new AttributeValue('cool')],
    ),
    $signedAssertion,
    new Attribute(
        Name: 'foo',
        NameFormat: 'urn:simplesamlphp:v1:simplesamlphp',
        AttributeValues: [new AttributeValue('is'), new AttributeValue('really'), new AttributeValue('cool')],
    ),
]);

echo $entityAttributes->toXML()->ownerDocument?->saveXML();
