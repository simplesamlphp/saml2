#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$document = DOMDocumentFactory::fromFile(dirname(__FILE__, 2) . '/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->sign($signer);
echo str_replace(
    'SomeNameIDValue',
    'SomeNameID<!-- some random comment-->Value',
    strval($unsignedAssertion->toXML()->ownerDocument->saveXML()),
);
