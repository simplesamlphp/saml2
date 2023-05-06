<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\AuthnRequestTest
 */
class AuthnRequestTest extends TestCase
{
    public function testUnmarshalling(): void
    {
        $authnRequest = new AuthnRequest();
        $authnRequest->setRequestedAuthnContext([
            'AuthnContextClassRef' => [
                'accr1',
                'accr2',
            ],
            'Comparison' => 'better',
        ]);

        $authnRequestElement = $authnRequest->toUnsignedXML();

        $xpCache = XPath::getXPath($authnRequestElement);
        $requestedAuthnContextElements = XPath::xpQuery(
            $authnRequestElement,
            './saml_protocol:RequestedAuthnContext',
            $xpCache,
        );
        $this->assertCount(1, $requestedAuthnContextElements);

        $requestedAuthnContextElement = $requestedAuthnContextElements[0];
        $this->assertEquals('better', $requestedAuthnContextElement->getAttribute("Comparison"));

        $xpCache = XPath::getXPath($requestedAuthnContextElement);
        $authnContextClassRefElements = XPath::xpQuery(
            $requestedAuthnContextElement,
            './saml_assertion:AuthnContextClassRef',
            $xpCache,
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

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xml)->documentElement);

        $expectedIssueInstant = XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z');
        $this->assertEquals($expectedIssueInstant, $authnRequest->getIssueInstant());
        $this->assertEquals('https://idp.example.org/SAML2/SSO/Artifact', $authnRequest->getDestination());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact', $authnRequest->getProtocolBinding());
        $this->assertEquals(
            'https://sp.example.com/SAML2/SSO/Artifact',
            $authnRequest->getAssertionConsumerServiceURL()
        );
        $this->assertEquals('https://sp.example.com/SAML2', $authnRequest->getIssuer()->getValue());
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
        $authnRequest = new AuthnRequest($document->documentElement);

        $this->assertXmlStringEqualsXmlString($document->C14N(), $authnRequest->toUnsignedXML()->C14N());
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

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xml)->documentElement);

        $nameId = $authnRequest->getNameId();
        $this->assertEquals("user@example.org", $nameId->getValue());
        $this->assertEquals("urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified", $nameId->getFormat());
    }


    public function testThatTheSubjectCanBeSetBySettingTheNameId(): void
    {
        $request = new AuthnRequest();
        $nameId = new NameID();
        $nameId->setValue('user@example.org');
        $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
        $request->setNameId($nameId);

        $requestAsXML = $request->toUnsignedXML()->ownerDocument->saveXML();
        $expected = '<saml:Subject><saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID></saml:Subject>';
        $this->assertStringContainsString($expected, $requestAsXML);
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
        <saml:EncryptedID xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:dsig="http://www.w3.org/2000/09/xmldsig#">
            <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:dsig="http://www.w3.org/2000/09/xmldsig#" Type="http://www.w3.org/2001/04/xmlenc#Element">
                <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
                <dsig:KeyInfo xmlns:dsig="http://www.w3.org/2000/09/xmldsig#">
                    <xenc:EncryptedKey>
                        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
                        <xenc:CipherData>
                            <xenc:CipherValue>Kzb231F/6iLrDG9KP99h1C08eV2WfRqasU0c3y9AG+nb0JFdQgqip5+5FN+ypi1zPz4FIdoPufXdQDIRi4tm1UMyaiA5MBHjk2GOw5GDc6idnzFAoy4uWlofELeeT2ftcP4c6ETDsu++iANi5XUU1A+WPxxel2NMss6F6MjOuCg=</xenc:CipherValue>
                        </xenc:CipherData>
                    </xenc:EncryptedKey>
                </dsig:KeyInfo>
                <xenc:CipherData>
                    <xenc:CipherValue>EHj4x8ZwXvxIHFo4uenQcXZsUnS0VPyhevIMwE6YfejFwW0V3vUImCVKfdEtMJgNS/suukvc/HmF2wHptBqk3yjwbRfdFX2axO7UPqyThiGkVTkccOpIv7RzN8mkiDe9cjOztIQYd1DfKrjgh+FFL10o08W+HSZFgp4XQGOAruLj+JVyoDlx6FMyTIRgeLxlW4K2G1++Xmp8wyLyoMCccdDRzX3KT/Ph2RVIDpE/XLznpQd19sgwaEguUerqdHwo</xenc:CipherValue>
                </xenc:CipherData>
            </xenc:EncryptedData>
        </saml:EncryptedID>
    </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xml)->documentElement);

        $key = CertificatesMock::getPrivateKey();
        $authnRequest->decryptNameId($key);

        $nameId = $authnRequest->getNameId();
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
        // the NameID we're going to encrypt
        $nameId = new NameID();
        $nameId->setValue(md5('Arthur Dent'));
        $nameId->setFormat(Constants::NAMEID_ENCRYPTED);

        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setNameId($nameId);

        // encrypt the NameID
        $key = CertificatesMock::getPublicKey();
        $request->encryptNameId($key);

        $requestStructure = $request->toUnsignedXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($requestStructure);
        $issuerElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('https://gateway.example.org/saml20/sp/metadata', $issuerElements[0]->textContent);

        // Test ordering of AuthnRequest contents
        $requestElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(1, $requestElements);
        $this->assertEquals('saml:Subject', $requestElements[0]->tagName);

        // Test existence of EncryptedID
        $encryptedIdElements = XPath::xpQuery(
            $requestStructure,
            './saml_assertion:Subject/saml_assertion:EncryptedID',
            $xpCache,
        );
        $this->assertCount(1, $encryptedIdElements);
    }


    /**
     * Test for setting IDPEntry values via setIDPList.
     * Tests legacy support (single string), array of attributes, and skipping of unknown attributes.
     */
    public function testIDPlistAttributes(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIDPList([
            'Legacy1',
            ['ProviderID' => 'http://example.org/AAP', 'Name' => 'N00T', 'Loc' => 'https://mies'],
            ['ProviderID' => 'urn:example:1', 'Name' => 'Voorbeeld', 'Something' => 'Else']
        ]);

        $requestStructure = $request->toUnsignedXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($requestStructure);
        $issuerElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('https://gateway.example.org/saml20/sp/metadata', $issuerElements[0]->textContent);

        // Test ordering of AuthnRequest contents
        $requestElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(1, $requestElements);
        $this->assertEquals('samlp:Scoping', $requestElements[0]->tagName);

        // Test existence of IDPEntry
        $idpEntryElements = XPath::xpQuery(
            $requestStructure,
            './saml_protocol:Scoping/saml_protocol:IDPList/saml_protocol:IDPEntry',
            $xpCache,
        );
        $this->assertCount(3, $idpEntryElements);
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

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xmlRequest)->firstChild);

        $expectedList = ["Legacy1", "http://example.org/AAP", "urn:example:1"];

        $list = $authnRequest->getIDPList();
        $this->assertCount(3, $list);
        $this->assertEquals($expectedList, $list);
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

        $this->expectException(\Exception::class, 'Could not get ProviderID');
        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xmlRequest)->firstChild);
    }


    /**
     * Test setting a requesterID.
     */
    public function testRequesterIdIsAddedCorrectly(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setRequesterID([
            'https://engine.demo.openconext.org/authentication/sp/metadata',
            'https://shib.example.edu/SSO/Metadata',
        ]);

        $requestStructure = $request->toUnsignedXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($requestStructure);
        $issuerElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('https://gateway.example.org/saml20/sp/metadata', $issuerElements[0]->textContent);

        // Test ordering of AuthnRequest contents
        $requestElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(1, $requestElements);
        $this->assertEquals('samlp:Scoping', $requestElements[0]->tagName);

        // Test existence of RequesterID
        $requesterIdElements = XPath::xpQuery(
            $requestStructure,
            './saml_protocol:Scoping/saml_protocol:RequesterID',
            $xpCache
        );
        $this->assertCount(2, $requesterIdElements);
    }


    /**
     * Test reading a requesterID.
     */
    public function testRequesterIdIsReadCorrectly(): void
    {
        $requesterId = [
            'https://engine.demo.openconext.org/authentication/sp/metadata',
            'https://shib.example.edu/SSO/Metadata',
        ];

        $xmlRequest = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_1234567890abvdefghijkl"
    Version="2.0"
    IssueInstant="2015-05-11T09:02:36Z"
    Destination="https://some.sp.invalid/acs">
    <saml:Issuer>https://some.sp.invalid/metadata</saml:Issuer>
    <samlp:Scoping>
        <samlp:RequesterID>https://engine.demo.openconext.org/authentication/sp/metadata</samlp:RequesterID>
        <samlp:RequesterID>https://shib.example.edu/SSO/Metadata</samlp:RequesterID>
    </samlp:Scoping>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xmlRequest)->firstChild);

        $this->assertEquals($requesterId, $authnRequest->getRequesterID());
    }


    /**
     * Test setting a ProxyCount.
     */
    public function testProxyCountIsAddedCorrectly(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));
        $request->setProxyCount(34);
        $request->setRequesterID([
            'https://engine.demo.openconext.org/authentication/sp/metadata',
        ]);

        $requestStructure = $request->toUnsignedXML();

        // Test existence of RequesterID
        $xpCache = XPath::getXPath($requestStructure);
        $scopingElements = XPath::xpQuery(
            $requestStructure,
            './saml_protocol:Scoping',
            $xpCache,
        );
        $this->assertCount(1, $scopingElements);

        $proxyCount = $scopingElements[0]->getAttribute('ProxyCount');
        $this->assertEquals('34', $proxyCount);
    }


    /**
     * Test reading ProxyCount
     */
    public function testProxyCountIsReadCorrectly(): void
    {
        $proxyCount = 10;

        $xmlRequest = <<<AUTHNREQUEST
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="CORTO9d0394481f7c05d0ebe26263926f72e1bc5bac17"
                    Version="2.0"
                    IssueInstant="2016-12-27T15:51:00Z"
                    Destination="https://idp.surfnet.nl/saml2/idp/SSOService.php"
                    AssertionConsumerServiceURL="https://engine.example.org/authentication/sp/consume-assertion"
                    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                    >
    <saml:Issuer>https://engine.example.org/authentication/sp/metadata</saml:Issuer>
    <samlp:NameIDPolicy AllowCreate="true" />
    <samlp:Scoping ProxyCount="10">
        <samlp:RequesterID>https://profile.example.org/authentication/metadata</samlp:RequesterID>
    </samlp:Scoping>
</samlp:AuthnRequest>
AUTHNREQUEST;

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xmlRequest)->firstChild);

        $this->assertEquals($proxyCount, $authnRequest->getProxyCount());
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
        $authnRequest = new AuthnRequest($document->documentElement);

        $nameIdPolicy = $authnRequest->getNameIdPolicy();

        $this->assertCount(3, $nameIdPolicy);

        $this->assertArrayHasKey('AllowCreate', $nameIdPolicy);
        $this->assertArrayHasKey('SPNameQualifier', $nameIdPolicy);
        $this->assertArrayHasKey('Format', $nameIdPolicy);

        $this->assertEquals(true, $nameIdPolicy['AllowCreate']);
        $this->assertEquals("https://sp.example.com/SAML2", $nameIdPolicy['SPNameQualifier']);
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:nameid-format:transient", $nameIdPolicy['Format']);
    }


    /**
     * Test setting NameIDPolicy results in expected XML
     */
    public function testSettingNameIDPolicy(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));

        $nameIdPolicy = [
            "Format" => "urn:oasis:names:tc:SAML:2.0:nameid-format:transient",
            "SPNameQualifier" => "https://sp.example.com/SAML2",
            "AllowCreate" => true
        ];
        $request->setNameIDPolicy($nameIdPolicy);

        $requestStructure = $request->toUnsignedXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($requestStructure);
        $issuerElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('https://gateway.example.org/saml20/sp/metadata', $issuerElements[0]->textContent);

        // Test ordering of AuthnRequest contents
        $requestElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(1, $requestElements);
        $this->assertEquals('samlp:NameIDPolicy', $requestElements[0]->tagName);

        // Test NameIDPolicy
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:nameid-format:transient", $requestElements[0]->getAttribute('Format'));
        $this->assertEquals("https://sp.example.com/SAML2", $requestElements[0]->getAttribute('SPNameQualifier'));
        $this->assertEquals('true', $requestElements[0]->getAttribute('AllowCreate'));
    }


    /**
     * Test setting NameIDPolicy with only a Format results in expected XML
     */
    public function testSettingNameIDPolicyFormatOnly(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));

        $nameIdPolicy = ["Format" => "urn:oasis:names:tc:SAML:2.0:nameid-format:transient"];
        $request->setNameIDPolicy($nameIdPolicy);

        $requestStructure = $request->toUnsignedXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($requestStructure);
        $issuerElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('https://gateway.example.org/saml20/sp/metadata', $issuerElements[0]->textContent);

        // Test ordering of AuthnRequest contents
        $requestElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(1, $requestElements);
        $this->assertEquals('samlp:NameIDPolicy', $requestElements[0]->tagName);

        // Test NameIDPolicy
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:nameid-format:transient", $requestElements[0]->getAttribute('Format'));
        $this->assertFalse($requestElements[0]->hasAttribute('SPNameQualifier'));
        $this->assertFalse($requestElements[0]->hasAttribute('AllowCreate'));
    }


    /**
     * Test setting NameIDPolicy with invalid type for AllowCreate.
     */
    public function testSettingNameIDPolicyToIncorrectTypeAllowCreate(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');

        // AllowCreate must be a bool
        $nameIdPolicy = ["AllowCreate" => "true"];
        $this->expectException(\InvalidArgumentException::class, 'Invalid Argument type: "bool" expected');
        $request->setNameIDPolicy($nameIdPolicy);
    }


    /**
     * Test setting NameIDPolicy with invalid type for SPNameQualifier.
     */
    public function testSettingNameIDPolicyToIncorrectTypeSPNameQualifier(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');

        // SPNameQualifier must be a string
        $nameIdPolicy = ["SPNameQualifier" => true];
        $this->expectException(\InvalidArgumentException::class, 'Invalid Argument type: "string" expected');
        $request->setNameIDPolicy($nameIdPolicy);
    }


    /**
     * Test setting NameIDPolicy with one invalid type for Format.
     * It would be nice to iterate over various types to check this more thoroughly.
     */
    public function testSettingNameIDPolicyToIncorrectTypeFormat(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');

        // Format must be a string
        $nameIdPolicy = ["Format" => 2.0];
        $this->expectException(\InvalidArgumentException::class, 'Invalid Argument type: "string" expected');
        $request->setNameIDPolicy($nameIdPolicy);
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
        $authnRequest = new AuthnRequest($document->documentElement);

        $this->assertFalse($authnRequest->getForceAuthn());

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
        $authnRequest = new AuthnRequest($document->documentElement);
        $this->assertTrue($authnRequest->getForceAuthn());
    }


    /**
     * Test setting ForceAuthn
     */
    public function testSettingForceAuthnResultsInCorrectXML(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));

        $request->setForceAuthn(true);

        $requestStructure = $request->toUnsignedXML();

        $this->assertEquals('true', $requestStructure->getAttribute('ForceAuthn'));
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
        $authnRequest = new AuthnRequest($document->documentElement);

        $this->assertFalse($authnRequest->getIsPassive());

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
        $authnRequest = new AuthnRequest($document->documentElement);
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
        $authnRequest = new AuthnRequest($document->documentElement);
        $this->assertTrue($authnRequest->getIsPassive());
    }


    /**
     * Test setting IsPassive
     */
    public function testSettingIsPassiveResultsInCorrectXML(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));

        $request->setIsPassive(true);

        $requestStructure = $request->toUnsignedXML();

        $this->assertEquals('true', $requestStructure->getAttribute('IsPassive'));
    }


    /**
     * Test setting ProviderName
     */
    public function testSettingProviderNameResultsInCorrectXml(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));

        $request->setProviderName("My Example SP");

        $requestStructure = $request->toUnsignedXML();

        $this->assertEquals('My Example SP', $requestStructure->getAttribute('ProviderName'));
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
        $authnRequest = new AuthnRequest($document->documentElement);

        $this->assertEquals("Example SP", $authnRequest->getProviderName());
    }


    /**
     * Test setting ProtocolBinding and AssertionConsumerServiceURL
     */
    public function testSettingProtocolBindingAndACSUrl(): void
    {
        // the Issuer
        $issuer = new Issuer();
        $issuer->setValue('https://sp.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://idp.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setIssueInstant(XMLUtils::xsDateTimeToTimestamp('2004-12-05T09:21:59Z'));

        $request->setProtocolBinding("urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST");
        $request->setAssertionConsumerServiceURL("https://sp.example.org/authentication/sp/consume-assertion");

        $requestStructure = $request->toUnsignedXML();

        $this->assertEquals(
            'https://sp.example.org/authentication/sp/consume-assertion',
            $requestStructure->getAttribute('AssertionConsumerServiceURL'),
        );
        $this->assertEquals(
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            $requestStructure->getAttribute('ProtocolBinding'),
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

        $this->expectException(\Exception::class, 'More than one <saml:Subject> in <saml:AuthnRequest>');
        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xml)->documentElement);
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

        $this->expectException(\Exception::class, 'More than one <saml:NameID> or <saml:EncryptedID> in <saml:Subject>');
        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xml)->documentElement);
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

        $this->expectException(\Exception::class, 'Missing <saml:NameID> or <saml:EncryptedID> in <saml:Subject>');
        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xml)->documentElement);
    }


    /**
     * Test setting audiences.
     */
    public function testAudiencesAreAddedCorrectly(): void
    {
        $issuer = new Issuer();
        $issuer->setValue('https://gateway.example.org/saml20/sp/metadata');

        // basic AuthnRequest
        $request = new AuthnRequest();
        $request->setIssuer($issuer);
        $request->setDestination('https://tiqr.example.org/idp/profile/saml2/Redirect/SSO');
        $request->setAudiences(array('https://sp1.example.org', 'https://sp2.example.org'));

        $requestStructure = $request->toUnsignedXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($requestStructure);
        $issuerElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $issuerElements);
        $this->assertEquals('https://gateway.example.org/saml20/sp/metadata', $issuerElements[0]->textContent);

        // Test ordering of AuthnRequest contents
        $requestElements = XPath::xpQuery($requestStructure, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(1, $requestElements);
        $this->assertEquals('saml:Conditions', $requestElements[0]->tagName);

        // Test existence of Audience
        $audienceElements = XPath::xpQuery(
            $requestStructure,
            './saml_assertion:Conditions/saml_assertion:AudienceRestriction/saml_assertion:Audience',
            $xpCache,
        );
        $this->assertCount(2, $audienceElements);
        $this->assertEquals('https://sp1.example.org', $audienceElements[0]->textContent);
        $this->assertEquals('https://sp2.example.org', $audienceElements[1]->textContent);
    }


    /**
     * Test reading audiences.
     */
    public function testAudiencesAreReadCorrectly(): void
    {
        $expectedAudiences = array('https://sp1.example.org', 'https://sp2.example.org');

        $xmlRequest = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_1234567890abvdefghijkl"
    Version="2.0"
    IssueInstant="2015-05-11T09:02:36Z"
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

        $authnRequest = new AuthnRequest(DOMDocumentFactory::fromString($xmlRequest)->firstChild);

        $this->assertEquals($expectedAudiences, $authnRequest->getAudiences());
    }
}
