#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\EncryptedAttribute;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$container = new MockContainer();
$container->setBlacklistedAlgorithms(null);
ContainerSingleton::setContainer($container);

$encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
    C::KEY_TRANSPORT_OAEP,
    PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY)
);
$attribute = new Attribute(
    Name: 'urn:encrypted:attribute',
    AttributeValues: [new AttributeValue('very secret data')],
);
$encAttribute = new EncryptedAttribute($attribute->encrypt($encryptor));

echo $encAttribute->toXML()->ownerDocument->saveXML();
