#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use DateTimeImmutable;
use DateTimeZone;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

$document = DOMDocumentFactory::fromFile(dirname(__FILE__, 2) . '/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);

$signer = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA256,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->sign($signer);

$unsignedResponse = new Response(
    issueInstant: new DateTimeImmutable('now', new DateTimeZone('Z')),
    status: new Status(new StatusCode(C::STATUS_SUCCESS)),
    issuer: new Issuer('https://IdentityProvider.com'),
    id: 'abc123',
    inResponseTo: 'PHPUnit',
    destination: C::ENTITY_OTHER,
    consent: C::ENTITY_SP,
    assertions: [$unsignedAssertion],
);

echo $unsignedResponse->toXML()->ownerDocument->saveXML();
