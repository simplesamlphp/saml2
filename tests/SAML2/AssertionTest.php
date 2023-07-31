<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use DOMNodeList;
use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\AssertionTest
 */
class AssertionTest extends TestCase
{
    /**
     * Test to build a basic assertion
     */
    public function testMarshalling(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();
        $assertion->setIssuer($issuer);
        $assertion->setValidAudiences(
            [new Audience('audience1'), new Audience('audience2')]
        );
        $assertion->setAuthnContext(
            new AuthnContext(new AuthnContextClassRef('someAuthnContext'), null, null, []),
        );

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($assertionElement);
        $issuerElements = XPath::xpQuery($assertionElement, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);

        // Test for an AudienceRestriction
        $audienceElements = XPath::xpQuery(
            $assertionElement,
            './saml_assertion:Conditions/saml_assertion:AudienceRestriction/saml_assertion:Audience',
            $xpCache,
        );
        $this->assertCount(2, $audienceElements);
        $this->assertEquals('audience1', $audienceElements[0]->textContent);
        $this->assertEquals('audience2', $audienceElements[1]->textContent);

        // Test for an Authentication Context
        $authnContextElements = XPath::xpQuery(
            $assertionElement,
            './saml_assertion:AuthnStatement/saml_assertion:AuthnContext/saml_assertion:AuthnContextClassRef',
            $xpCache,
        );
        $this->assertCount(1, $authnContextElements);
        $this->assertEquals('someAuthnContext', $authnContextElements[0]->textContent);
    }


    /**
     * Test to parse a basic assertion
     */
    public function testUnmarshalling(): void
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
        $this->assertFalse($assertion->wasSignedAtConstruction());

        // Test for valid audiences
        $assertionValidAudiences = $assertion->getValidAudiences();
        $this->assertCount(2, $assertionValidAudiences);
        $this->assertEquals('audience1', $assertionValidAudiences[0]->getContent());
        $this->assertEquals('audience2', $assertionValidAudiences[1]->getContent());

        // Test for Authenticating Authorities
        $assertionAuthenticatingAuthorities = $assertion->getAuthnContext()?->getAuthenticatingAuthorities();
        $this->assertCount(2, $assertionAuthenticatingAuthorities);
        $this->assertEquals('someIdP1', $assertionAuthenticatingAuthorities[0]->getContent());
        $this->assertEquals('someIdP2', $assertionAuthenticatingAuthorities[1]->getContent());
    }


    /**
     * Test an assertion with lots of options
     */
    public function testMarshallingUnmarshallingChristmas(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();

        $assertion->setIssuer($issuer);
        $assertion->setValidAudiences(
            [new Audience('audience1'), new Audience('audience2')]
        );

        $this->assertNull($assertion->getAuthnContext()?->getAuthnContextClassRef());

        $assertion->setID("_123abc");

        $assertion->setIssueInstant(1234567890);
        $assertion->setAuthnInstant(1234567890 - 1);
        $assertion->setNotBefore(1234567890 - 10);
        $assertion->setNotOnOrAfter(1234567890 + 100);
        $assertion->setSessionNotOnOrAfter(1234568890 + 200);

        $assertion->setSessionIndex("idx1");

        $assertion->setAuthnContext(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml'),
                [new AuthenticatingAuthority("idp1"), new AuthenticatingAuthority("idp2")],
            ),
        );

        $assertion->setAttributes([
            "name1" => ["value1", "value2"],
            "name2" => [2],
            "name3" => [null]
        ]);
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");

        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($assertionElement)->firstChild);

        $this->assertEquals(
            'https://example.org/relative/path/to/document.xml',
            $assertionToVerify->getAuthnContext()?->getAuthnContextDeclRef()->getContent(),
        );
        $this->assertEquals('_123abc', $assertionToVerify->getId());
        $this->assertEquals(1234567890, $assertionToVerify->getIssueInstant());
        $this->assertEquals(1234567889, $assertionToVerify->getAuthnInstant());
        $this->assertEquals(1234567880, $assertionToVerify->getNotBefore());
        $this->assertEquals(1234567990, $assertionToVerify->getNotOnOrAfter());
        $this->assertEquals(1234569090, $assertionToVerify->getSessionNotOnOrAfter());

        $this->assertEquals('idx1', $assertionToVerify->getSessionIndex());

        $authauth = $assertionToVerify->getAuthnContext()?->getAuthenticatingAuthorities();
        $this->assertCount(2, $authauth);
        $this->assertEquals("idp2", $authauth[1]->getContent());

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
    public function testMarshallingUnmarshallingAttributeValTypes(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();

        $assertion->setIssuer($issuer);
        $assertion->setValidAudiences(
            [new Audience('audience1'), new Audience('audience2')]
        );

        $assertion->setAuthnContext(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null,
                [new AuthenticatingAuthority("idp1"), new AuthenticatingAuthority("idp2")],
            ),
        );

        $assertion->setAttributes([
            "name1" => ["value1",123,"2017-31-12"],
            "name2" => [2],
            "name3" => [1234, "+2345"]
        ]);
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");

        // set xs:type for first and third name1 values, and all name3 values.
        // second name1 value and all name2 values will use default behaviour
        $assertion->setAttributesValueTypes([
            "name1" => ["xs:string", null, "xs:date"],
            "name3" => "xs:decimal"
        ]);

        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($assertionElement)->firstChild);

        $authauth = $assertionToVerify->getAuthnContext()?->getAuthenticatingAuthorities();
        $this->assertCount(2, $authauth);
        $this->assertEquals("idp2", $authauth[1]->getContent());

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

    public function testMarshallingWrongAttributeValTypes(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();

        $assertion->setIssuer($issuer);
        $assertion->setValidAudiences(
            [new Audience('audience1'), new Audience('audience2')]
        );

        $assertion->setAuthnContext(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null,
                [new AuthenticatingAuthority("idp1"), new AuthenticatingAuthority("idp2")],
            ),
        );

        $assertion->setAttributes([
            "name1" => ["value1", "2017-31-12"],
            "name2" => [2],
            "name3" => [1234, "+2345"]
        ]);
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");

        // set wrong number elements in name1
        $assertion->setAttributesValueTypes([
            "name1" => ["xs:string"],
            "name3" => "xs:decimal"
        ]);

        $this->expectException(Exception::class, "Array of value types and array of values have different size for attribute 'name1'");
        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();
    }


    /**
     * Test parsing AuthnContext elements Decl and ClassRef
     */
    public function testAuthnContextDeclAndClassRef(): void
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
        $authnContextDecl = $assertion->getAuthnContext()->getAuthnContextDecl();
        $this->assertNotEmpty($authnContextDecl);
        $childLocalName = $authnContextDecl->getElements()[0]->getLocalName();
        $this->assertEquals('AuthenticationContextDeclaration', $childLocalName);

        $this->assertEquals('someAuthnContext', $assertion->getAuthnContext()?->getAuthnContextClassRef()->getContent());
    }


    /**
     * Test parsing AuthnContext elements DeclRef and ClassRef
     */
    public function testAuthnContextDeclRefAndClassRef(): void
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
      <saml:AuthnContextDeclRef>https://example.org/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->documentElement);
        $this->assertEquals(
            'https://example.org/relative/path/to/document.xml',
            $assertion->getAuthnContext()?->getAuthnContextDeclRef()->getContent(),
        );
        $this->assertEquals(
            'someAuthnContext',
            $assertion->getAuthnContext()?->getAuthnContextClassRef()->getContent(),
        );
    }


    /**
     * Test setting an AuthnContextDecl chunk.
     */
    public function testSetAuthnContextDecl(): void
    {
        $xml = <<<XML
<saml:AuthnContextDecl xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
  </samlac:AuthenticationContextDeclaration>
</saml:AuthnContextDecl>
XML;

        $document  = DOMDocumentFactory::fromString($xml);
        $assertion = new Assertion();

        $assertion->setAuthnContext(
            new AuthnContext(
                null,
                AuthnContextDecl::fromXML($document->documentElement),
                null,
                []
            ),
        );
        $issuer = new Issuer('example:issuer');
        $assertion->setIssuer($issuer);
        $documentParent  = DOMDocumentFactory::fromString("<root />");
        $assertionElement = $assertion->toXML($documentParent->firstChild);

        $xpCache = XPath::getXPath($assertionElement);
        $acElements = XPath::xpQuery(
            $assertionElement,
            './saml_assertion:AuthnStatement/saml_assertion:AuthnContext/saml_assertion:AuthnContextDecl',
            $xpCache,
        );
        $this->assertCount(1, $acElements);
        $this->assertEquals('samlac:AuthenticationContextDeclaration', $acElements[0]->firstChild->tagName);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:ac', $acElements[0]->firstChild->namespaceURI);
    }


    /**
     * @group Assertion
     */
    public function testConvertIssuerToXML(): void
    {
        // Create an Issuer
        $issuer = new Issuer('https://gateway.stepup.org/saml20/sp/metadata');

        // first, try with common Issuer objects (Format=entity)
        $assertion = new Assertion();
        $assertion->setIssuer($issuer);

        $xml = $assertion->toXML();
        $xpCache = XPath::getXPath($xml);
        $xml_issuer = XPath::xpQuery($xml, './saml_assertion:Issuer', $xpCache);
        $xml_issuer = $xml_issuer[0];

        $this->assertFalse($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->getContent(), $xml_issuer->textContent);

        // now, try an Issuer with another format and attributes
        $issuer = new Issuer(
            'https://gateway.stepup.org/saml20/sp/metadata',
            'SomeNameQualifier',
            'SomeSPNameQualifier',
            C::NAMEID_UNSPECIFIED,
            'SomeSPProvidedID',
        );

        $assertion->setIssuer($issuer);
        $xml = $assertion->toXML();
        $xpCache = XPath::getXPath($xml);
        $xml_issuer = XPath::xpQuery($xml, './saml_assertion:Issuer', $xpCache);
        $xml_issuer = $xml_issuer[0];

        $this->assertTrue($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->getContent(), $xml_issuer->textContent);
        $this->assertEquals($issuer->getNameQualifier(), $xml_issuer->getAttribute('NameQualifier'));
        $this->assertEquals($issuer->getSPNameQualifier(), $xml_issuer->getAttribute('SPNameQualifier'));
        $this->assertEquals($issuer->getSPProvidedID(), $xml_issuer->getAttribute('SPProvidedID'));
    }


    public function testAuthnContextDeclAndRefConstraint(): void
    {
        $xml = <<<XML
<saml:AuthnContextDecl xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <samlac:AuthenticationContextDeclaration xmlns:samlac="urn:oasis:names:tc:SAML:2.0:ac">
  </samlac:AuthenticationContextDeclaration>
</saml:AuthnContextDecl>
XML;

        $document  = DOMDocumentFactory::fromString($xml);
        $assertion = new Assertion();

        $e = null;
        try {
            $assertion->setAuthnContext(
                new AuthnContext(
                    null,
                    AuthnContextDecl::fromXML($document->documentElement),
                    new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml'),
                    [],
                ),
            );
        } catch (AssertionFailedException $e) {
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
            $assertion = new Assertion($document->documentElement);
        } catch (AssertionFailedException $e) {
        }
        $this->assertNotEmpty($e);
    }


    public function testMustHaveClassRefOrDeclOrDeclRef(): void
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
        } catch (Exception $e) {
        }
        $this->assertNotEmpty($e);
    }


    public function testGetSubjectConfirmationData(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">joehoe</saml:NameID>
    <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
      <saml:SubjectConfirmationData NotOnOrAfter="2010-03-05T13:42:06Z"
        Recipient="https://example.org/authentication/consume-assertion"
        InResponseTo="_004387940075992d891e90c6a10bc9fd1bd443ee85a61b7d07fd12b0843d"
        />
    </saml:SubjectConfirmation>
  </saml:Subject>
</saml:Assertion>
XML
        );

        $assertion = new Assertion($document->firstChild);
        $sc = $assertion->getSubjectConfirmation();

        $this->assertCount(1, $sc);
        $this->assertInstanceOf(SubjectConfirmation::class, $sc[0]);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:cm:bearer', $sc[0]->getMethod());
        $this->assertEquals('https://example.org/authentication/consume-assertion', $sc[0]->getSubjectConfirmationData()->getRecipient());
        $this->assertEquals(1267796526, $sc[0]->getSubjectConfirmationData()->getNotOnOrAfter()->getTimestamp());
    }


    /**
     * Tests that AuthnContextDeclRef is not mistaken for AuthnContextClassRef.
     *
     * This tests against reintroduction of removed behavior.
     */
    public function testNoAuthnContextDeclRefFallback(): void
    {
        $authnContextDeclRef = 'https://example.org/relative/path/to/document.xml';

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
        $this->assertNull($assertion->getAuthnContext()->getAuthnContextClassRef());
        $this->assertEquals(
            $authnContextDeclRef,
            $assertion->getAuthnContext()?->getAuthnContextDeclRef()->getContent(),
        );
    }


    public function testHasEncryptedAttributes(): void
    {
        $document = new DOMDocument();
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


    public function testHasEncryptedAttributes2(): void
    {
        $document = new DOMDocument();
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
        $assertion = new Assertion($document->firstChild);
        $this->assertFalse($assertion->hasEncryptedAttributes());
    }


    /**
     * @group Assertion
     */
    public function testCorrectSignatureMethodCanBeExtracted(): void
    {
        $document = new DOMDocument();
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
        $unsignedAssertion->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);
        $this->assertFalse($unsignedAssertion->wasSignedAtConstruction());
        $this->assertEquals($privateKey, $unsignedAssertion->getSignatureKey());

        $signedAssertion = new Assertion($unsignedAssertion->toXML());

        $signatureMethod = $signedAssertion->getSignatureMethod();

        $this->assertEquals($privateKey->getAlgorith(), $signatureMethod);

        $this->assertTrue($signedAssertion->wasSignedAtConstruction());
    }


    public function testEptiAttributeValuesAreParsedCorrectly(): void
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

        $this->assertInstanceOf(NameID::class, $maceValue);
        $this->assertInstanceOf(NameID::class, $oidValue);

        $this->assertEquals('abcd-some-value-xyz', $maceValue->getContent());
        $this->assertEquals('abcd-some-value-xyz', $oidValue->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceValue->getFormat());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $oidValue->getFormat());
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }


    public function testEptiLegacyAttributeValuesCanBeString(): void
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
          <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonTargetedID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
          <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:EntityConcernedSubID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
          <saml:AttributeValue xsi:type="xs:string">string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;
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
          <saml:AttributeValue xsi:type="xs:string">string-12</saml:AttributeValue>
        </saml:Attribute>
        <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonTargetedID" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
          <saml:AttributeValue xsi:type="xs:string">string-23</saml:AttributeValue>
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

        $this->assertInstanceOf(NameID::class, $maceValue);
        $this->assertInstanceOf(NameID::class, $oidValue);

        $this->assertEquals('string-23', $maceValue->getContent());
        $this->assertEquals('string-12', $oidValue->getContent());
    }


    /**
     * as per http://software.internet2.edu/eduperson/internet2-mace-dir-eduperson-201310.html#eduPersonTargetedID
     * it is multivalued
     */
    public function testEptiAttributeParsingSupportsMultipleValues(): void
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

        $this->assertInstanceOf(NameID::class, $maceFirstValue);
        $this->assertInstanceOf(NameID::class, $maceSecondValue);

        $this->assertEquals('abcd-some-value-xyz', $maceFirstValue->getContent());
        $this->assertEquals('xyz-some-value-abcd', $maceSecondValue->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceFirstValue->getFormat());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceSecondValue->getFormat());

        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }


    public function testAttributeValuesWithComplexTypesAreParsedCorrectly(): void
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
            DOMNodeList::class,
            $attributes['urn:some:custom:outer:element'][0]
        );
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }


    public function testTypedAttributeValuesAreParsedCorrectly(): void
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
        $this->assertIsInt($attributes['urn:some:integer'][0]);
        $this->assertIsString($attributes['urn:some:string'][0]);
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }


    public function testEncryptedAttributeValuesWithComplexTypeValuesAreParsedCorrectly(): void
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
        $assertion->setRequiredEncAttributes(true);
        $this->assertEquals($privateKey, $assertion->getEncryptionKey());

        $encryptedAssertion = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($encryptedAssertion)->firstChild);

        $this->assertTrue($assertionToVerify->hasEncryptedAttributes());

        $assertionToVerify->decryptAttributes(CertificatesMock::getPrivateKey());

        $attributes = $assertionToVerify->getAttributes();
        $this->assertInstanceOf(
            DOMNodeList::class,
            $attributes['urn:some:custom:outer:element'][0]
        );
        $this->assertXmlStringEqualsXmlString($xml, $assertionToVerify->toXML()->ownerDocument->saveXML());
    }


    public function testTypedEncryptedAttributeValuesAreParsedCorrectly(): void
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
        $assertion->setRequiredEncAttributes(true);
        $encryptedAssertion = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = new Assertion(DOMDocumentFactory::fromString($encryptedAssertion)->firstChild);

        $this->assertTrue($assertionToVerify->hasEncryptedAttributes());

        $assertionToVerify->decryptAttributes(CertificatesMock::getPrivateKey());
        $attributes = $assertionToVerify->getAttributes();

        $this->assertIsInt($attributes['urn:some:integer'][0]);
        $this->assertIsString($attributes['urn:some:string'][0]);
        $this->assertXmlStringEqualsXmlString($xml, $assertionToVerify->toXML()->ownerDocument->saveXML());
    }


    /**
     * Try to verify a signed assertion.
     */
    public function testVerifySignedAssertion(): void
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/signedassertion.xml');

        $publicKey = CertificatesMock::getPublicKeySha256();

        $assertion = new Assertion($doc->firstChild);
        $result = $assertion->validate($publicKey);

        $this->assertTrue($result);
        // Double-check that we can actually retrieve some basics.
        $this->assertEquals("_d908a49b8b63665738430d1c5b655f297b91331864", $assertion->getId());
        $this->assertEquals(
            "https://thki-sid.pt-48.utr.surfcloud.nl/ssp/saml2/idp/metadata.php",
            $assertion->getIssuer()->getContent()
        );
        $this->assertEquals("1457707995", $assertion->getIssueInstant());

        $certs = $assertion->getCertificates();
        $this->assertCount(1, $certs);
        $this->assertEquals(CertificatesMock::getPlainPublicKeyContents(), $certs[0]);

        // Was signed
        $this->assertTrue($assertion->wasSignedAtConstruction());
    }


    /**
     * Make sure an assertion whose signature verifies cannot be tampered by using XML comments.
     * @see https://duo.com/labs/psa/duo-psa-2017-003
     */
    public function testCommentsInSignedAssertion(): void
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/signedassertion_with_comments.xml');

        $publicKey = CertificatesMock::getPublicKeySha256();

        $assertion = new Assertion($doc->firstChild);
        $result = $assertion->validate($publicKey);

        $this->assertTrue($result);
        $this->assertEquals("_1bbcf227253269d19a689c53cdd542fe2384a9538b", $assertion->getNameId()->getContent());
    }


    /**
     * Try to verify a signed assertion in which a byte was changed after signing.
     * Must yield a validation exception.
     */
    public function testVerifySignedAssertionChangedBody(): void
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/signedassertion_tampered.xml');

        $publicKey = CertificatesMock::getPublicKeySha256();

        $this->expectException(Exception::class, 'Reference validation failed');
        $assertion = new Assertion($doc->firstChild);
    }


    /**
     * Try to verify a signed assertion with the wrong key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKey(): void
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/signedassertion.xml');

        $publicKey = CertificatesMock::getPublicKey2Sha256();

        $assertion = new Assertion($doc->firstChild);
        $this->expectException(Exception::class, 'Unable to validate Signature');
        $assertion->validate($publicKey);
    }


    /**
     * Try to verify an assertion signed with RSA with a DSA public key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKeyDSA(): void
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/signedassertion.xml');

        $publicKey = CertificatesMock::getPublicKeyDSAasRSA();

        $assertion = new Assertion($doc->firstChild);
        $this->expectException(Exception::class, 'Unable to validate Signature');
        $assertion->validate($publicKey);
    }


    /**
     * Calling validate on an unsigned assertion must return
     * false, not an exception.
     */
    public function testVerifyUnsignedAssertion(): void
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
        $this->assertFalse($assertion->wasSignedAtConstruction());

        $publicKey = CertificatesMock::getPublicKeySha256();
        $result = $assertion->validate($publicKey);
        $this->assertFalse($result);
    }


    /**
     * An assertion must always be version "2.0".
     */
    public function testAssertionVersionOtherThan20ThrowsException(): void
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
        $this->expectException(Exception::class, 'Unsupported version: 1.3');
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * An assertion without an ID must throw an exception
     */
    public function testAssertionWithoutIDthrowsException(): void
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
        $this->expectException(Exception::class, 'Missing ID attribute on SAML assertion');
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * An assertion must always have an Issuer element.
     */
    public function testAssertionWithoutIssuerThrowsException(): void
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
        $this->expectException(Exception::class, 'Missing <saml:Issuer> in assertion');
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * More than one <saml:Subject> is not allowed in an Assertion.
     */
    public function testMoreThanOneSubjectThrowsException(): void
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

        $this->expectException(Exception::class, 'More than one <saml:Subject> in <saml:Assertion>');
        $assertion = new Assertion($document->documentElement);
    }


    /**
     * No more than one NameID may be present in the Subject
     */
    public function testMoreThanOneNameIDThrowsException(): void
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

        $this->expectException(Exception::class, 'More than one <saml:NameID> or <saml:EncryptedID> in <saml:Subject>');
        $assertion = new Assertion($document->documentElement);
    }


    /**
     * A <saml:Subject> wtthout both NameID and SubjectConfirmation throws exception.
     */
    public function testSubjectMustHaveNameIDorSubjectConfirmation(): void
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

        $this->expectException(Exception::class, 'Missing <saml:SubjectConfirmation> in <saml:Subject>');
        $assertion = new Assertion($document->documentElement);
    }


    /**
     * An Assertion may not have more than one <saml:Conditions>
     */
    public function testTooManyConditionsThrowsException(): void
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

        $this->expectException(Exception::class, 'More than one <saml:Conditions> in <saml:Assertion>');
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * A Condition must be of namespace saml.
     */
    public function testConditionWithUnknownNamespaceThrowsException(): void
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

        $this->expectException(Exception::class, 'Unknown namespace of condition:');
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * Test various types of allowed Conditions.
     * - AudienceRestriction: are ANDed together so should only be audience1
     * - OneTimeUse and ProxyRestrictions must be accepted but are
     *   currently a no-op.
     */
    public function testConditionAllowedTypes(): void
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
        $this->assertEquals('audience1', $audienceRestrictions[0]->getContent());
    }


    /**
     * Any Condition other than AudienceRestirction, OneTimeUse and
     * ProxyRestriction must throw an Exception.
     */
    public function testUnkownThrowsException(): void
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

        $this->expectException(Exception::class, "Unknown condition: 'OtherCondition'");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * More than one AuthnStatement will throw Exception.
     */
    public function testMoreThanOneAuthnStatementThrowsException(): void
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

        $this->expectException(Exception::class, "More than one <saml:AuthnStatement> in <saml:Assertion> not supported");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * AuthnStatement must have AuthnInstant attribute, if missing
     * throw Exception.
     */
    public function testMissingAuthnInstantThrowsException(): void
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

        $this->expectException(Exception::class, "Missing required AuthnInstant attribute on <saml:AuthnStatement>");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * More than one AuthnContext inside AuthnStatement will throw Exception.
     */
    public function testMoreThanOneAuthnContextThrowsException(): void
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

        $this->expectException(
            AssertionFailedException::class,
            "More than one <saml:AuthnContext> in <saml:AuthnStatement>",
        );
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * No AuthnContext inside AuthnStatement will throw Exception.
     */
    public function testMissingAuthnContextThrowsException(): void
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

        $this->expectException(Exception::class, "Missing required <saml:AuthnContext> in <saml:AuthnStatement>");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * More than one AuthnContextDeclRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclRefThrowsException(): void
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

        $this->expectException(Exception::class, "More than one <saml:AuthnContextDeclRef> found");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * More than one AuthnContextDecl inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextDeclThrowsException(): void
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

        $this->expectException(Exception::class, "More than one <saml:AuthnContextDecl> found?");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * More than one AuthnContextClassRef inside AuthnContext will throw Exception.
     */
    public function testMoreThanOneAuthnContextClassRefThrowsException(): void
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

        $this->expectException(Exception::class, "More than one <saml:AuthnContextClassRef> in <saml:AuthnContext>");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * When an Attribute element has no name, exception is thrown.
     */
    public function testMissingNameOnAttribute(): void
    {
        $document = new DOMDocument();
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

        $this->expectException(Exception::class, "Missing name on <saml:Attribute> element");
        $assertion = new Assertion($document->firstChild);
    }


    /**
     * If this assertion mixes Attribute NameFormats, the AttributeNameFormat
     * of this assertion will be set to unspecified.
     */
    public function testMixedAttributeNameFormats(): void
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
    public function testNameIDunmarshalling(): void
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
      <saml:AuthnContextDeclRef>https://example.org/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $assertion = new Assertion($document->documentElement);

        $nameID = $assertion->getNameID();
        $this->assertEquals('b7de81420a19416', $nameID->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->getFormat());
        $this->assertFalse($assertion->isNameIdEncrypted());

        // Not encrypted, should be a no-op
        $privateKey = CertificatesMock::getPrivateKey();
        $decrypted = $assertion->decryptNameId($privateKey);
        $this->assertEquals('b7de81420a19416', $nameID->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->getFormat());
        $this->assertFalse($assertion->isNameIdEncrypted());
    }


    /**
     * Test NameID Encryption and Decryption.
     */
    public function testNameIdEncryption(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();
        $assertion->setIssuer($issuer);
        $assertion->setValidAudiences(
            [new Audience('audience1'), new Audience('audience2')]
        );
        $assertion->setAuthnContext(
            new AuthnContext(new AuthnContextClassRef('someAuthnContext'), null, null, [])
        );

        $nameId = new NameID(
            "just_a_basic_identifier",
            null,
            null,
            "urn:oasis:names:tc:SAML:2.0:nameid-format:transient",
        );
        $assertion->setNameId($nameId);
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
        $this->assertEquals('just_a_basic_identifier', $nameID->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->getFormat());
    }


    /**
     * Test Exception when trying to get encrypted NameId without
     * decrypting it first.
     */
    public function testRetrieveEncryptedNameIdException(): void
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
        $this->expectException(Exception::class, "Attempted to retrieve encrypted NameID without decrypting it first");
        $assertion->getNameID();
    }


    public function testMarshallingElementOrdering(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();
        $assertion->setIssuer($issuer);
        $assertion->setAttributes([
            "name1" => ["value1","value2"],
            "name2" => ["value3"],
        ]);
        $assertion->setAttributeNameFormat("urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified");
        $assertion->setSignatureKey(CertificatesMock::getPrivateKey());

        $nameId = new NameID(
            "just_a_basic_identifier",
            "urn:oasis:names:tc:SAML:2.0:nameid-format:transient",
        );
        $assertion->setNameId($nameId);
        $assertion->setAuthnContext(
            new AuthnContext(new AuthnContextClassRef('someAuthnContext'), null, null, [])
        );

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($assertionElement);
        $issuerElements = XPath::xpQuery($assertionElement, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);
        // Test ordering of Assertion contents
        $assertionElements = XPath::xpQuery(
            $assertionElement,
            './saml_assertion:Issuer/following-sibling::*',
            $xpCache,
        );
        $this->assertCount(5, $assertionElements);
        $this->assertEquals('ds:Signature', $assertionElements[0]->tagName);
        $this->assertEquals('saml:Subject', $assertionElements[1]->tagName);
        $this->assertEquals('saml:Conditions', $assertionElements[2]->tagName);
        $this->assertEquals('saml:AuthnStatement', $assertionElements[3]->tagName);
        $this->assertEquals('saml:AttributeStatement', $assertionElements[4]->tagName);
    }


    /**
     * Test attribute value empty string and null.
     * Ensure that empty string attribues are output, and null attributes also but
     * with different type.
     */
    public function testAttributeValueEmptyStringAndNull(): void
    {
        // Create an assertion
        $assertion = new Assertion();

        $issuer = new Issuer('testIssuer');
        $assertion->setIssuer($issuer);

        $assertion->setAttributes([
            "name1" => ["value1", "value2"],
            "name2" => ["value3", ""],
            "name3" => ["value1", null, "value5"],
        ]);

        $assertionElement = $assertion->toXML();
        $xpCache = XPath::getXPath($assertionElement);
        $assertionElements = XPath::xpQuery(
            $assertionElement,
            './saml_assertion:AttributeStatement/saml_assertion:Attribute',
            $xpCache,
        );

        $this->assertCount(3, $assertionElements);

        $this->assertEquals(2, $assertionElements[1]->childNodes->length);
        // empty string should be empty string with type string
        $this->assertEquals('', $assertionElements[1]->childNodes->item(1)->nodeValue);
        $this->assertEquals(1, $assertionElements[1]->childNodes->item(1)->attributes->length);
        $this->assertEquals('xsi:type', $assertionElements[1]->childNodes->item(1)->attributes->item(0)->nodeName);
        $this->assertEquals('xs:string', $assertionElements[1]->childNodes->item(1)->attributes->item(0)->value);

        $this->assertEquals(3, $assertionElements[2]->childNodes->length);
        // null value should be empty attribute with nil attribute set to true
        $this->assertEquals('', $assertionElements[2]->childNodes->item(1)->nodeValue);
        $this->assertEquals(1, $assertionElements[2]->childNodes->item(1)->attributes->length);
        $this->assertEquals('xsi:nil', $assertionElements[2]->childNodes->item(1)->attributes->item(0)->nodeName);
        $this->assertEquals('true', $assertionElements[2]->childNodes->item(1)->attributes->item(0)->value);
        // double check that 'normal' string attribute is still correct
        $this->assertEquals('value5', $assertionElements[2]->childNodes->item(2)->nodeValue);
        $this->assertEquals('xsi:type', $assertionElements[2]->childNodes->item(2)->attributes->item(0)->nodeName);
        $this->assertEquals('xs:string', $assertionElements[2]->childNodes->item(2)->attributes->item(0)->value);
    }
}
