<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\ProxyRestriction;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\SAML2\XML\samlp\RequesterID;
use SimpleSAML\SAML2\XML\samlp\Scoping;
use SimpleSAML\Test\SAML2\SignedElementTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\AuthnRequestTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\AuthnRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AuthnRequestTest extends TestCase
{
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = AuthnRequest::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
            issueInstant: 1411393320,
            destination: 'https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO',
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
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
            'better',
        );

        // Create Subject
        $subject = new Subject(
            new Issuer('some issuer'),
        );

        // Create NameIDPolicy
        $nameIdPolicy = new NameIDPolicy(
            'urn:the:format',
            'TheSPNameQualifier',
            true,
        );

        // Create Conditions
        $conditions = new Conditions(
            1405558878,
            1705558908,
            [],
            [
                new AudienceRestriction(
                    [
                        new Audience('http://sp.example.com/demo1/metadata.php'),
                    ],
                ),
            ],
            true,
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
            subject: $subject,
            nameIdPolicy: $nameIdPolicy,
            conditions: $conditions,
            issuer: new Issuer('https://gateway.stepup.org/saml20/sp/metadata'),
            scoping: $scoping
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
        $expectedIssueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $this->assertEquals($expectedIssueInstant, $authnRequest->getIssueInstant());
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


    public function testThatTheSubjectIsCorrectlyRead(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO"
    Version="2.0"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z">
  <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $subject = $authnRequest->getSubject();
        $this->assertInstanceOf(Subject::class, $subject);

        $nameId = $subject->getIdentifier();
        $this->assertInstanceOf(NameID::class, $nameId);
        $this->assertEquals("user@example.org", $nameId->getContent());
        $this->assertEquals(C::NAMEID_UNSPECIFIED, $nameId->getFormat());
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
            $identifier->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
        );

        $nameId = $identifier->decrypt($decryptor);
        $this->assertInstanceOf(NameID::class, $nameId);

        $this->assertEquals('very secret', $nameId->getContent());
    }


    /**
     * Due to the fact that the symmetric key is generated each time, we cannot test whether or not the resulting XML
     * matches a specific XML, but we can test whether or not the resulting structure is actually correct, conveying
     * all information required to decrypt the NameId.
     */
    public function testThatAnEncryptedNameIdResultsInTheCorrectXmlStructure(): void
    {
        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        // create an encrypted NameID
        $nameId = new NameID(md5('Arthur Dent'));
        $nameId = new EncryptedID($nameId->encrypt($encryptor));

        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';

        // basic AuthnRequest
        $request = new AuthnRequest(
            subject: new Subject($nameId),
            issuer: $issuer,
            destination: $destination,
        );

        $expectedXml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version=""
    ID=""
    IssueInstant=""
    Destination="">
  <saml:Issuer></saml:Issuer>
  <saml:Subject>
    <saml:EncryptedID xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <xenc:EncryptedData
          xmlns:xenc="http://www.w3.org/2001/04/xmlenc#"
          xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
          Type="http://www.w3.org/2001/04/xmlenc#Element">
        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
          <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <xenc:EncryptedKey>
              <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
              <xenc:CipherData>
                <xenc:CipherValue></xenc:CipherValue>
              </xenc:CipherData>
            </xenc:EncryptedKey>
          </ds:KeyInfo>
          <xenc:CipherData>
          <xenc:CipherValue></xenc:CipherValue>
        </xenc:CipherData>
      </xenc:EncryptedData>
    </saml:EncryptedID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $expectedStructure = DOMDocumentFactory::fromString($expectedXml)->documentElement;
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);
    }


    /**
     * Test for setting IDPEntry values via setIDPList.
     * Tests legacy support (single string), array of attributes, and skipping of unknown attributes.
     */
    public function testIDPlistAttributes(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';

        $scoping = new Scoping(
            IDPList: new IDPList(
                [
                    new IDPEntry('urn:test:Legacy1'),
                    new IDPEntry('http://example.org/AAP', 'N00T', 'https://mies'),
                    new IDPEntry('urn:example:1', 'Voorbeeld', 'urn:test:else'),
                ],
            ),
        );

        // basic AuthnRequest
        $request = new AuthnRequest(
            issuer: $issuer,
            destination: $destination,
            scoping: $scoping,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version=""
    ID=""
    IssueInstant=""
    Destination="">
    <saml:Issuer></saml:Issuer>
    <samlp:Scoping><samlp:IDPList>
        <samlp:IDPEntry ProviderID="urn:test:Legacy1"/>
        <samlp:IDPEntry ProviderID="http://example.org/AAP" Name="N00T" Loc="https://mies"/>
        <samlp:IDPEntry ProviderID="urn:example:1" Name="Voorbeeld"/>
    </samlp:IDPList></samlp:Scoping>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);
    }


    /**
     * Test for getting IDPlist values.
     */
    public function testgetIDPlistAttributes(): void
    {
        $xmlRequest = <<<AUTHNREQUEST
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
    <samlp:Scoping><samlp:IDPList>
        <samlp:IDPEntry ProviderID="urn:test:Legacy1"/>
        <samlp:IDPEntry ProviderID="http://example.org/AAP" Name="N00T" Loc="https://mies"/>
        <samlp:IDPEntry ProviderID="urn:example:1" Name="Voorbeeld"/>
    </samlp:IDPList></samlp:Scoping>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xmlRequest)->documentElement);

        $expectedList = [
            new IDPEntry('urn:test:Legacy1'),
            new IDPEntry('http://example.org/AAP', 'N00T', 'https://mies'),
            new IDPEntry('urn:example:1', 'Voorbeeld'),
        ];

        $scoping = $authnRequest->getScoping();
        $this->assertInstanceOf(Scoping::class, $scoping);

        $list = $scoping->getIDPList();
        $this->assertInstanceOf(IDPList::class, $list);

        $entries = $list->getIdpEntry();
        $this->assertCount(3, $entries);
        $this->assertEquals($expectedList, $entries);
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
     * Test getting NameIDPolicy
     */
    public function testGettingNameIDPolicy(): void
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
  <samlp:NameIDPolicy
    AllowCreate="true"
    SPNameQualifier="https://sp.example.com/SAML2"
    Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"/>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);

        $nameIdPolicy = $authnRequest->getNameIdPolicy();

        $this->assertInstanceOf(NameIDPolicy::class, $nameIdPolicy);
        $this->assertEquals(true, $nameIdPolicy->getAllowCreate());
        $this->assertEquals("https://sp.example.com/SAML2", $nameIdPolicy->getSPNameQualifier());
        $this->assertEquals(C::NAMEID_TRANSIENT, $nameIdPolicy->getFormat());
    }


    /**
     * Test setting NameIDPolicy results in expected XML
     */
    public function testSettingNameIDPolicy(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');

        $nameIdPolicy = new NameIDPolicy(
            C::NAMEID_TRANSIENT,
            "https://sp.example.com/SAML2",
            true,
        );

        // basic AuthnRequest
        $request = new AuthnRequest(
            nameIdPolicy: $nameIdPolicy,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="123"
    IssueInstant="2004-12-05T09:21:59Z"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
    <samlp:NameIDPolicy
        Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"
        SPNameQualifier="https://sp.example.com/SAML2" AllowCreate="true"
    />
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);

        $this->assertXmlStringEqualsXmlString(
            $expectedStructure->ownerDocument->saveXML(),
            $requestStructure->ownerDocument->saveXML(),
        );
    }


    /**
     * Test setting NameIDPolicy with only a Format results in expected XML
     */
    public function testSettingNameIDPolicyFormatOnly(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $nameIdPolicy = new NameIDPolicy(C::NAMEID_TRANSIENT);

        // basic AuthnRequest
        $request = new AuthnRequest(
            nameIdPolicy: $nameIdPolicy,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="123"
    IssueInstant="2004-12-05T09:21:59Z"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
    <samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"/>
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);

        $this->assertXmlStringEqualsXmlString(
            $expectedStructure->ownerDocument->saveXML(),
            $requestStructure->ownerDocument->saveXML(),
        );
    }


    /**
     * Test getting ForceAuthn
     */
    public function testGettingForceAuthn(): void
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
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);

        $this->assertNull($authnRequest->getForceAuthn());

        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  ForceAuthn="true"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);
        $this->assertTrue($authnRequest->getForceAuthn());
    }


    /**
     * Test setting ForceAuthn
     */
    public function testSettingForceAuthnResultsInCorrectXML(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $forceAuthn = true;

        // basic AuthnRequest
        $request = new AuthnRequest(
            forceAuthn: $forceAuthn,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="123"
    ForceAuthn="true"
    IssueInstant="2004-12-05T09:21:59Z"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);

        $this->assertXmlStringEqualsXmlString(
            $expectedStructure->ownerDocument->saveXML(),
            $requestStructure->ownerDocument->saveXML(),
        );
    }


    /**
     * Test getting IsPassive
     */
    public function testGettingIsPassive(): void
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
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);

        $this->assertNull($authnRequest->getIsPassive());

        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest IsPassive="false"
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);
        $this->assertFalse($authnRequest->getIsPassive());

        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest IsPassive="true"
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);
        $this->assertTrue($authnRequest->getIsPassive());
    }


    /**
     * Test setting IsPassive
     */
    public function testSettingIsPassiveResultsInCorrectXML(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $isPassive = true;

        // basic AuthnRequest
        $request = new AuthnRequest(
            isPassive: $isPassive,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="123"
    IsPassive="true"
    IssueInstant="2004-12-05T09:21:59Z"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);

        $this->assertXmlStringEqualsXmlString(
            $expectedStructure->ownerDocument->saveXML(),
            $requestStructure->ownerDocument->saveXML(),
        );
    }


    /**
     * Test setting ProviderName
     */
    public function testSettingProviderNameResultsInCorrectXml(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $providerName = "My Example SP";

        // basic AuthnRequest
        $request = new AuthnRequest(
            ProviderName: $providerName,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="123"
    ProviderName="My Example SP"
    IssueInstant="2004-12-05T09:21:59Z"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);

        $this->assertXmlStringEqualsXmlString(
            $expectedStructure->ownerDocument->saveXML(),
            $requestStructure->ownerDocument->saveXML(),
        );
    }


    /**
     * Test getting ProviderName
     */
    public function testGettingProviderName(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Version="2.0"
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProviderName="Example SP"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $document     = DOMDocumentFactory::fromString($xml);
        $authnRequest = AuthnRequest::fromXML($document->documentElement);

        $this->assertEquals("Example SP", $authnRequest->getProviderName());
    }


    /**
     * Test setting ProtocolBinding and AssertionConsumerServiceIndex
     * throws a ProtocolViolationException.
     */
    public function testSettingProtocolBindingAndACSIndex(): void
    {
        // the Issuer
        $issuer = new Issuer('https://sp.example.org/saml20/sp/metadata');
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
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
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
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
     * Test setting ProtocolBinding and AssertionConsumerServiceURL
     */
    public function testSettingProtocolBindingAndACSUrl(): void
    {
        // the Issuer
        $issuer = new Issuer('https://sp.example.org/saml20/sp/metadata');
        $issueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $destination = 'https://idp.example.org/idp/profile/saml2/Redirect/SSO';
        $protocolBinding = C::BINDING_HTTP_POST;
        $assertionConsumerServiceURL = "https://sp.example.org/authentication/sp/consume-assertion";

        // basic AuthnRequest
        $request = new AuthnRequest(
            assertionConsumerServiceURL: $assertionConsumerServiceURL,
            protocolBinding: $protocolBinding,
            issuer: $issuer,
            issueInstant: $issueInstant,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="123"
    IssueInstant="2004-12-05T09:21:59Z"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO"
    AssertionConsumerServiceURL="https://sp.example.org/authentication/sp/consume-assertion"
    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
>
    <saml:Issuer>https://sp.example.org/saml20/sp/metadata</saml:Issuer>
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();

        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);

        $this->assertXmlStringEqualsXmlString(
            $expectedStructure->ownerDocument->saveXML(),
            $requestStructure->ownerDocument->saveXML(),
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
            . 'exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>'
        );
        AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
    }


    /**
     * Test setting audiences.
     */
    public function testAudiencesAreAddedCorrectly(): void
    {
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $conditions = new Conditions(
            audienceRestriction: [
                new AudienceRestriction(
                    [
                        new Audience('https://sp1.example.org'),
                        new Audience('https://sp2.example.org'),
                    ],
                ),
            ],
        );

        // basic AuthnRequest
        $request = new AuthnRequest(
            conditions: $conditions,
            issuer: $issuer,
            destination: $destination,
        );

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version=""
    ID=""
    IssueInstant=""
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
    <saml:Conditions>
      <saml:AudienceRestriction>
        <saml:Audience>https://sp1.example.org</saml:Audience>
        <saml:Audience>https://sp2.example.org</saml:Audience>
      </saml:AudienceRestriction>
    </saml:Conditions>
</samlp:AuthnRequest>
AUTHNREQUEST;

        /** @psalm-var \DOMDocument $expectedStructure->ownerDocument */
        $expectedStructure = DOMDocumentFactory::fromString($expectedStructureDocument)->documentElement;
        /** @psalm-var \DOMDocument $requestStructure->ownerDocument */
        $requestStructure = $request->toXML();
        $this->assertEqualXMLStructure($expectedStructure, $requestStructure);
    }
}
