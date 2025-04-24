#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$container = new MockContainer();
$container->setBlacklistedAlgorithms(null);
ContainerSingleton::setContainer($container);

$encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
    C::KEY_TRANSPORT_OAEP_MGF1P,
    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
);

$document = DOMDocumentFactory::fromFile(dirname(__FILE__, 2) . '/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);
$eassertion = new EncryptedAssertion($assertion->encrypt($encryptor));

echo $eassertion->toXML()->ownerDocument->saveXML();
