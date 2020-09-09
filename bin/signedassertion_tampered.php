#!/usr/bin/env php
<?php

require_once(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

// @TODO: use the xml-file from tests/resources/xml/saml_assertion.xml instead
$document = DOMDocumentFactory::fromString(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" ID="_d908a49b8b63665738430d1c5b655f297b91331864" Version="2.0" IssueInstant="2016-03-11T14:53:15Z">
  <saml:Issuer>https://thki-sid.pt-48.utr.surfcloud.nl/ssp/saml2/idp/metadata.php</saml:Issuer>
  <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <ds:SignedInfo>
      <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
      <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
      <ds:Reference URI="#_d908a49b8b63665738430d1c5b655f297b91331864">
        <ds:Transforms>
          <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
          <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
        </ds:Transforms>
        <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
        <ds:DigestValue>3T1G7tVq5t3vYQEHerp8sRWakxs=</ds:DigestValue>
      </ds:Reference>
    </ds:SignedInfo>
    <ds:SignatureValue>pdcWOeAtYOnCAXSt/bTwtFHRM1Qk23NI85NMDXwUPpwC8RJ939fmleb3OL12LyO1lkOq7a8JRV/Qmav5CPapYMVMkNpN8Brz731rBcP9cPzcuXumP7h4m7HjxuAo4C+V6q6l981cuf/THsrX2PfG/+SFwwYzwAECb7JSHYFnoRc=</ds:SignatureValue>
    <ds:KeyInfo>
      <ds:X509Data>
        <ds:X509Certificate>MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMCTk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYDVQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xiZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2ZlaWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5vMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7nbK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2QarQ4/67OZfHd7R+POBXhophSMv1ZOo</ds:X509Certificate>
      </ds:X509Data>
    </ds:KeyInfo>
  </ds:Signature>
  <saml:Subject>
    <saml:NameID SPNameQualifier="https://engine.test.surfconext.nl/authentication/sp/metadata" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">_1bbcf227253269d19a689c53cdd542fe2384a9538b</saml:NameID>
    <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
      <saml:SubjectConfirmationData NotOnOrAfter="2016-03-11T14:58:15Z" Recipient="https://engine.test.surfconext.nl/authentication/sp/consume-assertion" InResponseTo="CORTO6e667c685720477499c07c3864ac257271f1a212"/>
    </saml:SubjectConfirmation>
  </saml:Subject>
  <saml:Conditions NotBefore="2016-03-11T14:52:45Z" NotOnOrAfter="2016-03-11T14:58:15Z">
    <saml:AudienceRestriction>
      <saml:Audience>https://engine.test.surfconext.nl/authentication/sp/metadata</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2016-03-11T14:53:15Z" SessionNotOnOrAfter="2016-03-11T22:53:15Z" SessionIndex="_a2576e3e285e9e4d676b40b6c695b4a3cdc16ebd8b">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml:AuthnContextClassRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
  <saml:AttributeStatement>
    <saml:Attribute Name="urn:mace:dir:attribute-def:uid" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
      <saml:AttributeValue xsi:type="xs:string">student2</saml:AttributeValue>
    </saml:Attribute>
    <saml:Attribute Name="urn:mace:terena.org:attribute-def:schacHomeOrganization" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
      <saml:AttributeValue xsi:type="xs:string">university.example.org</saml:AttributeValue>
      <saml:AttributeValue xsi:type="xs:string">bbb.cc</saml:AttributeValue>
    </saml:Attribute>
    <saml:Attribute Name="urn:schac:attribute-def:schacPersonalUniqueCode" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
      <saml:AttributeValue xsi:type="xs:string">urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020</saml:AttributeValue>
      <saml:AttributeValue xsi:type="xs:string">urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345</saml:AttributeValue>
    </saml:Attribute>
    <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonAffiliation" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
      <saml:AttributeValue xsi:type="xs:string">member</saml:AttributeValue>
      <saml:AttributeValue xsi:type="xs:string">student</saml:AttributeValue>
    </saml:Attribute>
  </saml:AttributeStatement>
</saml:Assertion>
XML
);

$privateKey = PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY);

$unsignedAssertion = Assertion::fromXML($document->documentElement);
$unsignedAssertion->setSigningKey($privateKey);
$unsignedAssertion->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)]);
$signedAssertion = $unsignedAssertion->toXML();


echo str_replace('student2', 'student3', strval($signedAssertion->ownerDocument->saveXML()));
