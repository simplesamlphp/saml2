#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$assertionSigner = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::OTHER_PRIVATE_KEY),
);

$document = DOMDocumentFactory::fromFile(dirname(__FILE__, 2) . '/resources/xml/saml_Assertion.xml');
$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->sign($assertionSigner);
$signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());

$unsignedResponse = new Response(
    new Status(new StatusCode(C::STATUS_SUCCESS)),
    new Issuer('https://IdentityProvider.com'),
    'abc123',
    null,
    'PHPUnit',
    C::ENTITY_OTHER,
    C::ENTITY_SP,
    null,
    [$signedAssertion]
);

$responseSigner = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA512,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
);

$unsignedResponse->sign($responseSigner);
echo $unsignedResponse->toXML()->ownerDocument->saveXML();
