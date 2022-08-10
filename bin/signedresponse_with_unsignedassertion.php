#!/usr/bin/env php
<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;

$document = DOMDocumentFactory::fromFile(dirname(dirname(__FILE__)) . '/tests/resources/xml/saml_Assertion.xml');
$unsignedAssertion = Assertion::fromXML($document->documentElement);

$unsignedResponse = new Response(
    new Status(new StatusCode(Constants::STATUS_SUCCESS)),
    new Issuer('https://IdentityProvider.com'),
    'abc123',
    null,
    '123456',
    'urn:some:destination',
    'urn:some:sp',
    null,
    [$unsignedAssertion]
);

$otherKey = PrivateKey::fromFile('../vendor/simplesamlphp/xml-security' . PEMCertificatesMock::CERTIFICATE_DIR_RSA . '/' . PEMCertificatesMock::OTHER_PRIVATE_KEY);
$responseSigner = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA512,
    $otherKey
);

$unsignedResponse->sign($responseSigner);
echo $unsignedResponse->toXML()->ownerDocument->saveXML();
