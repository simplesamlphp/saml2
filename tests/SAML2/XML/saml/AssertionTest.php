<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use DOMNodeList;
use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SAML2\XML\saml\SubjectLocality;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;
use function time;

/**
 * Class \SimpleSAML\SAML2\AssertionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Assertion
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AssertionTest extends MockeryTestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = Assertion::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Assertion.xml'
        );

        $container = new MockContainer();
        $container->setBlacklistedAlgorithms(null);
        ContainerSingleton::setContainer($container);
    }


    /**
     * Test to build a basic assertion
     */
    public function testMarshalling(): void
    {
        // Create an Issuer
        $issuer = new Issuer('Provider');

        // Create the conditions
        $conditions = new Conditions(
            1314780665,
            1314780665,
            [],
            [new AudienceRestriction([new Audience('ServiceProvider')])]
        );

        // Create the AuthnStatement
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT),
                null,
                null
            ),
            1314780665,
            null,
            '_93af655219464fb403b34436cfb0c5cb1d9a5502',
            new SubjectLocality('127.0.0.1')
        );

        // Create the AttributeStatement
        $attrStatement = new AttributeStatement(
            [
                new Attribute('urn:ServiceID', null, null, [new AttributeValue('1')]),
                new Attribute('urn:EntityConcernedID', null, null, [new AttributeValue('1')]),
                new Attribute('urn:EntityConcernedSubID', null, null, [new AttributeValue('1')])
            ]
        );

        // Create the Subject
        $subject = new Subject(
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                C::NAMEID_TRANSIENT,
                null
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        C::NAMEID_TRANSIENT,
                        null
                    ),
                    new SubjectConfirmationData(
                        null,
                        1314780665,
                        'https://sp.example.org/authentication/sp/consume-assertion',
                        '_13603a6565a69297e9809175b052d115965121c8'
                    )
                )
            ]
        );

        // Create an assertion
        $assertion = new Assertion(
            $issuer,
            '_93af655219464fb403b34436cfb0c5cb1d9a5502',
            5611,
            $subject,
            $conditions,
            [$authnStatement, $attrStatement]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertion)
        );
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

        $audience = $restriction1->getAudience();
        $this->assertEquals('audience1', $audience[0]->getContent());
        $this->assertEquals('audience2', $audience[1]->getContent());

        // Test for Authenticating Authorities
        $assertionAuthenticatingAuthorities = $assertion->getAuthnStatements()[0]->getAuthnContext()->getAuthenticatingAuthorities();
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

        // Create Conditions
        $conditions = new Conditions(
            1234567880,
            1234567990,
            [],
            [
                new AudienceRestriction(
                    [new Audience('audience1'), new Audience('audience2')]
                )
            ]
        );

        // Create AuthnStatement
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef('someAuthnContext'),
                null,
                new AuthnContextDeclRef('/relative/path/to/document.xml'),
                [new AuthenticatingAuthority("idp1"), new AuthenticatingAuthority("idp2")]
            ),
            1234567890 - 1,
            1234568890 + 200,
            'idx1',
            new SubjectLocality('127.0.0.1', 'no.place.like.home')
        );

        // Create AttributeStatement
        $attributeStatement = new AttributeStatement(
            // Attribute
            [
                new Attribute('name1', null, null, [new AttributeValue('value1'), new AttributeValue('value2')]),
                new Attribute('name2', C::NAMEFORMAT_UNSPECIFIED, null, [new AttributeValue(2)]),
                new Attribute('name3', C::NAMEFORMAT_BASIC, null, [new AttributeValue(null)])
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

        $authnStatements = $assertionToVerify->getAuthnStatements();
        $this->assertCount(1, $authnStatements);

        $authnStatement = $authnStatements[0];
        $this->assertEquals(
            '/relative/path/to/document.xml',
            $authnStatement->getAuthnContext()->getAuthnContextDeclRef()->getContent()
        );
        $this->assertEquals('_123abc', $assertionToVerify->getId());
        $this->assertEquals(1234567890, $assertionToVerify->getIssueInstant());
        $this->assertEquals(1234569090, $authnStatement->getSessionNotOnOrAfter());
        $this->assertEquals(1234567889, $authnStatement->getAuthnInstant());
        $this->assertEquals('idx1', $authnStatement->getSessionIndex());

        $subjectLocality = $authnStatement->getSubjectLocality();
        $this->assertEquals('127.0.0.1', $subjectLocality->getAddress());
        $this->assertEquals('no.place.like.home', $subjectLocality->getDnsName());

        $authauth = $authnStatement->getAuthnContext()->getAuthenticatingAuthorities();
        $this->assertCount(2, $authauth);
        $this->assertEquals("idp2", $authauth[1]->getContent());

        $attributeStatements = $assertionToVerify->getAttributeStatements();
        $this->assertCount(1, $attributeStatements);

        $attributeStatement = $attributeStatements[0];
        $attributes = $attributeStatement->getAttributes();

        $this->assertCount(3, $attributes);
        $this->assertCount(2, $attributes[0]->getAttributeValues());
        $this->assertEquals("value1", $attributes[0]->getAttributeValues()[0]->getValue());
        $this->assertEquals(2, $attributes[1]->getAttributeValues()[0]->getValue());
        $this->assertNull($attributes[2]->getAttributeValues()[0]->getValue());

        $this->assertNull($attributes[0]->getNameFormat());
        $this->assertEquals(C::NAMEFORMAT_UNSPECIFIED, $attributes[1]->getNameFormat());
        $this->assertEquals(C::NAMEFORMAT_BASIC, $attributes[2]->getNameFormat());
    }


    // @tvdijen: We have no way to set a type xs:date right now
    /**
     * Test an assertion attribute value types options
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
            ),
            time()
        );

        // Create AttributeStatement
        $attributeStatement = new AttributeStatement(
            // Attribute
            [
                new Attribute('name1', null, null, [new AttributeValue('value1'), new AttributeValue(123), new AttributeValue('2017-31-12')]),
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
        $this->assertEquals(C::NAMEFORMAT_UNSPECIFIED, $assertionToVerify->getAttributeNameFormat());

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
     */


    /**
     * @group Assertion
     */
    public function testCorrectSignatureMethodCanBeExtracted(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
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

        $privateKey = PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY);
        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            $privateKey,
        );
        $keyInfo = new KeyInfo([
            new X509Data([
                new X509Certificate(PEMCertificatesMock::getPlainPublicKeyContents(PEMCertificatesMock::PUBLIC_KEY)),
            ]),
        ]);

        $unsignedAssertion = Assertion::fromXML($document->documentElement);
        $this->assertFalse($unsignedAssertion->wasSignedAtConstruction());

        $unsignedAssertion->sign($signer, C::C14N_EXCLUSIVE_WITHOUT_COMMENTS, $keyInfo);
        $signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());

        $this->assertEquals(C::SIG_RSA_SHA256, $signedAssertion->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm());
        $this->assertTrue($signedAssertion->wasSignedAtConstruction());
    }

    public function testEptiAttributeValuesAreParsedCorrectly(): void
    {
        $xml = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
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
            <saml:AttributeValue>string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $attributes = $assertion->getAttributeStatements()[0]->getAttributes();

        $maceValue = $attributes[1]->getAttributeValues()[0];
        $oidValue = $attributes[0]->getAttributeValues()[0];

        /** @psalm-var (\SimpleSAML\SAML2\XML\saml\AttributeValue|\SimpleSAML\SAML2\XML\saml\IdentifierInterface)[] $mValue */
        $mValue = $maceValue->getValue();

        /** @psalm-var (\SimpleSAML\SAML2\XML\saml\AttributeValue|\SimpleSAML\SAML2\XML\saml\IdentifierInterface)[] $oValue */
        $oValue = $oidValue->getValue();

        $this->assertInstanceOf(NameID::class, $mValue[0]);
        $this->assertInstanceOf(NameID::class, $oValue[0]);

        $this->assertEquals('abcd-some-value-xyz', $mValue[0]->getContent());
        $this->assertEquals('abcd-some-value-xyz', $oValue[0]->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $mValue[0]->getFormat());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $oValue[0]->getFormat());
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
        $attributes = $assertion->getAttributeStatements()[0]->getAttributes();
        $maceValue = $attributes[1]->getAttributeValues()[0];
        $oidValue = $attributes[0]->getAttributeValues()[0];

        $this->assertEquals('string-23', $maceValue->getValue());
        $this->assertEquals('string-12', $oidValue->getValue());
    }


    /**
     * See: http://software.internet2.edu/eduperson/internet2-mace-dir-eduperson-201310.html#eduPersonTargetedID
     * As per specification the eduPersonTargetedID-attribute is multivalued
     */
    public function testEptiAttributeParsingSupportsMultipleValues(): void
    {
        $xml
            = <<<XML
            <saml:Assertion
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
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
            <saml:AttributeValue>string</saml:AttributeValue>
        </saml:Attribute>
      </saml:AttributeStatement>
    </saml:Assertion>
XML;

        $assertion = Assertion::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $attributes = $assertion->getAttributeStatements()[0]->getAttributes();

        $values = $attributes[0]->getAttributeValues();
        $this->assertCount(2, $values);
        $maceFirstValue = $values[0];
        $maceSecondValue = $values[1];

        /** @psalm-var (\SimpleSAML\SAML2\XML\saml\AttributeValue|\SimpleSAML\SAML2\XML\saml\IdentifierInterface)[] $firstValue */
        $firstValue = $maceFirstValue->getValue();

        /** @psalm-var (\SimpleSAML\SAML2\XML\saml\AttributeValue|\SimpleSAML\SAML2\XML\saml\IdentifierInterface)[] $secondValue */
        $secondValue = $maceSecondValue->getValue();

        $this->assertInstanceOf(NameID::class, $firstValue[0]);
        $this->assertInstanceOf(NameID::class, $secondValue[0]);

        $this->assertEquals('abcd-some-value-xyz', $firstValue[0]->getContent());
        $this->assertEquals('xyz-some-value-abcd', $secondValue[0]->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $firstValue[0]->getFormat());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $secondValue[0]->getFormat());

        $this->assertXmlStringEqualsXmlString($xml, $assertion->toXML()->ownerDocument->saveXML());
    }


    /**
     * Try to verify a signed assertion.
     */
    public function testVerifySignedAssertion(): void
    {
        $doc = DOMDocumentFactory::fromFile('tests/resources/xml/assertions/signedassertion.xml');
        $assertion = Assertion::fromXML($doc->documentElement);

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $assertion->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        $verified = $assertion->verify($verifier);

        // Double-check that we can actually retrieve some basics.
        $this->assertEquals("_d908a49b8b63665738430d1c5b655f297b91331864", $verified->getId());
        $this->assertEquals(
            "https://idp.example.org/simplesaml/saml2/idp/metadata.php",
            $verified->getIssuer()->getContent()
        );
        $this->assertEquals("1457707995", $verified->getIssueInstant());

        // Was signed
        $this->assertTrue($verified->wasSignedAtConstruction());
    }


    /**
     * Make sure an assertion whose signature verifies cannot be tampered by using XML comments.
     * @see https://duo.com/labs/psa/duo-psa-2017-003
     */
    public function testCommentsInSignedAssertion(): void
    {
        $doc = DOMDocumentFactory::fromFile('tests/resources/xml/assertions/signedassertion_with_comments.xml');
        $assertion = Assertion::fromXML($doc->documentElement);

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $assertion->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        $verified = $assertion->verify($verifier);

        $subject = $verified->getSubject();

        $identifier = $subject->getIdentifier();
        $this->assertEquals("_1bbcf227253269d19a689c53cdd542fe2384a9538b", $identifier->getContent());
    }


    /**
     * Try to verify a signed assertion in which a byte was changed after signing.
     * Must yield a validation exception.
     */
    public function testVerifySignedAssertionChangedBody(): void
    {
        $doc = DOMDocumentFactory::fromFile('tests/resources/xml/assertions/signedassertion_tampered.xml');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference validation failed');

        Assertion::fromXML($doc->documentElement);
    }


    /**
     * Try to verify a signed assertion with the wrong key algorithm.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongAlgorithm(): void
    {
        $doc = DOMDocumentFactory::fromFile('tests/resources/xml/assertions/signedassertion.xml');
        $assertion = Assertion::fromXML($doc->documentElement);

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA384,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Algorithm provided in key does not match algorithm used in signature.');

        $assertion->verify($verifier);
    }


    /**
     * Try to verify a signed assertion with the wrong key.
     * Must yield a signature validation exception.
     */
    public function testVerifySignedAssertionWrongKey(): void
    {
        $doc = DOMDocumentFactory::fromFile('tests/resources/xml/assertions/signedassertion.xml');
        $assertion = Assertion::fromXML($doc->documentElement);

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $assertion->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');

        $assertion->verify($verifier);
    }


    /**
     * Try to verify an assertion signed with RSA with a DSA public key.
     * Must yield a signature validation exception.
    public function testVerifySignedAssertionWrongKeyDSA(): void
    {
        $doc = DOMDocumentFactory::fromFile('tests/resources/xml/assertions/signedassertion.xml');
        $assertion = Assertion::fromXML($doc->documentElement);

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $assertion->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to validate Signature');

        $assertion->verify($verifier);
    }
     */


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
        $this->assertNull($assertion->getSignature());
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
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing <saml:Issuer> in assertion.');
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
        $this->assertCount(2, $audienceRestrictions);

        $restriction1 = $audienceRestrictions[0];
        $audience = $restriction1->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('audience1', $audience[0]->getContent());

        $restriction2 = $audienceRestrictions[1];
        $audience = $restriction2->getAudience();
        $this->assertCount(2, $audience);
        $this->assertEquals('audience2', $audience[0]->getContent());
        $this->assertEquals('audience1', $audience[1]->getContent());
    }


    /**
     * Test NameID Encryption and Decryption.
     */
    public function testNameIdEncryption(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create the Conditions
        $conditions = new Conditions(
            null,
            null,
            [],
            [
                new AudienceRestriction(
                    [new Audience('audience1'), new Audience('audience2')]
                )
            ]
        );

        // Create a Subject
        $nameId = new NameID("just_a_basic_identifier", null, null, C::NAMEID_TRANSIENT);
        $this->assertInstanceOf(NameID::class, $nameId);

        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
        );
        $encId = new EncryptedID($nameId->encrypt($encryptor));

        $subject = new Subject($encId);

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
        $assertion = new Assertion(
            $issuer,
            null,
            null,
            $subject,
            $conditions,
            [$authnStatement]
        );

        // Marshall it to a \DOMElement
        $assertionElement = $assertion->toXML()->ownerDocument->saveXML();

        $assertionToVerify = Assertion::fromXML(DOMDocumentFactory::fromString($assertionElement)->documentElement);

        $identifier = $assertionToVerify->getSubject()->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $identifier);

        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $identifier->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );
        $nameID = $identifier->decrypt($decryptor);

        $this->assertInstanceOf(NameID::class, $nameID);
        $this->assertEquals('just_a_basic_identifier', $nameID->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:transient', $nameID->getFormat());
    }


    public function testMarshallingElementOrdering(): void
    {
        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create the conditions
        $conditions = new Conditions(
            null,
            null,
            [],
            [new AudienceRestriction([new Audience('audience1'), new Audience('audience2')])]
        );

        // Create AttributeStatement
        $attributeStatement = new AttributeStatement(
            // Attribute
            [
                new Attribute(
                    'name1',
                    C::NAMEFORMAT_UNSPECIFIED,
                    null,
                    [new AttributeValue('value1'), new AttributeValue('value2')]
                ),
                new Attribute('name2', C::NAMEFORMAT_UNSPECIFIED, null, [new AttributeValue('value3')]),
            ],
            // EncryptedAttribute
            []
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

        // Create Subject
        $subject = new Subject(
            new NameID("just_a_basic_identifier", C::NAMEID_TRANSIENT)
        );

        $statements = [$authnStatement, $attributeStatement];

        // Create a signed assertion
        $assertion = new Assertion($issuer, null, null, $subject, $conditions, $statements);
        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );

        // Marshall it to a \DOMElement
        $assertion->sign($signer);
        $assertionElement = $assertion->toXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($assertionElement);
        $issuerElements = XPath::xpQuery($assertionElement, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('testIssuer', $issuerElements[0]->textContent);

        // Test ordering of Assertion contents
        /** @psalm-var \DOMElement[] $assertionElements */
        $assertionElements = XPath::xpQuery(
            $assertionElement,
            './saml_assertion:Issuer/following-sibling::*',
            $xpCache
        );
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

        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY),
        );

        $encass = new EncryptedAssertion($assertion->encrypt($encryptor));
        $doc = DOMDocumentFactory::fromString(strval($encass));

        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encass->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::OTHER_PRIVATE_KEY)
        );

        $decrypted = $encass->decrypt($decryptor);
        $this->assertEquals(strval($assertion), strval($decrypted));
    }
}
