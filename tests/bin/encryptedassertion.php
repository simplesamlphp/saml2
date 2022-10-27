#!/usr/bin/env php
<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

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
    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)
);

$document = DOMDocumentFactory::fromFile(dirname(dirname(__FILE__)) . '/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);
$eassertion = new EncryptedAssertion($assertion->encrypt($encryptor));

echo $eassertion->toXML()->ownerDocument->saveXML();
