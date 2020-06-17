<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use DOMNodeList;
use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

/**
 * Class \SAML2\AssertionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Assertion
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AssertionTest extends MockeryTestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Assertion.xml'
        );
    }


    /**
     * Test to build a basic assertion
     */
    public function testMarshalling(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create the conditions
        $conditions = new Conditions(
            null,
            null,
            [],
            [new AudienceRestriction(['audience1', 'audience2'])]
        );

        // Create the statements
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null
            ),
            time()
        );

        // Create an assertion
        $assertion = new Assertion($issuer, null, null, null, $conditions, [$authnStatement]);

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $issuerElements = XMLUtils::xpQuery($assertionElement, './saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);

        // Test for an AudienceRestriction
        $audienceElements = XMLUtils::xpQuery(
            $assertionElement,
            './saml_assertion:Conditions/saml_assertion:AudienceRestriction/saml_assertion:Audience'
        );
        $this->assertCount(2, $audienceElements);
        $this->assertEquals('audience1', $audienceElements[0]->textContent);
        $this->assertEquals('audience2', $audienceElements[1]->textContent);

        // Test for an Authentication Context
        $authnContextElements = XMLUtils::xpQuery(
            $assertionElement,
            './saml_assertion:AuthnStatement/saml_assertion:AuthnContext/saml_assertion:AuthnContextClassRef'
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
        $assertion = Assertion::fromXML($document->documentElement);

        // Was not signed
        $this->assertFalse($assertion->wasSignedAtConstruction());

        // Test for valid audiences
        $conditions = $assertion->getConditions();
        $this->assertNotNull($conditions);

        $audienceRestriction = $conditions->getAudienceRestriction();
        $this->assertCount(1, $audienceRestriction);

        $restriction1 = array_pop($audienceRestriction);
        $this->assertCount(2, $restriction1->getAudience());
        $this->assertEquals(['audience1', 'audience2'], $restriction1->getAudience());

        // Test for Authenticating Authorities
        $assertionAuthenticatingAuthorities = $assertion->getAuthnStatements()[0]->getAuthnContext()->getAuthenticatingAuthorities();
        $this->assertCount(2, $assertionAuthenticatingAuthorities);
        $this->assertEquals('someIdP1', $assertionAuthenticatingAuthorities[0]);
        $this->assertEquals('someIdP2', $assertionAuthenticatingAuthorities[1]);
    }


    /**
     * Test an assertion with lots of options
     */
    public function testMarshallingUnmarshallingChristmas(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create Conditions
        $conditions = new Conditions(
            1234567880,
            1234567990,
            [],
            [
                new AudienceRestriction(
                    ['audience1', 'audience2']
                )
            ]
        );

        // Create AuthnStatement
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                new AuthnContextDeclRef('/relative/path/to/document.xml'),
                ["idp1", "idp2"]
            ),
            1234567890 - 1,
            1234568890 + 200,
            'idx1'
//            'idx1',
//            new SubjectLocality('127.0.0.1', 'no.place.like.home')
        );

        // Create AttributeStatement
        $attributeStatement = new AttributeStatement(
            // Attribute
            [
                new Attribute('name1', null, null, [new AttributeValue('value1'), new AttributeValue('value2')]),
                new Attribute('name2', null, null, [new AttributeValue(2)]),
                new Attribute('name3', null, null, [new AttributeValue(null)])
            ],
            // EncryptedAttribute
            []
        );

        // Create an assertion
        $statements = [$authnStatement, $attributeStatement];
        $assertion = new Assertion($issuer, '_123abc', 1234567890, null, $conditions, $statements);

        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = Assertion::fromXML(DOMDocumentFactory::fromString($assertionElement)->documentElement);
        $conditions = $assertionToVerify->getConditions();
        $this->assertNotNull($conditions);

        $this->assertEquals('/relative/path/to/document.xml', $assertionToVerify->getAuthnContextDeclRef());
        $this->assertEquals('_123abc', $assertionToVerify->getId());
        $this->assertEquals(1234567890, $assertionToVerify->getIssueInstant());
        $this->assertEquals(1234567889, $assertionToVerify->getAuthnInstant());
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
        $this->assertEquals(Constants::NAMEFORMAT_UNSPECIFIED, $assertionToVerify->getAttributeNameFormat());
    }


    /**
     * Test an assertion attribute value types options
     */
    public function testMarshallingUnmarshallingAttributeValTypes(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create Conditions
        $conditions = new Conditions(
            null,
            null,
            [],
            [
                new AudienceRestriction(
                    ['audience1', 'audience2']
                )
            ]
        );

        // Create AuthnStatement
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                null,
                ["idp1", "idp2"]
            )
        );

        // Create AttributeStatement
        $attributeStatement = new AttributeStatement(
            // Attribute
            [
                new Attribute('name1', null, null, [new AttributeValue('value1'), new AttributeValues(123), new AttributeValue('2017-31-12')]),
                new Attribute('name2', null, null, [new AttributeValue(2)]),
                new Attribute('name3', null, null, [new AttributeValue(1234), new AttributeValue('+2345')])
            ],
            // EncryptedAttribute
            []
        );

        // Create an assertion
        $assertion = new Assertion($issuer, null, null, null, $conditions, [$authnStatement, $attributeStatement]);


        // set xs:type for first and third name1 values, and all name3 values.
        // second name1 value and all name2 values will use default behaviour
        $assertion->setAttributesValueTypes([
            "name1" => ["xs:string", null, "xs:date"],
            "name3" => "xs:decimal"
        ]);

        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = Assertion::fromXML(DOMDocumentFactory::fromString($assertionElement)->documentElement);

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
        $this->assertEquals(Constants::NAMEFORMAT_UNSPECIFIED, $assertionToVerify->getAttributeNameFormat());

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

        // Create Conditions
        $conditions = new Conditions(
            null,
            null,
            [],
            [
                new AudienceRestriction(
                    ['audience1', 'audience2']
                )
            ]
        );

        // Create AuthnStatement
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                ["idp1", "idp2"]
            )
        );

        // Create AttributeStatement
        $attributeStatement = new AttributeStatement(
            // Attribute
            [
                new Attribute('name1', null, null, [new AttributeValue('value1'), new AttributeValue('2017-31-12')]),
                new Attribute('name2', null, null, [new AttributeValue(2)]),
                new Attribute('name3', null, null, [new AttributeValue(1234), new AttributeValue('+2345')])
            ],
            // EncryptedAttribute
            []
        );

        // Create an assertion
        $assertion = new Assertion($issuer, null, null, null, $conditions, [$authnStatement, $attributeStatement]);

        // set wrong number elements in name1
        $assertion->setAttributesValueTypes([
            "name1" => ["xs:string"],
            "name3" => "xs:decimal"
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Array of value types and array of values have different size for attribute 'name1'"
        );
        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();
    }


    /**
     * @group Assertion
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

        $privateKey = PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY);

        $unsignedAssertion = Assertion::fromXML($document->documentElement);
        $unsignedAssertion->setSigningKey($privateKey);
        $unsignedAssertion->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY)]);
        $this->assertFalse($unsignedAssertion->wasSignedAtConstruction());
        $this->assertEquals($privateKey, $unsignedAssertion->getSigningKey());

        $signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());

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

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $attributes = $assertion->getAttributes();

        $maceValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $oidValue = $attributes['urn:oid:1.3.6.1.4.1.5923.1.1.1.10'][0];

        $this->assertInstanceOf(NameID::class, $maceValue);
        $this->assertInstanceOf(NameID::class, $oidValue);

        $this->assertEquals('abcd-some-value-xyz', $maceValue->getValue());
        $this->assertEquals('abcd-some-value-xyz', $oidValue->getValue());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceValue->getFormat());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $oidValue->getFormat());
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }
*/
/**
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

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
        $attributes = $assertion->getAttributes();
        $maceValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $oidValue = $attributes['urn:oid:1.3.6.1.4.1.5923.1.1.1.10'][0];

//        $this->assertInstanceOf(NameID::class, $maceValue);
//        $this->assertInstanceOf(NameID::class, $oidValue);

        $this->assertEquals('string-23', $maceValue->getValue());
        $this->assertEquals('string-12', $oidValue->getValue());
    }
*/

    /**
     * as per http://software.internet2.edu/eduperson/internet2-mace-dir-eduperson-201310.html#eduPersonTargetedID
     * it is multivalued
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

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $attributes = $assertion->getAttributes();

        $maceFirstValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $maceSecondValue = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][1];

        $this->assertInstanceOf(NameID::class, $maceFirstValue);
        $this->assertInstanceOf(NameID::class, $maceSecondValue);

        $this->assertEquals('abcd-some-value-xyz', $maceFirstValue->getValue());
        $this->assertEquals('xyz-some-value-abcd', $maceSecondValue->getValue());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceFirstValue->getFormat());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $maceSecondValue->getFormat());

        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }
     */


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

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $attributes = $assertion->getAttributes();
        $this->assertInstanceOf(
            DOMNodeList::class,
            $attributes['urn:some:custom:outer:element'][0]
        );
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }

/**
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

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $attributes = $assertion->getAttributes();
        $this->assertIsInt($attributes['urn:some:integer'][0]);
        $this->assertIsString($attributes['urn:some:string'][0]);
        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }
*/
/**
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

        $privateKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256);

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
        $assertion->setEncryptionKey($privateKey);
        $assertion->setRequiredEncAttributes(true);
        $this->assertEquals($privateKey, $assertion->getEncryptionKey());

        $encryptedAssertion = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = Assertion::fromXML(DOMDocumentFactory::fromString($encryptedAssertion)->documentElement);

        $this->assertTrue($assertionToVerify->hasEncryptedAttributes());

        $assertionToVerify->decryptAttributes(PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY));

        $attributes = $assertionToVerify->getAttributes();
        $this->assertInstanceOf(
            DOMNodeList::class,
            $attributes['urn:some:custom:outer:element'][0]
        );
        $this->assertXmlStringEqualsXmlString($xml, $assertionToVerify->toXML()->ownerDocument->saveXML());
    }
*/

/**
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

        $privateKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256);

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
        $assertion->setEncryptionKey($privateKey);
        $assertion->setRequiredEncAttributes(true);
        $encryptedAssertion = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = Assertion::fromXML(DOMDocumentFactory::fromString($encryptedAssertion)->documentElement);

        $this->assertTrue($assertionToVerify->hasEncryptedAttributes());

        $assertionToVerify->decryptAttributes(PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY));
        $attributes = $assertionToVerify->getAttributes();

        $this->assertIsInt($attributes['urn:some:integer'][0]);
        $this->assertIsString($attributes['urn:some:string'][0]);
        $this->assertXmlStringEqualsXmlString($xml, $assertionToVerify->toXML()->ownerDocument->saveXML());
    }
*/

    /**
     * Try to verify a signed assertion.
     */
    public function testVerifySignedAssertion(): void
    {
        $doc = new DOMDocument();
        $doc->load('tests/resources/xml/assertions/signedassertion.xml');

        $publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        $assertion = Assertion::fromXML($doc->documentElement);
        $result = $assertion->validate($publicKey);

        $this->assertTrue($result);
        // Double-check that we can actually retrieve some basics.
        $this->assertEquals("_d908a49b8b63665738430d1c5b655f297b91331864", $assertion->getId());
        $this->assertEquals(
            "https://idp.example.org/simplesaml/saml2/idp/metadata.php",
            $assertion->getIssuer()->getValue()
        );
        $this->assertEquals("1457707995", $assertion->getIssueInstant());

        $certs = $assertion->getCertificates();
        $this->assertCount(1, $certs);
        $this->assertEquals(PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY), $certs[0]);

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
        $doc->load('tests/resources/xml/assertions/signedassertion_with_comments.xml');

        $publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        $assertion = Assertion::fromXML($doc->documentElement);
        $result = $assertion->validate($publicKey);

        $this->assertTrue($result);
        $this->assertEquals("_1bbcf227253269d19a689c53cdd542fe2384a9538b", $assertion->getSubject()->getIdentifier()->getValue());
    }


    /**
     * Try to verify a signed assertion in which a byte was changed after signing.
     * Must yield a validation exception.
     */
    public function testVerifySignedAssertionChangedBody(): void
    {
        $doc = new DOMDocument();
        $doc->load('tests/resources/xml/assertions/signedassertion_tampered.xml');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference validation failed');

        Assertion::fromXML($doc->documentElement);
    }


    /**
     * Try to verify a signed assertion with the wrong key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKey(): void
    {
        $doc = new DOMDocument();
        $doc->load('tests/resources/xml/assertions/signedassertion.xml');

        $publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::OTHER_PUBLIC_KEY);

        $assertion = Assertion::fromXML($doc->documentElement);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');
        $assertion->validate($publicKey);
    }


    /**
     * Try to verify an assertion signed with RSA with a DSA public key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKeyDSA(): void
    {
        $doc = new DOMDocument();
        $doc->load('tests/resources/xml/assertions/signedassertion.xml');

        $publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY, PEMCertificatesMock::ALG_SIG_DSA);

        $assertion = Assertion::fromXML($doc->documentElement);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');
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
        $assertion = Assertion::fromXML($document->documentElement);

        // Was not signed
        $this->assertFalse($assertion->wasSignedAtConstruction());

        $publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PUBLIC_KEY);
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported version: "1.3"');
        Assertion::fromXML($document->documentElement);
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing \'ID\' attribute on saml:Assertion');
        Assertion::fromXML($document->documentElement);
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing or more than one <saml:Issuer> in assertion.');
        Assertion::fromXML($document->documentElement);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('More than one <saml:Subject> in <saml:Assertion>');
        Assertion::fromXML($document->documentElement);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>');
        Assertion::fromXML($document->documentElement);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('More than one <saml:Conditions> in <saml:Assertion>');
        Assertion::fromXML($document->documentElement);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown namespace of condition:');
        Assertion::fromXML($document->documentElement);
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

        $assertion = Assertion::fromXML($document->documentElement);
        $conditions = $assertion->getConditions();
        $this->assertNotNull($conditions);

        $audienceRestrictions = $conditions->getAudienceRestriction();
        $this->assertCount(1, $audienceRestrictions);

        $restriction1 = $audienceRestrictions[0];
        $this->assertCount(1, $restriction1->getAudience());
        $this->assertEquals(['audience1'], $restriction1->getAudience());

        $restriction2 = $audienceRestrictions[1];
        $this->assertCount(2, $restriction2->getAudience());
        $this->assertEquals(['audience2', 'audience1'], $restriction1->getAudience());
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown condition: 'OtherCondition'");
        Assertion::fromXML($document->documentElement);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("More than one <saml:AuthnStatement> in <saml:Assertion> not supported");
        Assertion::fromXML($document->documentElement);
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

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $nameFormat = $assertion->getAttributeNameFormat();
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified', $nameFormat);
    }


    /**
     * Test basic NameID unmarshalling.
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
      <saml:AuthnContextDeclRef>/relative/path/to/document.xml</saml:AuthnContextDeclRef>
    </saml:AuthnContext>
  </saml:AuthnStatement>
</saml:Assertion>
XML;

        $document = DOMDocumentFactory::fromString($xml);

        $assertion = Assertion::fromXML($document->documentElement);

        $nameID = $assertion->getSubject()->getIdentifier();
        $this->assertInstanceOf(NameID::class, $nameID);
        $this->assertEquals('b7de81420a19416', $nameID->getValue());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->getFormat());
    }
     */


    /**
     * Test NameID Encryption and Decryption.
    public function testNameIdEncryption(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create an assertion
        $assertion = new Assertion();
        $assertion->setIssuer($issuer);
        $assertion->setConditions(
            new Conditions(
                null,
                null,
                [],
                [
                    new AudienceRestriction(
                        ['audience1', 'audience2']
                    )
                ]
            )
        );
        $assertion->setAuthnContextClassRef('someAuthnContext');

        $nameId = new NameID("just_a_basic_identifier", null, null, Constants::NAMEID_TRANSIENT);
        $this->assertInstanceOf(NameID::class, $nameId);

        $publicKey = PEMCertificatesMock::getPublicKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PUBLIC_KEY);
        $assertion->encryptNameId($publicKey);
        $this->assertTrue($assertion->isNameIdEncrypted());

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = Assertion::fromXML(DOMDocumentFactory::fromString($assertionElement)->documentElement);

        $identifier = $assertionToVerify->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $identifier);

        $privateKey = PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY);
        $nameID = $identifier->decrypt($privateKey, []);

        $this->assertEquals('just_a_basic_identifier', $nameID->getValue());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->getFormat());
    }
     */


    /**
     * Test Exception when trying to get encrypted NameId without
     * decrypting it first.
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
    <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Type="http://www.w3.org/2001/04/xmlenc#Element">
    <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
    <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <xenc:EncryptedKey><xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
      <xenc:CipherData><xenc:CipherValue>Y78/DDeSkI4qECUPXJM1cWUTaYVglxnqDRpjcqd6zdIR6yWMwIzUCd+fa9KhKutN4kN1i/koSMNmk+c6uOXSi0Xuohth61eU9oIwLl6mKZwThXEQiuphAtMVPXtooKfU1l58+xWiiO2IidYmtb1vCcVD0hZwnVv28kxrMQdQmzw=</xenc:CipherValue></xenc:CipherData>
      </xenc:EncryptedKey>
   </ds:KeyInfo>
   <xenc:CipherData>
     <xenc:CipherValue>cfQoRV0xf+D5bOQs+8icVEkWX4MRNxl1MhImqO/GwYxjCwj0AH/9O4kr2v4WZ4MC3zHhUjcq4HO70/xrkzQVMN9pBsF2yv9sUuN2rEPd8k/Oj/OA3X4xGNywxoJILioh56OyNkFK/q4WRptvvSQV1vPc0G5y65MZBiR2fy+L+ukBJ8mnzxL7aIIEKRxNa0beKdrrZ2twWH3Uwn3UW5LcSefaY+VHcM/9I4Xb7U5QWxRXzBOEa6v/a3cZ/TmlXYkj</xenc:CipherValue>
   </xenc:CipherData>
   </xenc:EncryptedData>
  </saml:EncryptedID>
  </saml:Subject>
</saml:Assertion>
XML;
        $document = DOMDocumentFactory::fromString($xml);

        $assertion = Assertion::fromXML($document->documentElement);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Attempted to retrieve encrypted NameID without decrypting it first");
        $assertion->getNameID();
    }
     */


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
        $assertion->setSigningKey(PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY));

        $nameId = new NameID("just_a_basic_identifier", Constants::NAMEID_TRANSIENT);
        $assertion->setSubject(new Subject($nameId));
        $assertion->setAuthnContextClassRef('someAuthnContext');

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $issuerElements = XMLUtils::xpQuery($assertionElement, './saml_assertion:Issuer');
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);
        // Test ordering of Assertion contents
        $assertionElements = XMLUtils::xpQuery($assertionElement, './saml_assertion:Issuer/following-sibling::*');
        $this->assertCount(5, $assertionElements);
        $this->assertEquals('ds:Signature', $assertionElements[0]->tagName);
        $this->assertEquals('saml:Subject', $assertionElements[1]->tagName);
        $this->assertEquals('saml:Conditions', $assertionElements[2]->tagName);
        $this->assertEquals('saml:AuthnStatement', $assertionElements[3]->tagName);
        $this->assertEquals('saml:AttributeStatement', $assertionElements[4]->tagName);
    }


    /**
     * Test that encryption / decryption of assertions works.
     */
    public function testEncryption(): void
    {
        $this->markTestSkipped('This test is not ready to run since Assertion does not extend AbstractXMLElement');
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
        $assertion = Assertion::fromXML($document->documentElement);

        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::PUBLIC_KEY_PEM);

        $encass = EncryptedAssertion::fromUnencryptedElement($assertion, $pubkey);
        $doc = DOMDocumentFactory::fromString((string) $encass);
        $encass = EncryptedAssertion::fromXML($doc->documentElement);
        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::PRIVATE_KEY_PEM);
        $decrypted = $encass->decrypt($privkey);
        $this->assertEquals((string) $assertion, (string) $decrypted);
    }
}
