#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use DateTimeImmutable;
use DateTimeZone;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$container = new MockContainer();
$container->setBlacklistedAlgorithms(null);
ContainerSingleton::setContainer($container);

$encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
    C::KEY_TRANSPORT_OAEP,
    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
);

$nid = new NameID(SAMLStringValue::fromString('very secret'));
$eid = new EncryptedID($nid->encrypt($encryptor));

$issuer = new Issuer(SAMLStringValue::fromString('https://gateway.example.org/saml20/sp/metadata'));
$subject = new Subject($eid);

$authnRequest = new AuthnRequest(
    subject: $subject,
    issueInstant: SAMLDateTimeValue::fromDateTime(new DateTimeImmutable('now', new DateTimeZone('Z'))),
    issuer: $issuer,
    id: IDValue::fromString('123'),
    destination: SAMLAnyURIValue::fromString('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO'),
);

$authnRequest = $authnRequest->toXML();

echo $authnRequest->ownerDocument->saveXML();
