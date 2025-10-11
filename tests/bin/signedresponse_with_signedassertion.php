#!/usr/bin/env php
<?php

require_once(dirname(__FILE__, 3) . '/vendor/autoload.php');

use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\NCNameValue;
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
    status: new Status(
        new StatusCode(SAMLAnyURIValue::fromString(C::STATUS_SUCCESS)),
    ),
    issuer: new Issuer(SAMLStringValue::fromString('https://IdentityProvider.com')),
    issueInstant: SAMLDateTimeValue::fromDateTime(new DateTimeImmutable('now', new DateTimeZone('Z'))),
    id: IDValue::fromString('abc123'),
    inResponseTo: NCNameValue::fromString('PHPUnit'),
    destination: SAMLAnyURIValue::fromString(C::ENTITY_OTHER),
    consent: SAMLAnyURIValue::fromString(C::ENTITY_SP),
    assertions: [$signedAssertion],
);

$responseSigner = (new SignatureAlgorithmFactory())->getAlgorithm(
    C::SIG_RSA_SHA512,
    PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
);

$unsignedResponse->sign($responseSigner);
echo $unsignedResponse->toXML()->ownerDocument->saveXML();
