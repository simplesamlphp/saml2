<?php

declare(strict_types=1);

namespace SAML2;

use Exception;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class \SAML2\SignedElementHelperTest
 */
class SignedElementHelperTest extends TestCase
{
    /**
     * First check that we are able to validate with no modifications.
     *
     * To do this we first need to copy the element and add it to it's own document again
     * @todo explain why we need to copy the element?
     * @return void
     */
    public function testValidateWithoutModification(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(__FILE__) . '/signedassertion.xml');
        $assertion = new Assertion($document->documentElement);
        $this->assertTrue($assertion->validate(CertificatesMock::getPublicKeySha256()));
    }


    /**
     * Test the modification of references.
     * @return void
     */
    public function testValidateWithInvalidDigestValue(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(__FILE__) . '/signedassertion.xml');
        $digestValueElements = Utils::xpQuery(
            $document->documentElement,
            '/saml_assertion:Assertion/ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue'
        );
        $this->assertCount(1, $digestValueElements);
        $digest = $digestValueElements[0]->firstChild->textContent;
        $digest[0] = '4';
        $digestValueElements[0]->firstChild->textContent = $digest;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference validation failed');
        new Assertion($document->documentElement);
    }


    /**
     * Test that signatures no longer validate if the value has been tampered with.
     * @return void
     */
    public function testValidateWithValueTampering(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(__FILE__) . '/signedassertion.xml');
        $signatureValues = Utils::xpQuery(
            $document->documentElement,
            '/saml_assertion:Assertion/ds:Signature/ds:SignatureValue'
        );
        $this->assertCount(1, $signatureValues);
        $value = $signatureValues[0]->firstChild->textContent;
        $value[0] = 'a';
        $signatureValues[0]->firstChild->textContent = $value;
        $assertion = new Assertion($document->documentElement);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');
        $assertion->validate(CertificatesMock::getPublicKeySha256());
    }


    /**
     * Test that signatures fail to validate if the signed content has been tampered with.
     */
    public function testValidateWithTamperedDocument(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(__FILE__) . '/signedassertion_tampered.xml');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference validation failed');
        new Assertion($document->documentElement);
    }


    /**
     * Test that signatures fail to validate if the signed content and its corresponding digest have been tampered with.
     */
    public function testValidateWithTamperedDocumentAndDigest(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(__FILE__) . '/signedassertion_tampered.xml');
        $digestValueElements = Utils::xpQuery(
            $document->documentElement,
            '/saml_assertion:Assertion/ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue'
        );
        $digestValueElements[0]->firstChild->textContent = 'QYezJRRNgVe8996u09gVs+FLygU=';
        $assertion = new Assertion($document->documentElement);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');
        $assertion->validate(CertificatesMock::getPublicKeySha256());
    }


    /**
     * Test that validating with the wrong key fails.
     */
    public function testValidateWithWrongKey(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(__FILE__) . '/signedassertion.xml');
        $assertion = new Assertion($document->documentElement);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');
        $assertion->validate(CertificatesMock::getPublicKey2Sha256());
    }


    /**
     * Test that signing works
     */
    public function testSign(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" ID="_d908a49b8b63665738430d1c5b655f297b91331864" Version="2.0" IssueInstant="2016-03-11T14:53:15Z">
  <saml:Issuer>https://thki-sid.pt-48.utr.surfcloud.nl/ssp/saml2/idp/metadata.php</saml:Issuer>
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

        $privateKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
        $privateKey->loadKey(CertificatesMock::PRIVATE_KEY_PEM);

        $assertion = new Assertion($document->documentElement);
        $assertion->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);
        $assertion->setSignatureKey($privateKey);
        $xml = $assertion->toXML();

        $assertion2 = new Assertion($xml);
        $this->assertTrue($assertion2->validate(CertificatesMock::getPublicKeySha256()));
        $this->assertEquals([CertificatesMock::getPlainPublicKeyContents()], $assertion2->getCertificates());
    }
}
