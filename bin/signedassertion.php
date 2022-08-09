#!/usr/bin/env php
<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;

$document = DOMDocumentFactory::fromFile(dirname(dirname(__FILE__)) . '/tests/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);

$key = PrivateKey::fromFile('../vendor/simplesamlphp/xml-security' . PEMCertificatesMock::CERTIFICATE_DIR_RSA . '/' . PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY);
$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    $key
);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->sign($signer);
echo $unsignedAssertion->toXML()->ownerDocument->saveXML();
