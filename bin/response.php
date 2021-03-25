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
use SimpleSAML\XMLSecurity\XMLSecurityKey;

$document = DOMDocumentFactory::fromFile(dirname(dirname(__FILE__)) . '/tests/resources/xml/saml_Assertion.xml');
$assertion = Assertion::fromXML($document->documentElement);

$privateKey = PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->setSigningKey($privateKey);
$unsignedAssertion->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)]);

$unsignedResponse = new Response(
    new Status(new StatusCode(Constants::STATUS_SUCCESS)),
    new Issuer('https://IdentityProvider.com'),
    'abc123',
    null,
    '123456',
    null,
    'urn:some:sp',
    null,
    [$unsignedAssertion]
);
$unsignedResponse->setSigningKey($privateKey);
$unsignedResponse->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)]);

$signedResponse = $unsignedResponse->toXML();
echo $signedResponse->ownerDocument->saveXML();
