#!/usr/bin/env php
<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\samlp\LogoutRequest;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

ContainerSingleton::setContainer(new MockContainer());

$publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_OAEP_MGF1P, PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);
$nid = new NameID('very secret');
$eid = EncryptedID::fromUnencryptedElement($nid, $publicKey);

$logoutRequest = new LogoutRequest(
    $eid,
    null,
    null,
    ['SomeSessionIndex1', 'SomeSessionIndex2'],
    new Issuer('TheIssuer')
);

$logoutRequest = $logoutRequest->toXML();

echo strval($logoutRequest->ownerDocument->saveXML());
