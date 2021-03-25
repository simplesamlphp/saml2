#!/usr/bin/env php
<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

ContainerSingleton::setContainer(new MockContainer());

$publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);
$nid = new NameID('very secret');
$eid = EncryptedID::fromUnencryptedElement($nid, $publicKey);

$issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
$subject = new Subject($eid);

$authnRequest = new AuthnRequest(
    null,
    $subject,
    null,
    null,
    null,
    null,
    null,
    null,
    null,
    null,
    null,
    $issuer,
    "123",
    null,
    'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO'
);

$authnRequest = $authnRequest->toXML();

echo $authnRequest->ownerDocument->saveXML();
