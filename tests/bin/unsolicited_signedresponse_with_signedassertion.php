#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
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
    status: new Status(new StatusCode(C::STATUS_SUCCESS)),
    issuer: new Issuer('https://IdentityProvider.com'),
    issueInstant: new DateTimeImmutable('now', new DateTimeZone('Z')),
    id: 'abc123',
    //inResponseTo: 'PHPUnit',
    destination: C::ENTITY_OTHER,
    consent: C::ENTITY_SP,
    assertions: [$signedAssertion],
);

$responseSigner = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA512,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
);

$unsignedResponse->sign($responseSigner);
$signedResponse = $unsignedResponse->toXML($signedResponse);

$xmlRepresentation = $signedResponse->ownerDocument->saveXML($signedResponse);
echo $xmlRepresentation . PHP_EOL;
echo base64_encode($xmlRepresentation) . PHP_EOL;
