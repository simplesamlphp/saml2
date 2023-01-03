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
    new Issuer('testIssuer'),
    '_93af655219464fb403b34436cfb0c5cb1d9a5502',
    null,
    new Subject(new NameID('some:entity', null, null, C::NAMEID_ENTITY)),
    new Conditions(
        null,
        null,
        [],
        [
            new AudienceRestriction([
                new Audience('https://simplesamlphp.org/idp/metadata'),
                new Audience('urn:x-simplesamlphp:phpunit:entity')
            ]),
        ]
    ),
    [new AttributeStatement([
        new Attribute(
            'urn:mace:dir:attribute-def:uid',
            C::NAMEFORMAT_URI,
            null,
            [new AttributeValue('student2')]
        ),
        new Attribute(
            'urn:mace:terena.org:attribute-def:schacHomeOrganization',
            C::NAMEFORMAT_URI,
            null,
            [new AttributeValue('university.example.org'), new AttributeValue('bbb.cc')]
        ),
        new Attribute(
            'urn:schac:attribute-def:schacPersonalUniqueCode',
            C::NAMEFORMAT_URI,
            null,
            [
                new AttributeValue('urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020'),
                new AttributeValue('urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345')
            ]
        ),
        new Attribute(
            'urn:mace:dir:attribute-def:eduPersonAffiliation',
            C::NAMEFORMAT_URI,
            null,
            [new AttributeValue('member'), new AttributeValue('student')]
        ),
    ])],
);
$unsignedAssertion->sign($signer);
$signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());
$entityAttributes = new EntityAttributes([
    new Attribute(
        'attrib1',
        C::NAMEFORMAT_URI,
        null,
        [new AttributeValue('is'), new AttributeValue('really'), new AttributeValue('cool')]
    ),
    $signedAssertion,
    new Attribute(
        'foo',
        'urn:simplesamlphp:v1:simplesamlphp',
        null,
        [new AttributeValue('is'), new AttributeValue('really'), new AttributeValue('cool')]
    ),
]);

echo $entityAttributes->toXML()->ownerDocument?->saveXML();
