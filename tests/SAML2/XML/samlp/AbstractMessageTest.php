<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMDocument;
use Exception;
use InvalidArgumentException;
use SAML2\CertificatesMock;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\Issuer;
use SAML2\XML\samlp\AbstractMessage;
use SAML2\XML\samlp\Extensions;
use SAML2\XML\samlp\MessageFactory;
use SAML2\XML\samlp\Response;
use SAML2\XML\samlp\Status;
use SAML2\XML\samlp\StatusCode;

class AbstractMessageTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @group Message
     * @return void
     */
    public function testCorrectSignatureMethodCanBeExtractedFromAuthnRequest(): void
    {
        $authnRequest = new DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer>https://gateway.stepup.org/saml20/sp/metadata</saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $privateKey = CertificatesMock::getPrivateKey();

        $unsignedMessage = MessageFactory::fromXML($authnRequest->documentElement);
        $unsignedMessage->setSigningKey($privateKey);
        $unsignedMessage->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        $signedMessage = MessageFactory::fromXML($unsignedMessage->toXML());

        $this->assertEquals($privateKey->getAlgorithm(), $signedMessage->getSignature()->getAlgorithm());
    }


    /**
     * @group Message
     * @return void
     */
    public function testIssuerParsedAsNameID(): void
    {
        $authnRequest = new DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer NameQualifier="https://gateway.stepup.org/saml20/sp/metadata"
    SPNameQualifier="https://spnamequalifier.com"
    SPProvidedID="ProviderID"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">
        https://gateway.stepup.org/saml20/sp/metadata
  </saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $message = MessageFactory::fromXML($authnRequest->documentElement);
        $issuer = $message->getIssuer();
        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('https://gateway.stepup.org/saml20/sp/metadata', $issuer->getNameQualifier());
        $this->assertEquals('https://spnamequalifier.com', $issuer->getSPNameQualifier());
        $this->assertEquals('ProviderID', $issuer->getSPProvidedID());
        $this->assertEquals('urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified', $issuer->getFormat());
        $this->assertEquals('https://gateway.stepup.org/saml20/sp/metadata', $issuer->getValue());
    }


    /**
     * @group Message
     * @return void
     */
    public function testConvertIssuerToXML(): void
    {
        $status = new Status(new StatusCode());

        // first, try with common Issuer objects (Format=entity)
        $issuer = new Issuer('https://gateway.stepup.org/saml20/sp/metadata');

        $response = new Response($status, $issuer);
        $xml = $response->toXML();
        $xml_issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        $xml_issuer = $xml_issuer[0];

        $this->assertFalse($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->getValue(), $xml_issuer->textContent);

        // now, try an Issuer with another format and attributes
        $issuer = new Issuer(
            'https://gateway.stepup.org/saml20/sp/metadata',
            Constants::NAMEID_UNSPECIFIED,
            'SomeSPProvidedID',
            'SomeNameQualifier',
            'SomeSPNameQualifier'
        );
        $response = new Response($status, $issuer);
        $xml = $response->toXML();
        $xml_issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        $xml_issuer = $xml_issuer[0];

        $this->assertTrue($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->getValue(), $xml_issuer->textContent);
        $this->assertEquals($issuer->getNameQualifier(), $xml_issuer->getAttribute('NameQualifier'));
        $this->assertEquals($issuer->getSPNameQualifier(), $xml_issuer->getAttribute('SPNameQualifier'));
        $this->assertEquals($issuer->getSPProvidedID(), $xml_issuer->getAttribute('SPProvidedID'));

        // finally, make sure we can skip the Issuer by setting it to null
        $response = new Response($status);
        $xml = $response->toXML();

        $this->assertEmpty(Utils::xpQuery($xml, './saml_assertion:Issuer'));
    }


    /**
     * @group Message
     * @return void
     */
    public function testCorrectSignatureMethodCanBeExtractedFromResponse(): void
    {
        $response = new DOMDocument();
        $response->load(__DIR__ . '../../../Response/response.xml');

        $privateKey = CertificatesMock::getPrivateKey();

        $unsignedMessage = MessageFactory::fromXML($response->documentElement);
        $unsignedMessage->setSigningKey($privateKey);
        $unsignedMessage->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        $signedMessage = MessageFactory::fromXML($unsignedMessage->toXML());

        $this->assertEquals($privateKey->getAlgorithm(), $signedMessage->getSignature()->getAlgorithm());
    }


    /**
     * @group Message
     * @covers \SAML2\XML\samlp\AbstractMessage::getExtensions()
     * @return void
     */
    public function testGetExtensions(): void
    {
        $authnRequest = new DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer NameQualifier="https://gateway.stepup.org/saml20/sp/metadata"
    SPNameQualifier="https://spnamequalifier.com"
    SPProvidedID="ProviderID"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">
        https://gateway.stepup.org/saml20/sp/metadata
  </saml:Issuer>
  <samlp:Extensions>
    <myextElt att="value3">example1</myextElt>
    <myextElt att="value5" />
  </samlp:Extensions>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $message = MessageFactory::fromXML($authnRequest->documentElement);
        $exts = $message->getExtensions();
        $this->assertInstanceOf(Extensions::class, $exts);
        $exts = $exts->getList();
        $this->assertCount(2, $exts);
        $this->assertEquals("myextElt", $exts[0]->getLocalName());
        $this->assertEquals("example1", $exts[0]->getXML()->textContent);
        $this->assertEquals("myextElt", $exts[1]->getLocalName());
    }


    /**
     * @group Message
     * @covers \SAML2\XML\samlp\AbstractMessage::setExtensions()
     * @return void
     */
    public function testSetExtensions(): void
    {
        $this->markTestSkipped();
        $authnRequest = new \DOMDocument();
        $authnRequest->loadXML(<<<'AUTHNREQUEST'
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    AssertionConsumerServiceIndex="1"
    Destination="https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO"
    ID="_2b0226190ca1c22de6f66e85f5c95158"
    IssueInstant="2014-09-22T13:42:00Z"
    Version="2.0">
  <saml:Issuer NameQualifier="https://gateway.stepup.org/saml20/sp/metadata"
    SPNameQualifier="https://spnamequalifier.com"
    SPProvidedID="ProviderID"
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">
        https://gateway.stepup.org/saml20/sp/metadata
  </saml:Issuer>
  <saml:Subject>
        <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">user@example.org</saml:NameID>
  </saml:Subject>
</samlp:AuthnRequest>
AUTHNREQUEST
        );

        $message = MessageFactory::fromXML($authnRequest->documentElement);
        $exts = $message->getExtensions();
        $this->assertCount(0, $exts);

        $dom = \SAML2\DOMDocumentFactory::create();
        $ce = $dom->createElementNS('http://www.example.com/XFoo', 'xfoo:test', 'Test data!');
        $newexts[] = new \SAML2\XML\Chunk($ce);

        $message->setExtensions($newexts);

        $exts = $message->getExtensions();
        $this->assertCount(1, $exts);
        $this->assertEquals("test", $exts[0]->getLocalName());
        $this->assertEquals("Test data!", $exts[0]->getXML()->textContent);

        $xml = $message->toXML();
        $xml_exts = Utils::xpQuery($xml, './samlp:Extensions');
        $this->assertCount(1, $xml_exts);
        $this->assertEquals("test", $xml_exts[0]->childNodes->item(0)->localName);
        $this->assertEquals("Test data!", $xml_exts[0]->childNodes->item(0)->textContent);
    }


    /**
     * @group Message
     * @return void
     */
    public function testNamespaceMustBeProtocol(): void
    {
        $xml = <<<XML
<saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_593e33ddf86449ce4d4c22b60ac48e067d98a0b2bf"
                Version="2.0"
                IssueInstant="2010-03-05T13:34:28Z"
>
  <saml:Issuer>testIssuer</saml:Issuer>
</saml:Assertion>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown namespace of SAML message: 'urn:oasis:names:tc:SAML:2.0:assertion'");
        $message = MessageFactory::fromXML($document->documentElement);
    }


    /**
     * @group Message
     * @return void
     */
    public function testSAMLversionMustBe20(): void
    {
        $xml = <<<XML
<samlp:LogoutResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.1"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.example.org</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder" />
        <samlp:StatusMessage>Something is wrong...</samlp:StatusMessage>
    </samlp:Status>
</samlp:LogoutResponse>
XML;

        $document  = DOMDocumentFactory::fromString($xml);
        $this->expectException(InvalidArgumentException::class);
        $message = MessageFactory::fromXML($document->documentElement);
    }


    /**
     * @group Message
     * @return void
     */
    public function testMessageMustHaveID(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                Version="2.0" IssueInstant="2010-07-22T11:30:19Z"
                >
  <saml:Issuer>TheIssuer</saml:Issuer>
</samlp:LogoutRequest>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing \'ID\' attribute from samlp:LogoutRequest.');
        $message = MessageFactory::fromXML($document->documentElement);
    }


    /**
     * Tests AQ message type and some getters/setters.
     * @group Message
     * @return void
     */
    public function testParseAttributeQuery(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
        xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
        xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
        ID="aaf23196-1773-2113-474a-fe114412ab72"
        Version="2.0"
        Consent="urn:oasis:names:tc:SAML:2.0:consent:prior"
        IssueInstant="2017-09-06T11:49:27Z">
        <saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
        <saml:Subject>
          <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
        </saml:Subject>
        <saml:Attribute
          NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
          Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
          FriendlyName="entitlements">
        </saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $message = MessageFactory::fromXML($document->documentElement);

        $this->assertEquals('https://example.org/', $message->getIssuer()->getValue());
        $this->assertEquals('aaf23196-1773-2113-474a-fe114412ab72', $message->getId());

        $document->documentElement->setAttribute('ID', 'somethingNEW');
        $message = MessageFactory::fromXML($document->documentElement);

        $this->assertEquals('https://example.org/', $message->getIssuer()->getValue());
        $this->assertEquals('somethingNEW', $message->getId());
        $this->assertEquals(Constants::CONSENT_PRIOR, $message->getConsent());

        $messageElement = $message->toXML();
        $xp = Utils::xpQuery($messageElement, '.');
        $this->assertEquals('somethingNEW', $xp[0]->getAttribute('ID'));
    }


    /**
     * @group Message
     * @return void
     */
    public function testMessageTypeMustBeKnown(): void
    {
        $xml = <<<XML
<samlp:MyFantasy
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    ID="something"
    Version="2.0"
    IssueInstant="2018-11-16T18:28:49Z">
        <saml:Issuer>something</saml:Issuer>
</samlp:MyFantasy>
XML;
        $document  = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown SAML message: 'MyFantasy'");
        $message = MessageFactory::fromXML($document->documentElement);
    }
}
