<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\AuthnContextComparisonTypeValue;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\OneTimeUse;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\AuthnContextComparisonTypeEnum;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\SAML2\XML\samlp\RequesterID;
use SimpleSAML\SAML2\XML\samlp\Scoping;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue;
use SimpleSAML\XMLSchema\Type\UnsignedShortValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AuthnRequestTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(AuthnRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class AuthnRequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        self::$testedClass = AuthnRequest::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AuthnRequest.xml',
        );
    }


    // Marshalling


    public function testMarshalling(): void
    {
        $subject = new Subject(
            new NameID(
                value: SAMLStringValue::fromString('user@example.org'),
                Format: SAMLAnyURIValue::fromString(C::NAMEID_UNSPECIFIED),
            ),
        );

        $authnRequest = new AuthnRequest(
            subject: $subject,
            issuer: new Issuer(
                SAMLStringValue::fromString('https://gateway.stepup.org/saml20/sp/metadata'),
            ),
            consent: SAMLAnyURIValue::fromString(C::CONSENT_UNSPECIFIED),
            id: IDValue::fromString('_2b0226190ca1c22de6f66e85f5c95158'),
            issueInstant: SAMLDateTimeValue::fromString('2014-09-22T13:42:00Z'),
            destination: SAMLAnyURIValue::fromString('https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnRequest),
        );
    }


    public function testMarshallingElementOrdering(): void
    {
        // Create RequestedAuthnContext
        $rac = new RequestedAuthnContext(
            [
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString('urn:test:accr1'),
                ),
                new AuthnContextClassRef(
                    SAMLAnyURIValue::fromString('urn:test:accr2'),
                ),
            ],
            AuthnContextComparisonTypeValue::fromEnum(AuthnContextComparisonTypeEnum::Better),
        );

        // Create Subject
        $subject = new Subject(
            new Issuer(
                SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
            ),
        );

        // Create NameIDPolicy
        $nameIdPolicy = new NameIDPolicy(
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            BooleanValue::fromBoolean(true),
        );

        // Create Conditions
        $conditions = new Conditions(
            SAMLDateTimeValue::fromString('1970-01-01T01:33:31Z'),
            SAMLDateTimeValue::fromString('1970-01-02T01:33:31Z'),
            [],
            [
                new AudienceRestriction(
                    [
                        new Audience(
                            SAMLAnyURIValue::fromstring('http://sp.example.com/demo1/metadata.php'),
                        ),
                    ],
                ),
            ],
            new OneTimeUse(),
            new ProxyRestriction(
                [
                    new Audience(
                        SAMLAnyURIValue::fromString('http://sp.example.com/demo2/metadata.php'),
                    ),
                ],
                NonNegativeIntegerValue::fromInteger(2),
            ),
        );

        // Create Scoping
        $entry1 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester1'),
            SAMLStringValue::fromString('testName1'),
            SAMLAnyURIValue::fromString('urn:test:testLoc1'),
        );
        $getComplete = new GetComplete(
            SAMLAnyURIValue::fromString('https://some/location'),
        );
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = new RequesterID(
            EntityIDValue::fromString('urn:some:requester'),
        );
        $scoping = new Scoping(
            NonNegativeIntegerValue::fromInteger(2),
            $list,
            [$requesterId],
        );

        $authnRequest = new AuthnRequest(
            id: IDValue::fromString('SomeIDValue'),
            requestedAuthnContext: $rac,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            subject: $subject,
            nameIdPolicy: $nameIdPolicy,
            conditions: $conditions,
            issuer: new Issuer(
                SAMLStringValue::fromString('https://gateway.stepup.org/saml20/sp/metadata'),
            ),
            scoping: $scoping,
        );

        $authnRequestElement = $authnRequest->toXML();

        // Test for a Subject
        $xpCache = XPath::getXPath($authnRequestElement);
        $authnRequestElements = XPath::xpQuery($authnRequestElement, './saml_assertion:Subject', $xpCache);
        $this->assertCount(1, $authnRequestElements);

        // Test ordering of AuthnRequest contents
        /** @var \DOMElement[] $authnRequestElements */
        $authnRequestElements = XPath::xpQuery(
            $authnRequestElement,
            './saml_assertion:Subject/following-sibling::*',
            $xpCache,
        );
        $this->assertCount(4, $authnRequestElements);
        $this->assertEquals('samlp:NameIDPolicy', $authnRequestElements[0]->tagName);
        $this->assertEquals('saml:Conditions', $authnRequestElements[1]->tagName);
        $this->assertEquals('samlp:RequestedAuthnContext', $authnRequestElements[2]->tagName);
        $this->assertEquals('samlp:Scoping', $authnRequestElements[3]->tagName);
    }


    // Unmarshalling


    public function testUnmarshallingOfSimpleRequest(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
    <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
        $issuer = $authnRequest->getIssuer();
        $this->assertEquals('2004-12-05T09:21:59Z', $authnRequest->getIssueInstant()->getValue());
        $this->assertEquals('https://idp.example.org/SAML2/SSO/Artifact', $authnRequest->getDestination()->getValue());
        $this->assertEquals(C::BINDING_HTTP_ARTIFACT, $authnRequest->getProtocolBinding()->getValue());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $authnRequest->getAssertionConsumerServiceURL()->getValue(),
        );
        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('https://sp.example.com/SAML2', $issuer->getContent()->getValue());
    }


    /**
     * Test unmarshalling / marshalling of XML with Extensions element
     */
    public function testExtensionOrdering(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://sp.example.com/SAML2</saml:Issuer>
  <samlp:Extensions>
      <myns:AttributeList xmlns:myns="urn:test:mynamespace">
          <myns:Attribute name="UserName" value=""/>
      </myns:AttributeList>
  </samlp:Extensions>
  <saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
  <samlp:NameIDPolicy
    AllowCreate="true"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"/>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);

        $e = $authnRequest->toXML();
        $this->assertXmlStringEqualsXmlString($document->C14N(), $e->ownerDocument->C14N());
    }


    public function testThatAnEncryptedNameIdCanBeDecrypted(): void
    {
        $container = ContainerSingleton::getInstance();
        $container->setBlacklistedAlgorithms(null);

        $xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/authnrequest/authnrequest_encryptedid.xml',
        );
        $authnRequest = AuthnRequest::fromXML($xmlRepresentation->documentElement);

        $subject = $authnRequest->getSubject();
        $this->assertInstanceOf(Subject::class, $subject);

        $identifier = $subject->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $identifier);

        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $identifier->getEncryptedKeys()[0]->getEncryptionMethod()?->getAlgorithm()->getValue(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
        );

        $nameId = $identifier->decrypt($decryptor);
        $this->assertInstanceOf(NameID::class, $nameId);

        $this->assertEquals('very secret', $nameId->getContent());
    }


    /**
     * Test that parsing IDPList without ProviderID throws exception.
     */
    public function testScopeWithoutProviderIDThrowsException(): void
    {
        $xmlRequest = <<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eadf"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://sp.example.com/SAML2</saml:Issuer>
    <samlp:Scoping><samlp:IDPList>
        <samlp:IDPEntry Name="N00T" Loc="https://mies"/>
    </samlp:IDPList></samlp:Scoping>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'ProviderID\' attribute on samlp:IDPEntry.');
        AuthnRequest::fromXML(DOMDocumentFactory::fromString($xmlRequest)->documentElement);
    }


    /**
     * Test setting ProtocolBinding and AssertionConsumerServiceIndex
     * throws a ProtocolViolationException.
     */
    public function testSettingProtocolBindingAndACSIndex(): void
    {
        // the Issuer
        $issuer = new Issuer(
            SAMLStringValue::fromString('https://sp.example.org/saml20/sp/metadata'),
        );
        $issueInstant = SAMLDateTimeValue::fromString('2004-12-05T09:21:59Z');
        $destination = SAMLAnyURIValue::fromString('https://idp.example.org/idp/profile/saml2/Redirect/SSO');
        $protocolBinding = SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST);
        $assertionConsumerServiceIndex = UnsignedShortValue::fromInteger(1);

        $this->expectException(ProtocolViolationException::class);
        new AuthnRequest(
            id: IDValue::fromString('_306f8ec5b618f361c70b6ffb1480eadf'),
            assertionConsumerServiceIndex: $assertionConsumerServiceIndex,
            protocolBinding: $protocolBinding,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );
    }


    /**
     * Test setting AssertionConsumerServiceURL and AssertionConsumerServiceIndex
     * throws a ProtocolViolationException.
     */
    public function testSettingACSUrlAndACSIndex(): void
    {
        // the Issuer
        $issuer = new Issuer(
            SAMLStringValue::fromString('https://sp.example.org/saml20/sp/metadata'),
        );
        $issueInstant = SAMLDateTimeValue::fromString('2004-12-05T09:21:59Z');
        $destination = SAMLAnyURIValue::fromString('https://idp.example.org/idp/profile/saml2/Redirect/SSO');
        $assertionConsumerServiceIndex = UnsignedShortValue::fromInteger(1);
        $assertionConsumerServiceURL = SAMLAnyURIValue::fromString(
            "https://sp.example.org/authentication/sp/consume-assertion",
        );

        $this->expectException(ProtocolViolationException::class);
        new AuthnRequest(
            id: IDValue::fromString('SomeIDValue'),
            assertionConsumerServiceURL: $assertionConsumerServiceURL,
            assertionConsumerServiceIndex: $assertionConsumerServiceIndex,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );
    }


    /**
     * Test that having multiple subject tags throws an exception.
     */
    public function testMultipleSubjectsThrowsException(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO">
  <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
  <saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
        <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">aabbcc</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('Only one <saml:Subject> element is allowed.');
        AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
    }


    /**
     * Test that having multiple NameIds in a subject tag throws an exception.
     */
    public function testMultipleNameIdsInSubjectThrowsException(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO">
  <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
        <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">aabbcc</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:Subject>.');
        AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
    }


    /**
     * Test that a subject tag without a NameId throws an exception.
     */
    public function testEmptySubjectThrowsException(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO">
  <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide '
            . 'exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>',
        );
        AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
    }
}
