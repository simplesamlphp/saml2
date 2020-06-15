<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\CertificatesMock;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\MissingAttributeException;
use SAML2\Exception\TooManyElementsException;
use SAML2\XML\saml\AudienceRestriction;
use SAML2\XML\saml\AuthnContextClassRef;
use SAML2\XML\saml\Conditions;
use SAML2\XML\saml\EncryptedID;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\Subject;
use SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\samlp\AuthnRequestTest
 */
class AuthnRequestTest extends TestCase
{
    public function testUnmarshalling(): void
    {
        $rac = new RequestedAuthnContext(
            [
                new AuthnContextClassRef('accr1'),
                new AuthnContextClassRef('accr2')
            ],
            'better'
        );
        $authnRequest = new AuthnRequest($rac);

        $authnRequestElement = $authnRequest->toXML();

        $requestedAuthnContextElements = RequestedAuthnContext::getChildrenOfClass($authnRequestElement);
        $this->assertCount(1, $requestedAuthnContextElements);

        $requestedAuthnConextElement = $requestedAuthnContextElements[0];
        $this->assertEquals('better', $requestedAuthnConextElement->getAttribute("Comparison"));

        $authnContextClassRefElements = Utils::xpQuery(
            $requestedAuthnConextElement,
            './saml_assertion:AuthnContextClassRef'
        );
        $this->assertCount(2, $authnContextClassRefElements);
        $this->assertEquals('accr1', $authnContextClassRefElements[0]->textContent);
        $this->assertEquals('accr2', $authnContextClassRefElements[1]->textContent);
    }


    public function testMarshallingOfSimpleRequest(): void
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
        $expectedIssueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $this->assertEquals($expectedIssueInstant, $authnRequest->getIssueInstant());
        $this->assertEquals('https://idp.example.org/SAML2/SSO/Artifact', $authnRequest->getDestination());
        $this->assertEquals(Constants::BINDING_HTTP_ARTIFACT, $authnRequest->getProtocolBinding());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $authnRequest->getAssertionConsumerServiceURL()
        );
        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('https://sp.example.com/SAML2', $issuer->getValue());
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
  <samlp:Extensions>
      <myns:AttributeList xmlns:myns="urn:mynamespace">
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

        $this->assertXmlStringEqualsXmlString($document->C14N(), $authnRequest->toXML()->C14N());
    }


    public function testThatTheSubjectIsCorrectlyRead(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
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
        $this->assertEquals("user@example.org", $nameId->getValue());
        $this->assertEquals(Constants::NAMEID_UNSPECIFIED, $nameId->getFormat());
    }


    public function testThatTheSubjectCanBeSetBySettingTheNameId(): void
    {
        $nameId = new NameID('user@example.org', null, null, Constants::NAMEID_UNSPECIFIED);
        $request = new AuthnRequest(null, new Subject($nameId));
        /** @psalm-var \DOMDocument $document */
        $document = $request->toXML()->ownerDocument;

        $expected = '<saml:Subject><saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID></saml:Subject>';
        $this->assertStringContainsString($expected, $document->saveXML());
    }


    public function testThatAnEncryptedNameIdCanBeDecrypted(): void
    {
        $xml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    Version="2.0"
    IssueInstant="2015-05-11T09:02:36Z"
    Destination="https://tiqr.example.org/idp/profile/saml2/Redirect/SSO">
    <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
    <saml:Subject>
        <saml:EncryptedID xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Type="http://www.w3.org/2001/04/xmlenc#Element">
                <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
                <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                    <xenc:EncryptedKey>
                        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
                        <xenc:CipherData>
                            <xenc:CipherValue>Kzb231F/6iLrDG9KP99h1C08eV2WfRqasU0c3y9AG+nb0JFdQgqip5+5FN+ypi1zPz4FIdoPufXdQDIRi4tm1UMyaiA5MBHjk2GOw5GDc6idnzFAoy4uWlofELeeT2ftcP4c6ETDsu++iANi5XUU1A+WPxxel2NMss6F6MjOuCg=</xenc:CipherValue>
                        </xenc:CipherData>
                    </xenc:EncryptedKey>
                </ds:KeyInfo>
                <xenc:CipherData>
                    <xenc:CipherValue>EHj4x8ZwXvxIHFo4uenQcXZsUnS0VPyhevIMwE6YfejFwW0V3vUImCVKfdEtMJgNS/suukvc/HmF2wHptBqk3yjwbRfdFX2axO7UPqyThiGkVTkccOpIv7RzN8mkiDe9cjOztIQYd1DfKrjgh+FFL10o08W+HSZFgp4XQGOAruLj+JVyoDlx6FMyTIRgeLxlW4K2G1++Xmp8wyLyoMCccdDRzX3KT/Ph2RVIDpE/XLznpQd19sgwaEguUerqdHwo</xenc:CipherValue>
                </xenc:CipherData>
            </xenc:EncryptedData>
        </saml:EncryptedID>
    </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);

        $key = CertificatesMock::getPrivateKey();
        $subject = $authnRequest->getSubject();
        $this->assertInstanceOf(Subject::class, $subject);

        $identifier = $subject->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $identifier);

        $nameId = $identifier->decrypt($key);
        $this->assertInstanceOf(NameID::class, $nameId);

        $this->assertEquals(md5('Arthur Dent'), $nameId->getValue());
        $this->assertEquals(Constants::NAMEID_ENCRYPTED, $nameId->getFormat());
    }


    /**
     * Due to the fact that the symmetric key is generated each time, we cannot test whether or not the resulting XML
     * matches a specific XML, but we can test whether or not the resulting structure is actually correct, conveying
     * all information required to decrypt the NameId.
     */
    public function testThatAnEncryptedNameIdResultsInTheCorrectXmlStructure(): void
    {
        // create an encrypted NameID
        $key = CertificatesMock::getPublicKey();

        /** @psalm-var \SAML2\XML\saml\IdentifierInterface $nameId */
        $nameId = EncryptedID::fromUnencryptedElement(
            new NameID(md5('Arthur Dent'), Constants::NAMEID_ENCRYPTED),
            $key
        );

        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';

        // basic AuthnRequest
        $request = new AuthnRequest(null, new Subject($nameId), null, null, null, null, null, null, null, null, $issuer, null, null, $destination);



        $expectedXml = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID=""
    Version=""
    IssueInstant=""
    Destination="">
    <saml:Issuer></saml:Issuer>
    <saml:Subject>
        <saml:EncryptedID xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
            <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Type="http://www.w3.org/2001/04/xmlenc#Element">
                <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
                <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                    <xenc:EncryptedKey>
                        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
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
            null,
            new IDPList(
                [
                    new IDPEntry('Legacy1'),
                    new IDPEntry('http://example.org/AAP', 'N00T', 'https://mies'),
                    new IDPEntry('urn:example:1', 'Voorbeeld', 'Else')
                ]
            )
        );

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, null, null, null, null, null, null, null, null, $issuer, null, null, $destination, null, null, $scoping);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID=""
    Version=""
    IssueInstant=""
    Destination="">
    <saml:Issuer></saml:Issuer>
    <samlp:Scoping><samlp:IDPList>
        <samlp:IDPEntry ProviderID="Legacy1"/>
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
  IssueInstant="2004-12-05T09:21:59Z"
  Destination="https://idp.example.org/SAML2/SSO/Artifact"
  ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
  AssertionConsumerServiceURL="https://sp.example.com/SAML2/SSO/Artifact">
  <saml:Issuer>https://sp.example.com/SAML2</saml:Issuer>
    <samlp:Scoping><samlp:IDPList>
        <samlp:IDPEntry ProviderID="Legacy1"/>
        <samlp:IDPEntry ProviderID="http://example.org/AAP" Name="N00T" Loc="https://mies"/>
        <samlp:IDPEntry ProviderID="urn:example:1" Name="Voorbeeld"/>
    </samlp:IDPList></samlp:Scoping>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xmlRequest)->documentElement);

        $expectedList = [
            new IDPEntry('Legacy1'),
            new IDPEntry('http://example.org/AAP', 'N00T', 'https://mies'),
            new IDPEntry('urn:example:1', 'Voorbeeld')
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
  ID="_306f8ec5b618f361c70b6ffb1480eadf"
  Version="2.0"
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
        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xmlRequest)->documentElement);
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
        $this->assertEquals(Constants::NAMEID_TRANSIENT, $nameIdPolicy->getFormat());
    }


    /**
     * Test setting NameIDPolicy results in expected XML
     */
    public function testSettingNameIDPolicy(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');

        $nameIdPolicy = new NameIDPolicy(
            Constants::NAMEID_TRANSIENT,
            "https://sp.example.com/SAML2",
            true
        );

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, $nameIdPolicy, null, null, null, null, null, null, null, $issuer, null, $issueInstant, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    Version="2.0"
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

        $this->assertXmlStringEqualsXmlString($expectedStructure->ownerDocument->saveXML(), $requestStructure->ownerDocument->saveXML());
    }


    /**
     * Test setting NameIDPolicy with only a Format results in expected XML
     */
    public function testSettingNameIDPolicyFormatOnly(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $nameIdPolicy = new NameIDPolicy(Constants::NAMEID_TRANSIENT);

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, $nameIdPolicy, null, null, null, null, null, null, null, $issuer, null, $issueInstant, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    Version="2.0"
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

        $this->assertXmlStringEqualsXmlString($expectedStructure->ownerDocument->saveXML(), $requestStructure->ownerDocument->saveXML());
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $forceAuthn = true;

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, null, null, $forceAuthn, null, null, null, null, null, $issuer, null, $issueInstant, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    Version="2.0"
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

        $this->assertXmlStringEqualsXmlString($expectedStructure->ownerDocument->saveXML(), $requestStructure->ownerDocument->saveXML());
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $isPassive = true;

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, null, null, null, $isPassive, null, null, null, null, $issuer, null, $issueInstant, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    Version="2.0"
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

        $this->assertXmlStringEqualsXmlString($expectedStructure->ownerDocument->saveXML(), $requestStructure->ownerDocument->saveXML());
    }


    /**
     * Test setting ProviderName
     */
    public function testSettingProviderNameResultsInCorrectXml(): void
    {
        // the Issuer
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $providerName = "My Example SP";

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, null, null, null, null, null, null, null, $providerName, $issuer, null, $issueInstant, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    ProviderName="My Example SP"
    Version="2.0"
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

        $this->assertXmlStringEqualsXmlString($expectedStructure->ownerDocument->saveXML(), $requestStructure->ownerDocument->saveXML());
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
  ID="_306f8ec5b618f361c70b6ffb1480eade"
  Version="2.0"
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
     * Test setting ProtocolBinding and AssertionConsumerServiceURL
     */
    public function testSettingProtocolBindingAndACSUrl(): void
    {
        // the Issuer
        $issuer = new Issuer('https://sp.example.org/saml20/sp/metadata');
        $issueInstant = Utils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $destination = 'https://idp.example.org/idp/profile/saml2/Redirect/SSO';
        $protocolBinding = Constants::BINDING_HTTP_POST;
        $assertionConsumerServiceURL = "https://sp.example.org/authentication/sp/consume-assertion";

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, null, null, null, null, $assertionConsumerServiceURL, $protocolBinding, null, null, $issuer, null, $issueInstant, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="123"
    Version="2.0"
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

        $this->assertXmlStringEqualsXmlString($expectedStructure->ownerDocument->saveXML(), $requestStructure->ownerDocument->saveXML());
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
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
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
        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
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
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
        <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">aabbcc</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:Subject>.');
        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
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
    AssertionConsumerServiceIndex="1"
    Destination="https://idp.example.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer>https://gateway.example.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>');
        $authnRequest = AuthnRequest::fromXML(DOMDocumentFactory::fromString($xml)->documentElement);
    }


    /**
     * Test setting audiences.
     */
    public function testAudiencesAreAddedCorrectly(): void
    {
        $issuer = new Issuer('https://gateway.example.org/saml20/sp/metadata');
        $destination = 'https://tiqr.example.org/idp/profile/saml2/Redirect/SSO';
        $conditions = new Conditions(
            null,
            null,
            [],
            [
                new AudienceRestriction(
                    [
                        'https://sp1.example.org',
                        'https://sp2.example.org'
                    ]
                )
            ]
        );

        // basic AuthnRequest
        $request = new AuthnRequest(null, null, null, $conditions, null, null, null, null, null, null, $issuer, null, null, $destination);

        $expectedStructureDocument = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID=""
    Version=""
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
