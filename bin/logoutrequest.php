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
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PublicKey;

ContainerSingleton::setContainer(new MockContainer());

$encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
    C::KEY_TRANSPORT_OAEP,
    PublicKey::fromFile(
        '../vendor/simplesamlphp/xml-security'
        . PEMCertificatesMock::CERTIFICATE_DIR_RSA
        . '/'
        . PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY
    )
);
$nid = new NameID('very secret');
$eid = $nid->encrypt($encryptor);

$logoutRequest = new LogoutRequest(
    $eid,
    null,
    null,
    ['SomeSessionIndex1', 'SomeSessionIndex2'],
    new Issuer('TheIssuer')
);

$logoutRequest = $logoutRequest->toXML();

echo $logoutRequest->ownerDocument->saveXML();
