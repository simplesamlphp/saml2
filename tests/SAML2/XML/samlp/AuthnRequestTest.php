<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\Comparison;
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
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
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
                value: 'user@example.org',
                Format: C::NAMEID_UNSPECIFIED,
            ),
        );

        $authnRequest = new AuthnRequest(
            subject: $subject,
            issuer: new Issuer('https://gateway.stepup.org/saml20/sp/metadata'),
            id: '_2b0226190ca1c22de6f66e85f5c95158',
            issueInstant: new DateTimeImmutable('2014-09-22T13:42:00Z'),
            destination: 'https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO',
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
                new AuthnContextClassRef('urn:test:accr1'),
                new AuthnContextClassRef('urn:test:accr2'),
            ],
            Comparison::BETTER,
        );

        // Create Subject
        $subject = new Subject(
            new Issuer('urn:x-simplesamlphp:issuer'),
        );

        // Create NameIDPolicy
        $nameIdPolicy = new NameIDPolicy(
            'urn:the:format',
            'urn:x-simplesamlphp:spnamequalifier',
            true,
        );

        // Create Conditions
        $conditions = new Conditions(
            new DateTimeImmutable('1970-01-01T01:33:31Z'),
            new DateTimeImmutable('1970-01-02T01:33:31Z'),
            [],
            [
                new AudienceRestriction(
                    [
                        new Audience('http://sp.example.com/demo1/metadata.php'),
                    ],
                ),
            ],
            new OneTimeUse(),
            new ProxyRestriction(
                [
                    new Audience('http://sp.example.com/demo2/metadata.php'),
                ],
                2,
            ),
        );

        // Create Scoping
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'urn:test:testLoc1');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = new RequesterID('urn:some:requester');
        $scoping = new Scoping(2, $list, [$requesterId]);

        $authnRequest = new AuthnRequest(
            requestedAuthnContext: $rac,
            issueInstant: self::$clock->now(),
            subject: $subject,
            nameIdPolicy: $nameIdPolicy,
            conditions: $conditions,
            issuer: new Issuer('https://gateway.stepup.org/saml20/sp/metadata'),
            scoping: $scoping,
        );

        $authnRequestElement = $authnRequest->toXML();

        // Test for a Subject
        $xpCache = XPath::getXPath($authnRequestElement);
        $authnRequestElements = XPath::xpQuery($authnRequestElement, './saml_assertion:Subject', $xpCache);
        $this->assertCount(1, $authnRequestElements);

        // Test ordering of AuthnRequest contents
        /** @psalm-var \DOMElement[] $authnRequestElements */
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
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
    <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
        $issuer = $authnRequest->getIssuer();
        $this->assertEquals('2004-12-05T09:21:59Z', $authnRequest->getIssueInstant()->format(C::DATETIME_FORMAT));
        $this->assertEquals('https://idp.example.org/SAML2/SSO/Artifact', $authnRequest->getDestination());
        $this->assertEquals(C::BINDING_HTTP_ARTIFACT, $authnRequest->getProtocolBinding());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $authnRequest->getAssertionConsumerServiceURL(),
        );
        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('https://sp.example.com/SAML2', $issuer->getContent());
    }


    /**
     * Test unmarshalling / marshalling of XML with Extensions element
     */
    public function testExtensionOrdering(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
  <samlp:Extensions>
      <myns:AttributeList xmlns:myns="urn:test:mynamespace">
          <myns:Attribute name="UserName" value=""/>
      </myns:AttributeList>
  </samlp:Extensions>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
  <samlp:NameIDPolicy
    AllowCreate="true"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"/>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);

        /** @psalm-var \DOMDocument $e->ownerDocument */
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

        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $identifier->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
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
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eadf"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
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
        $issuer = new Issuer('https://sp.example.org/saml20/sp/metadata');
        $issueInstant = new DateTimeImmutable('2004-12-05T09:21:59Z');
        $destination = 'https://idp.example.org/idp/profile/saml2/Redirect/SSO';
        $protocolBinding = C::BINDING_HTTP_POST;
        $assertionConsumerServiceIndex = 1;

        $this->expectException(ProtocolViolationException::class);
        new AuthnRequest(
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
        $issuer = new Issuer('https://sp.example.org/saml20/sp/metadata');
        $issueInstant = new DateTimeImmutable('2004-12-05T09:21:59Z');
        $destination = 'https://idp.example.org/idp/profile/saml2/Redirect/SSO';
        $assertionConsumerServiceIndex = 1;
        $assertionConsumerServiceURL = "https://sp.example.org/authentication/sp/consume-assertion";

        $this->expectException(ProtocolViolationException::class);
        new AuthnRequest(
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
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO">
  <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
  <saml:Subject>
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
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO">
  <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
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
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO">
  <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
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
