<?php

namespace SAML2;

use SAML2\XML\Chunk;

/**
 * Class \SAML2\AssertionTest
 */
class AssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test to build a basic assertion
     */
    public function testMarshalling()
    {
        // Create an assertion
        $assertion = new Assertion();
        $assertion->setIssuer('testIssuer');
        $assertion->setValidAudiences(array('audience1', 'audience2'));
        $assertion->setAuthnContext('someAuthnContext');

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $issuerElements = Utils::xpQuery($assertionElement, './saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);

        // Test for an AudienceRestriction
        $audienceElements = Utils::xpQuery(
            $assertionElement,
            './saml_assertion:Conditions/saml_assertion:AudienceRestriction/saml_assertion:Audience'
        );
        $this->assertCount(2, $audienceElements);
        $this->assertEquals('audience1', $audienceElements[0]->textContent);
        $this->assertEquals('audience2', $audienceElements[1]->textContent);

        // Test for an Authentication Context
        $authnContextElements = Utils::xpQuery(
            $assertionElement,
            './saml_assertion:AuthnStatement/saml_assertion:AuthnContext/saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('someAuthnContext', $authnContextElements[0]->textContent);
    }

    /**
     * Test to parse a basic assertion
     */
    public function testUnmarshalling()
    {
        // Unmarshall an assertion
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $assertion = new Assertion($document->firstChild);

        // Was not signed
        $this->assertFalse($assertion->getWasSignedAtConstruction());

        // Test for valid audiences
        $assertionValidAudiences = $assertion->getValidAudiences();
        $this->assertCount(2, $assertionValidAudiences);
        $this->assertEquals('audience1', $assertionValidAudiences[0]);
        $this->assertEquals('audience2', $assertionValidAudiences[1]);

        // Test for Authenticating Authorities
        $assertionAuthenticatingAuthorities = $assertion->getAuthenticatingAuthority();
        $this->assertCount(2, $assertionAuthenticatingAuthorities);
        $this->assertEquals('someIdP1', $assertionAuthenticatingAuthorities[0]);
        $this->assertEquals('someIdP2', $assertionAuthenticatingAuthorities[1]);
    }

    /**
     * Test an assertion with lots of options
     */
    public function testMarshallingUnmarshallingChristmas()
    {
        // Create an assertion
        $assertion = new Assertion();

        $assertion->setIssuer('testIssuer');
        $assertion->setValidAudiences(array('audience1', 'audience2'));

        // deprecated function
        $this->assertNull($assertion->getAuthnContext());

        $assertion->setAuthnContext('someAuthnContext');
        $assertion->setAuthnContextDeclRef('/relative/path/to/document.xml');

        $assertion->setID("_123abc");

        $assertion->setIssueInstant(1234567890);
        $assertion->setAuthnInstant(1234567890 - 1);
        $assertion->setNotBefore(1234567890 - 10);
        $assertion->setNotOnOrAfter(1234567890 + 100);
        $assertion->setSessionNotOnOrAfter(1234568890 + 200);

        $assertion->setSessionIndex("idx1");

        $assertion->setAuthenticatingAuthority(array("idp1","idp2"));

        $assertion->setAttributes(array(
            "name1" => array("value1","value2"),
            "name2" => array(2),
            "name3" => array(null)));
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");

        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($assertionElement)->firstChild);

        $this->assertEquals('/relative/path/to/document.xml', $assertionToVerify->getAuthnContextDeclRef());
        $this->assertEquals('_123abc', $assertionToVerify->getId());
        $this->assertEquals(1234567890, $assertionToVerify->getIssueInstant());
        $this->assertEquals(1234567889, $assertionToVerify->getAuthnInstant());
        $this->assertEquals(1234567880, $assertionToVerify->getNotBefore());
        $this->assertEquals(1234567990, $assertionToVerify->getNotOnOrAfter());
        $this->assertEquals(1234569090, $assertionToVerify->getSessionNotOnOrAfter());

        $this->assertEquals('idx1', $assertionToVerify->getSessionIndex());

        $authauth = $assertionToVerify->getAuthenticatingAuthority();
        $this->assertCount(2, $authauth);
        $this->assertEquals("idp2", $authauth[1]);

        $attributes = $assertionToVerify->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertCount(2, $attributes['name1']);
        $this->assertEquals("value1", $attributes['name1'][0]);
        $this->assertEquals(2, $attributes['name2'][0]);
        // NOTE: nil attribute is currently parsed as string..
        //$this->assertNull($attributes["name3"][0]);
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified", $assertionToVerify->getAttributeNameFormat());
    }

    /**
     * Test an assertion attribute value types options
     */
    public function testMarshallingUnmarshallingAttributeValTypes()
    {
        // Create an assertion
        $assertion = new Assertion();

        $assertion->setIssuer('testIssuer');
        $assertion->setValidAudiences(array('audience1', 'audience2'));

        $assertion->setAuthnContext('someAuthnContext');

        $assertion->setAuthenticatingAuthority(array("idp1","idp2"));

        $assertion->setAttributes(array(
            "name1" => array("value1",123,"2017-31-12"),
            "name2" => array(2),
            "name3" => array(1234,"+2345")));
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");

        // set xs:type for first and third name1 values, and all name3 values.
        // second name1 value and all name2 values will use default behaviour
        $assertion->setAttributesValueTypes(array(
            "name1" => array("xs:string",null,"xs:date"),
            "name3" => "xs:decimal"));

        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($assertionElement)->firstChild);

        $authauth = $assertionToVerify->getAuthenticatingAuthority();
        $this->assertCount(2, $authauth);
        $this->assertEquals("idp2", $authauth[1]);

        $attributes = $assertionToVerify->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertCount(3, $attributes['name1']);
        $this->assertEquals("value1", $attributes['name1'][0]);
        $this->assertEquals(123, $attributes['name1'][1]);
        $this->assertEquals("2017-31-12", $attributes['name1'][2]);
        $this->assertEquals(2, $attributes['name2'][0]);
        $this->assertCount(2, $attributes['name3']);
        $this->assertEquals("1234", $attributes['name3'][0]);
        $this->assertEquals("+2345", $attributes['name3'][1]);
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified", $assertionToVerify->getAttributeNameFormat());

        $attributesValueTypes = $assertionToVerify->getAttributesValueTypes();
        $this->assertCount(3, $attributesValueTypes);
        $this->assertCount(3, $attributesValueTypes['name1']);
        $this->assertEquals("xs:string", $attributesValueTypes['name1'][0]);
        $this->assertEquals("xs:integer", $attributesValueTypes['name1'][1]);
        $this->assertEquals("xs:date", $attributesValueTypes['name1'][2]);
        $this->assertCount(1, $attributesValueTypes['name2']);
        $this->assertEquals("xs:integer", $attributesValueTypes['name2'][0]);
        $this->assertCount(2, $attributesValueTypes['name3']);
        $this->assertEquals("xs:decimal", $attributesValueTypes['name3'][0]);
        $this->assertEquals("xs:decimal", $attributesValueTypes['name3'][1]);
    }

    /**
     * Test attribute value types check in Marshalling an assertion.
     */

    public function testMarshallingWrongAttributeValTypes()
    {
        // Create an assertion
        $assertion = new Assertion();

        $assertion->setIssuer('testIssuer');
        $assertion->setValidAudiences(array('audience1', 'audience2'));

        $assertion->setAuthnContext('someAuthnContext');

        $assertion->setAuthenticatingAuthority(array("idp1","idp2"));

        $assertion->setAttributes(array(
            "name1" => array("value1","2017-31-12"),
            "name2" => array(2),
            "name3" => array(1234,"+2345")));
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");

        // set wrong number elements in name1
        $assertion->setAttributesValueTypes(array(
            "name1" => array("xs:string"),
            "name3" => "xs:decimal"));

        $this->setExpectedException('Exception', "Array of value types and array of values have different size for attribute 'name1'");
        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();
    }

    /**
     * Test parsing AuthnContext elements Decl and ClassRef
     */
    public function testAuthnContextDeclAndClassRef()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDecl>
        <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
        </samlac:AuthenticationContextDeclaration>
      </saml:AuthnContextDecl>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        // Try with unmarshalling
        $document = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->documentElement);
        $authnContextDecl = $assertion->getAuthnContextDecl();
        $this->assertNotEmpty($authnContextDecl);
        $this->assertEquals('AuthnContextDecl', $authnContextDecl->localName);
        $childLocalName = $authnContextDecl->getXML()->childNodes->item(1)->localName;
        $this->assertEquals('AuthenticationContextDeclaration', $childLocalName);

        $this->assertEquals('someAuthnContext', $assertion->getAuthnContextClassRef());

        // deprecated function
        $this->assertEquals('someAuthnContext', $assertion->getAuthnContext());
    }

    /**
     * Test parsing AuthnContext elements DeclRef and ClassRef
     */
    public function testAuthnContextDeclRefAndClassRef()
    {
        // Try with unmarshalling
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->documentElement);
        $this->assertEquals('/relative/path/to/document.xml', $assertion->getAuthnContextDeclRef());
        $this->assertEquals('someAuthnContext', $assertion->getAuthnContextClassRef());
    }

    /**
     * Test setting an AuthnContextDecl chunk.
     */
    public function testSetAuthnContextDecl()
    {
        $xml = <<<XML
<samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
</samlac:AuthenticationContextDeclaration>
XML;

        $document  = DOMDocumentFactory::fromString($xml);
        $assertion = new Assertion();

        $assertion->setAuthnContextDecl(new Chunk($document->documentElement));
        $documentParent  = DOMDocumentFactory::fromString("<root />");
        $assertionElement = $assertion->toXML($documentParent->firstChild);

        $acElements = Utils::xpQuery($assertionElement, './saml_assertion:AuthnStatement/saml_assertion:AuthnContext');
        $this->assertCount(1, $acElements);
        $this->assertEquals('samlac:AuthenticationContextDeclaration', $acElements[0]->firstChild->tagName);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:ac', $acElements[0]->firstChild->namespaceURI);
    }


    public function testAuthnContextDeclAndRefConstraint()
    {
        $xml = <<<XML
<samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
</samlac:AuthenticationContextDeclaration>
XML;

        $document  = DOMDocumentFactory::fromString($xml);
        $assertion = new Assertion();

        $e = null;
        try {
            $assertion->setAuthnContextDecl(new Chunk($document->documentElement));
            $assertion->setAuthnContextDeclRef('/relative/path/to/document.xml');
        } catch (\Exception $e) {
        }
        $this->assertNotEmpty($e);

        // Try again in reverse order for good measure.
        $assertion = new Assertion();

        $e = null;
        try {
            $assertion->setAuthnContextDeclRef('/relative/path/to/document.xml');
            $assertion->setAuthnContextDecl(new Chunk($document->documentElement));
        } catch (\Exception $e) {
        }
        $this->assertNotEmpty($e);

        // Try with unmarshalling
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextDecl>
        <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
        </samlac:AuthenticationContextDeclaration>
      </saml:AuthnContextDecl>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $e = null;
        try {
            new Assertion($document->documentElement);
        } catch (\Exception $e) {
        }
        $this->assertNotEmpty($e);

        // deprecated function
        $this->assertEquals('/relative/path/to/document.xml', $assertion->getAuthnContext());
    }

    public function testMustHaveClassRefOrDeclOrDeclRef()
    {
        // Unmarshall an assertion
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );
        $e = null;
        try {
            $assertion = new Assertion($document->firstChild);
        } catch (\Exception $e) {
        }
        $this->assertNotEmpty($e);
    }

    /**
     * Tests that AuthnContextDeclRef is not mistaken for AuthnContextClassRef.
     *
     * This tests against reintroduction of removed behavior.
     */
    public function testNoAuthnContextDeclRefFallback()
    {
        $authnContextDeclRef = 'relative/url/to/authcontext.xml';

        // Unmarshall an assertion
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                 IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextDeclRef>$authnContextDeclRef</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML
        );
        $assertion = new Assertion($document->firstChild);
        $this->assertEmpty($assertion->getAuthnContextClassRef());
        $this->assertEquals($authnContextDeclRef, $assertion->getAuthnContextDeclRef());
    }

    public function testHasEncryptedAttributes()
    {
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">s00000000:123456789</saml:NameID>
        <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
          <saml:SubjectConfirmationData NotOnOrAfter="2011-08-31T08:51:05Z" Recipient="https://sp.example.com/assertion_consumer" InResponseTo="_13603a6565a69297e9809175b052d115965121c8" />
        </saml:SubjectConfirmation>
      </saml:Subject>
      <saml:Conditions NotOnOrAfter="2011-08-31T08:51:05Z" NotBefore="2011-08-31T08:51:05Z">
        <saml:AudienceRestriction>
          <saml:Audience>ServiceProvider</saml:Audience>
        </saml:AudienceRestriction>
      </saml:Conditions>
      <saml:AuthnStatement AuthnInstant="2011-08-31T08:51:05Z" SessionIndex="_93af655219464fb403b34436cfb0c5cb1d9a5502">
        <saml:AuthnContext>
          <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
        </saml:AuthnContext>
        <saml:SubjectLocality Address="127.0.0.1"/>
      </saml:AuthnStatement>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:ServiceID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
        <saml:EncryptedAttribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
          <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Type="http://www.w3.org/2001/04/xmlenc#Element" Id="_F39625AF68B4FC078CC7582D28D05D9C">
            <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes256-cbc"/>
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
              <xenc:EncryptedKey>
                <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>
                <ds:KeyInfo>
                  <ds:KeyName>62355fbd1f624503c5c9677402ecca00ef1f6277</ds:KeyName>
                </ds:KeyInfo>
                <xenc:CipherData>
                  <xenc:CipherValue>K0mBLxfLziKVUKEAOYe7D6uVSCPy8vyWVh3RecnPES+8QkAhOuRSuE/LQpFr0huI/iCEy9pde1QgjYDLtjHcujKi2xGqW6jkXW/EuKomqWPPA2xYs1fpB1su4aXUOQB6OJ70/oDcOsy834ghFaBWilE8fqyDBUBvW+2IvaMUZabwN/s9mVkWzM3r30tlkhLK7iOrbGAldIHwFU5z7PPR6RO3Y3fIxjHU40OnLsJc3xIqdLH3fXpC0kgi5UspLdq14e5OoXjLoPG3BO3zwOAIJ8XNBWY5uQof6KrKbcvtZSY0fMvPYhYfNjtRFy8y49ovL9fwjCRTDlT5+aHqsCTBrw==</xenc:CipherValue>
                </xenc:CipherData>
              </xenc:EncryptedKey>
            </ds:KeyInfo>
            <xenc:CipherData>
              <xenc:CipherValue>ZzCu6axGgAYZHVf77NX8apZKB/GJDeuV6bFByBS0AIgiXkvDUAmLCpabTAWBM+yz19olA6rryuOfr82ev2bzPNURvm4SYxahvuL4Pibn5wJky0Bl54VqmcU+Aqj0dAvOgqG1y3X4wO9n9bRsTv6921m0eqRAFph8kK8L9hirK1BxYBYj2RyFCoFDPxVZ5wyra3q4qmE4/ELQpFP6mfU8LXb0uoWJUjGUelS2Aa7bZis8zEpwov4CwtlNjltQih4mv7ttCAfYqcQIFzBTB+DAa0+XggxCLcdB3+mQiRcECBfwHHJ7gRmnuBEgeWT3CGKa3Nb7GMXOfuxFKF5pIehWgo3kdNQLalor8RVW6I8P/I8fQ33Fe+NsHVnJ3zwSA//a</xenc:CipherValue>
            </xenc:CipherData>
          </xenc:EncryptedData>
        </saml:EncryptedAttribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML
        );
        $assertion = new Assertion($document->firstChild);
        $this->assertTrue($assertion->hasEncryptedAttributes());
    }

    /**
     * @group Assertion
     */
    public function testCorrectSignatureMethodCanBeExtracted()
    {
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">s00000000:123456789</saml:NameID>
        <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
          <saml:SubjectConfirmationData NotOnOrAfter="2011-08-31T08:51:05Z" Recipient="https://sp.example.com/assertion_consumer" InResponseTo="_13603a6565a69297e9809175b052d115965121c8" />
        </saml:SubjectConfirmation>
      </saml:Subject>
      <saml:Conditions NotOnOrAfter="2011-08-31T08:51:05Z" NotBefore="2011-08-31T08:51:05Z">
        <saml:AudienceRestriction>
          <saml:Audience>ServiceProvider</saml:Audience>
        </saml:AudienceRestriction>
      </saml:Conditions>
      <saml:AuthnStatement AuthnInstant="2011-08-31T08:51:05Z" SessionIndex="_93af655219464fb403b34436cfb0c5cb1d9a5502">
        <saml:AuthnContext>
          <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
        </saml:AuthnContext>
        <saml:SubjectLocality Address="127.0.0.1"/>
      </saml:AuthnStatement>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:ServiceID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML
        );

        $privateKey = CertificatesMock::getPrivateKey();

        $unsignedAssertion = new Assertion($document->firstChild);
        $unsignedAssertion->setSignatureKey($privateKey);
        $unsignedAssertion->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));
        $this->assertFalse($unsignedAssertion->getWasSignedAtConstruction());
        $this->assertEquals($privateKey, $unsignedAssertion->getSignatureKey());

        $signedAssertion = new Assertion($unsignedAssertion->toXML());

        $signatureMethod = $signedAssertion->getSignatureMethod();

        $this->assertEquals($privateKey->getAlgorith(), $signatureMethod);

        $this->assertTrue($signedAssertion->getWasSignedAtConstruction());

    }

    public function testEptiAttributeValuesAreParsedCorrectly()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.10" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue>
                <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">abcd-some-value-xyz</saml:NameID>
            </saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonTargetedID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue>
                <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">abcd-some-value-xyz</saml:NameID>
            </saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);

        $attributes = $assertion->getAttributes();

        $maceValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $oidValue = $attributes['urn:oid:1.3.6.1.4.1.5923.1.1.1.10'][0];

        $this->assertInstanceOf('SAML2\XML\saml\NameID', $maceValue);
        $this->assertInstanceOf('SAML2\XML\saml\NameID', $oidValue);
        $this->assertEquals('abcd-some-value-xyz', $maceValue->value);
        $this->assertEquals('abcd-some-value-xyz', $oidValue->value);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceValue->Format);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $oidValue->Format);
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }

    public function testEptiAttributeValuesMustBeANameID()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.10" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
          <saml:AttributeValue>
            <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">abcd-some-value-xyz</saml:NameID>
          </saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonTargetedID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
          <saml:AttributeValue>
            <saml:Attribute Name="urn:some:custom:nested:element">
              <saml:AttributeValue>abcd-some-value-xyz</saml:AttributeValue>
            </saml:Attribute>
          </saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
          <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $this->setExpectedException(
            'SAML2\Exception\RuntimeException',
            'A "urn:mace:dir:attribute-def:eduPersonTargetedID" (EPTI) attribute value must be a NameID, '
            . 'none found for value no. "0"'
        );
        new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);
    }

    /**
     * as per http://software.internet2.edu/eduperson/internet2-mace-dir-eduperson-201310.html#eduPersonTargetedID
     * it is multivalued
     */
    public function testEptiAttributeParsingSupportsMultipleValues()
    {
        $xml
            = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonTargetedID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue>
                <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">abcd-some-value-xyz</saml:NameID>
            </saml:AttributeValue>
            <saml:AttributeValue>
                <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">xyz-some-value-abcd</saml:NameID>
            </saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);

        $attributes = $assertion->getAttributes();

        $maceFirstValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $maceSecondValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][1];

        $this->assertInstanceOf('SAML2\XML\saml\NameID', $maceFirstValue);
        $this->assertInstanceOf('SAML2\XML\saml\NameID', $maceSecondValue);
        $this->assertEquals('abcd-some-value-xyz', $maceFirstValue->value);
        $this->assertEquals('xyz-some-value-abcd', $maceSecondValue->value);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceFirstValue->Format);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceSecondValue->Format);

        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }

    public function testAttributeValuesWithComplexTypesAreParsedCorrectly()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
            <saml:Issuer>Provider</saml:Issuer>
            <saml:Conditions/>
            <saml:AttributeStatement>
              <saml:Attribute Name="urn:some:custom:outer:element" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
                <saml:AttributeValue>
                  <saml:Attribute Name="urn:some:custom:nested:element">
                    <saml:AttributeValue>abcd-some-value-xyz</saml:AttributeValue>
                  </saml:Attribute>
                </saml:AttributeValue>
              </saml:Attribute>
              <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
                <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
              </saml:Attribute>
            </saml:AttributeStatement>
            </saml:Assertion>
XML;

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);

        $attributes = $assertion->getAttributes();
        $this->assertInstanceOf(
            '\DOMNodeList',
            $attributes['urn:some:custom:outer:element'][0]
        );
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }

    public function testTypedAttributeValuesAreParsedCorrectly()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:some:string">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:some:integer">
            <saml:AttributeValue xsi:type="xs:integer">42</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);

        $attributes = $assertion->getAttributes();
        $this->assertInternalType('int', $attributes['urn:some:integer'][0]);
        $this->assertInternalType('string', $attributes['urn:some:string'][0]);
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }

    public function testEncryptedAttributeValuesWithComplexTypeValuesAreParsedCorrectly()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:some:custom:outer:element" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue>
                <saml:Attribute Name="urn:some:custom:nested:element">
                    <saml:AttributeValue>abcd-some-value-xyz</saml:AttributeValue>
                </saml:Attribute>
            </saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $privateKey = CertificatesMock::getPublicKey();

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);
        $assertion->setEncryptionKey($privateKey);
        $assertion->setEncryptedAttributes(true);
        $this->assertEquals($privateKey, $assertion->getEncryptionKey());

        $encryptedAssertion = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($encryptedAssertion)->firstChild);

        $this->assertTrue($assertionToVerify->hasEncryptedAttributes());

        $assertionToVerify->decryptAttributes(CertificatesMock::getPrivateKey());

        $attributes = $assertionToVerify->getAttributes();
        $this->assertInstanceOf(
            '\DOMNodeList',
            $attributes['urn:some:custom:outer:element'][0]
        );
        $this->assertXmlStringEqualsXmlString($xml, $assertionToVerify->toXML()->ownerDocument->saveXML());
    }

    public function testTypedEncryptedAttributeValuesAreParsedCorrectly()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:some:string">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:some:integer">
            <saml:AttributeValue xsi:type="xs:integer">42</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $privateKey = CertificatesMock::getPublicKey();

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);
        $assertion->setEncryptionKey($privateKey);
        $assertion->setEncryptedAttributes(true);
        $encryptedAssertion = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($encryptedAssertion)->firstChild);

        $this->assertTrue($assertionToVerify->hasEncryptedAttributes());

        $assertionToVerify->decryptAttributes(CertificatesMock::getPrivateKey());
        $attributes = $assertionToVerify->getAttributes();

        $this->assertInternalType('int', $attributes['urn:some:integer'][0]);
        $this->assertInternalType('string', $attributes['urn:some:string'][0]);
        $this->assertXmlStringEqualsXmlString($xml, $assertionToVerify->toXML()->ownerDocument->saveXML());
    }

    /**
     * Try to verify a signed assertion.
     */
    public function testVerifySignedAssertion()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/signedassertion.xml');

        $publicKey = CertificatesMock::getPublicKeySha1();

        $assertion = new Assertion($doc->firstChild);
        $result = $assertion->validate($publicKey);

        $this->assertTrue($result);
        // Double-check that we can actually retrieve some basics.
        $this->assertEquals("_d908a49b8b63665738430d1c5b655f297b91331864", $assertion->getId());
        $this->assertEquals("https://thki-sid.pt-48.utr.surfcloud.nl/ssp/saml2/idp/metadata.php", $assertion->getIssuer());
        $this->assertEquals("1457707995", $assertion->getIssueInstant());

        $certs = $assertion->getCertificates();
        $this->assertCount(1, $certs);
        $this->assertEquals(CertificatesMock::getPlainPublicKeyContents(), $certs[0]);

        // Was signed
        $this->assertTrue($assertion->getWasSignedAtConstruction());
    }

    /**
     * Try to verify a signed assertion in which a byte was changed after signing.
     * Must yield a validation exception.
     */
    public function testVerifySignedAssertionChangedBody()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/signedassertion_tampered.xml');

        $publicKey = CertificatesMock::getPublicKeySha1();

        $this->setExpectedException('Exception', 'Reference validation failed');
        $assertion = new Assertion($doc->firstChild);
    }

    /**
     * Try to verify a signed assertion with the wrong key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKey()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/signedassertion.xml');

        $publicKey = CertificatesMock::getPublicKey2Sha1();

        $assertion = new Assertion($doc->firstChild);
        $this->setExpectedException('Exception', 'Unable to validate Signature');
        $assertion->validate($publicKey);
    }

    /**
     * Try to verify an assertion signed with RSA with a DSA public key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKeyDSA()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/signedassertion.xml');

        $publicKey = CertificatesMock::getPublicKeyDSAasRSA();

        $assertion = new Assertion($doc->firstChild);
        $this->setExpectedException('Exception', 'Unable to validate Signature');
        $assertion->validate($publicKey);
    }

    /**
     * Calling validate on an unsigned assertion must return
     * false, not an exception.
     */
    public function testVerifyUnsignedAssertion()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $assertion = new Assertion($document->firstChild);

        // Was not signed
        $this->assertFalse($assertion->getWasSignedAtConstruction());

        $publicKey = CertificatesMock::getPublicKeySha1();
        $result = $assertion->validate($publicKey);
        $this->assertFalse($result);
    }

    /**
     * An assertion must always be version "2.0".
     */
    public function testAssertionVersionOtherThan20ThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="1.3"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $this->setExpectedException('Exception', 'Unsupported version: 1.3');
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * An assertion without an ID must throw an exception
     */
    public function testAssertionWithoutIDthrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $this->setExpectedException('Exception', 'Missing ID attribute on SAML assertion');
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * An assertion must always have an Issuer element.
     */
    public function testAssertionWithoutIssuerThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $this->setExpectedException('Exception', 'Missing <saml:Issuer> in assertion');
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * More than one <saml:Subject> is not allowed in an Assertion.
     */
    public function testMoreThanOneSubjectThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">5</saml:NameID>
  </saml:Subject>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">aap</saml:NameID>
  </saml:Subject>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', 'More than one <saml:Subject> in <saml:Assertion>');
        $assertion = new Assertion($document->documentElement);
    }

    /**
     * No more than one NameID may be present in the Subject
     */
    public function testMoreThanOneNameIDThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">5</saml:NameID>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">6</saml:NameID>
  </saml:Subject>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', 'More than one <saml:NameID> or <saml:EncryptedID> in <saml:Subject>');
        $assertion = new Assertion($document->documentElement);
    }

    /**
     * A <saml:Subject> wtthout both NameID and SubjectConfirmation throws exception.
     */
    public function testSubjectMustHaveNameIDorSubjectConfirmation()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Subject>
    <Something>not a nameid or subject confirmation</Something>
  </saml:Subject>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', 'Missing <saml:SubjectConfirmation> in <saml:Subject>');
        $assertion = new Assertion($document->documentElement);
    }

    /**
     * An Assertion may not have more than one <saml:Conditions>
     */
    public function testTooManyConditionsThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
      <saml:Audience>audience2</saml:Audience>
    </saml:AudienceRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
  <saml:Conditions>
      <saml:OtherCondition>not allowed</saml:OtherCondition>
  </saml:Conditions>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', 'More than one <saml:Conditions> in <saml:Assertion>');
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * A Condition must be of namespace saml.
     */
    public function testConditionWithUnknownNamespaceThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
    </saml:AudienceRestriction>
    <other:OneTimeUse>this is not allowed</other:OneTimeUse>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', 'Unknown namespace of condition:');
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * Test various types of allowed Conditions.
     * - AudienceRestriction: are ANDed together so should only be audience1
     * - OneTimeUse and ProxyRestrictions must be accepted but are
     *   currently a no-op.
     */
    public function testConditionAllowedTypes()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
    </saml:AudienceRestriction>
    <saml:AudienceRestriction>
      <saml:Audience>audience2</saml:Audience>
      <saml:Audience>audience1</saml:Audience>
    </saml:AudienceRestriction>
    <saml:OneTimeUse>
    </saml:OneTimeUse>
    <saml:ProxyRestriction>
    </saml:ProxyRestriction>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->firstChild);

        $audienceRestrictions = $assertion->getValidAudiences();
        $this->assertCount(1, $audienceRestrictions);
        $this->assertEquals('audience1', $audienceRestrictions[0]);
    }

    /**
     * Any Condition other than AudienceRestirction, OneTimeUse and
     * ProxyRestriction must throw an Exception.
     */
    public function testUnkownThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Conditions>
    <saml:AudienceRestriction>
      <saml:Audience>audience1</saml:Audience>
    </saml:AudienceRestriction>
    <saml:OtherCondition>this is not allowed</saml:OtherCondition>
  </saml:Conditions>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "Unknown condition: 'OtherCondition'");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * More than one AuthnStatement will throw Exception.
     */
    public function testMoreThanOneAuthnStatementThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:30Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "More than one <saml:AuthnStatement> in <saml:Assertion> not supported");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * AuthnStatement must have AuthnInstant attribute, if missing
     * throw Exception.
     */
    public function testMissingAuthnInstantThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement>
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "Missing required AuthnInstant attribute on <saml:AuthnStatement>");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * More than one AuthnContext inside AuthnStatement will throw Exception.
     */
    public function testMoreThanOneAuthnContextThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP1</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthenticatingAuthority>someIdP2</saml:AuthenticatingAuthority>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "More than one <saml:AuthnContext> in <saml:AuthnStatement>");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * No AuthnContext inside AuthnStatement will throw Exception.
     */
    public function testMissingAuthnContextThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "Missing required <saml:AuthnContext> in <saml:AuthnStatement>");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * More than one AuthnContextDeclRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclRefThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextDeclRef>/relative/path/to/document1.xml</saml:AuthnContextDeclRef>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document2.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "More than one <saml:AuthnContextDeclRef> found");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * More than one AuthnContextDecl inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextDecl>
        <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
        </samlac:AuthenticationContextDeclaration>
      </saml:AuthnContextDecl>
      <saml:AuthnContextDecl>
        <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
        </samlac:AuthenticationContextDeclaration>
      </saml:AuthnContextDecl>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "More than one <saml:AuthnContextDecl> found?");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * More than one AuthnContextClassRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextClassRefThrowsException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
      <saml:AuthnContextClassRef>someOtherAuthnContext</saml:AuthnContextClassRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);

        $this->setExpectedException('Exception', "More than one <saml:AuthnContextClassRef> in <saml:AuthnContext>");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * When an Attribute element has no name, exception is thrown.
     */
    public function testMissingNameOnAttribute()
    {
        $document = new \DOMDocument();
        $document->loadXML(<<<XML
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:AttributeStatement>
        <saml:Attribute Name="urn:ServiceID">
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute>
          <saml:AttributeValue xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="xs:string">1</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML
        );

        $this->setExpectedException('Exception', "Missing name on <saml:Attribute> element");
        $assertion = new Assertion($document->firstChild);
    }

    /**
     * If this assertion mixes Attribute NameFormats, the AttributeNameFormat
     * of this assertion will be set to unspecified.
     */
    public function testMixedAttributeNameFormats()
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    Version="2.0"
                    ID="_93af655219464fb403b34436cfb0c5cb1d9a5502"
                    IssueInstant="1970-01-01T01:33:31Z">
      <saml:Issuer>Provider</saml:Issuer>
      <saml:Conditions/>
      <saml:AttributeStatement>
        <saml:Attribute Name="1.3.6.1.4.1.25178.1.2.9" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic">
            <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $assertion = new Assertion(DOMDocumentFactory::fromString($xml)->firstChild);

        $nameFormat = $assertion->getAttributeNameFormat();
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified', $nameFormat);
    }

    /**
     * Test basic NameID unmarshalling.
     */
    public function testNameIDunmarshalling()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">b7de81420a19416</saml:NameID>
  </saml:Subject>
  <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
    <saml:AuthnContext>
      <saml:AuthnContextClassRef>someAuthnContext</saml:AuthnContextClassRef>
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->documentElement);

        $nameID = $assertion->getNameID();
        $this->assertEquals('b7de81420a19416', $nameID->value);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->Format);
        $this->assertFalse($assertion->isNameIdEncrypted());

        // Not encrypted, should be a no-op
        $privateKey = CertificatesMock::getPrivateKey();
        $decrypted = $assertion->decryptNameId($privateKey);
        $this->assertEquals('b7de81420a19416', $nameID->value);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->Format);
        $this->assertFalse($assertion->isNameIdEncrypted());
    }

    /**
     * Test NameID Encryption and Decryption.
     */
    public function testNameIdEncryption()
    {
        // Create an assertion
        $assertion = new Assertion();
        $assertion->setIssuer('testIssuer');
        $assertion->setValidAudiences(array('audience1', 'audience2'));
        $assertion->setAuthnContext('someAuthnContext');

        $assertion->setNameId(array(
            "Value" => "just_a_basic_identifier",
            "Format" => "urn:oasis:names:tc:SAML:2.0:nameid-format:transient"));
        $this->assertFalse($assertion->isNameIdEncrypted());

        $publicKey = CertificatesMock::getPublicKey();
        $assertion->encryptNameId($publicKey);
        $this->assertTrue($assertion->isNameIdEncrypted());

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($assertionElement)->firstChild);

        $this->assertTrue($assertionToVerify->isNameIdEncrypted());
        $privateKey = CertificatesMock::getPrivateKey();
        $assertionToVerify->decryptNameId($privateKey);
        $this->assertFalse($assertionToVerify->isNameIdEncrypted());
        $nameID = $assertionToVerify->getNameID();
        $this->assertEquals('just_a_basic_identifier', $nameID->value);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->Format);
    }

    /**
     * Test Exception when trying to get encrypted NameId without
     * decrypting it first.
     */
    public function testRetrieveEncryptedNameIdException()
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Subject>
  <saml:EncryptedID>
    <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:dsig="http://www.w3.org/2000/09/xmldsig#" Type="http://www.w3.org/2001/04/xmlenc#Element">
    <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
    <dsig:KeyInfo xmlns:dsig="http://www.w3.org/2000/09/xmldsig#">
      <xenc:EncryptedKey><xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
      <xenc:CipherData><xenc:CipherValue>Y78/DDeSkI4qECUPXJM1cWUTaYVglxnqDRpjcqd6zdIR6yWMwIzUCd+fa9KhKutN4kN1i/koSMNmk+c6uOXSi0Xuohth61eU9oIwLl6mKZwThXEQiuphAtMVPXtooKfU1l58+xWiiO2IidYmtb1vCcVD0hZwnVv28kxrMQdQmzw=</xenc:CipherValue></xenc:CipherData>
      </xenc:EncryptedKey>
   </dsig:KeyInfo>
   <xenc:CipherData>
     <xenc:CipherValue>cfQoRV0xf+D5bOQs+8icVEkWX4MRNxl1MhImqO/GwYxjCwj0AH/9O4kr2v4WZ4MC3zHhUjcq4HO70/xrkzQVMN9pBsF2yv9sUuN2rEPd8k/Oj/OA3X4xGNywxoJILioh56OyNkFK/q4WRptvvSQV1vPc0G5y65MZBiR2fy+L+ukBJ8mnzxL7aIIEKRxNa0beKdrrZ2twWH3Uwn3UW5LcSefaY+VHcM/9I4Xb7U5QWxRXzBOEa6v/a3cZ/TmlXYkj</xenc:CipherValue>
   </xenc:CipherData>
   </xenc:EncryptedData>
  </saml:EncryptedID>
  </saml:Subject>
</saml:Assertion>
XML;
        $document = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->documentElement);
        $this->setExpectedException('Exception', "Attempted to retrieve encrypted NameID without decrypting it first");
        $assertion->getNameID();
    }
}
