#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$container = new MockContainer();
$container->setBlacklistedAlgorithms(null);
ContainerSingleton::setContainer($container);

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$nid = new NameID(SAMLStringValue::fromString('test@example.org'));

$issuer = new Issuer(SAMLStringValue::fromString('https://gateway.example.org/saml20/sp/metadata'));
$subject = new Subject($nid);

$authnRequest = new AuthnRequest(
    subject: $subject,
    issueInstant: SAMLDateTimeValue::fromDateTime(new DateTimeImmutable('now', new DateTimeZone('Z'))),
    issuer: $issuer,
    id: IDValue::fromString('phpunit'),
    destination: SAMLAnyURIValue::fromString('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO'),
);

$unsignedAuthnRequest = $authnRequest->toXML();

echo $unsignedAuthnRequest->ownerDocument->saveXML();

$authnRequest->sign($signer);
$signedAuthnRequest = $authnRequest->toXML();

echo $signedAuthnRequest->ownerDocument->saveXML();
