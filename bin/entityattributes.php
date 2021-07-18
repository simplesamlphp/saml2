#!/usr/bin/env php
<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

$attribute1 = new Attribute(
    'attrib1',
    Constants::NAMEFORMAT_URI,
    null,
    [
        new AttributeValue('is'),
        new AttributeValue('really'),
        new AttributeValue('cool'),
    ]
);

$attribute2 = new Attribute(
    'foo',
    'urn:simplesamlphp:v1:simplesamlphp',
    null,
    [
        new AttributeValue('is'),
        new AttributeValue('really'),
        new AttributeValue('cool')
    ]
);

$unsignedAssertion = new Assertion(
    new Issuer('Provider'),
    '_93af655219464fb403b34436cfb0c5cb1d9a5502',
    1457707995,
    new Subject(
        new NameID('s00000000:123456789', null, null, Constants::NAMEID_PERSISTENT)
    ),
    null, // Conditions
    [
        new AttributeStatement(
            [$attribute1]
        )
    ]
);

$privateKey = PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY);
$unsignedAssertion->setSigningKey($privateKey);
$unsignedAssertion->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)]);

$ea = new EntityAttributes([$attribute1, $unsignedAssertion, $attribute2]);
echo strval($ea->toXML()->ownerDocument->saveXML());

