#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\samlp\LogoutRequest;
use SimpleSAML\SAML2\XML\samlp\SessionIndex;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$container = new MockContainer();
$container->setBlacklistedAlgorithms(null);
ContainerSingleton::setContainer($container);

$encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
    C::KEY_TRANSPORT_OAEP,
    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)
);
$nid = new NameID('very secret');
$eid = new EncryptedID($nid->encrypt($encryptor));

$logoutRequest = new LogoutRequest(
    identifier: $eid,
    sessionIndexes: [new SessionIndex('SomeSessionIndex1'), new SessionIndex('SomeSessionIndex2')],
    issuer: new Issuer('urn:test:TheIssuer')
);

$logoutRequest = $logoutRequest->toXML();

echo $logoutRequest->ownerDocument->saveXML();
