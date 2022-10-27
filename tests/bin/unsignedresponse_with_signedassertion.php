#!/usr/bin/env php
<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$document = DOMDocumentFactory::fromFile(dirname(dirname(__FILE__)) . '/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->sign($signer);

$unsignedResponse = new Response(
    new Status(new StatusCode(C::STATUS_SUCCESS)),
    new Issuer('https://IdentityProvider.com'),
    'abc123',
    null,
    'PHPUnit',
    C::ENTITY_OTHER,
    C::ENTITY_SP,
    null,
    [$unsignedAssertion]
);

echo $unsignedResponse->toXML()->ownerDocument->saveXML();
