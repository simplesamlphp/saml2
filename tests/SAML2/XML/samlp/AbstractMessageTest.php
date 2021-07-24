<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use DOMElement;
use Exception;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\Extensions;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AbstractMessageTest extends MockeryTestCase
{
    /**
     * @group Message
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

        $privateKey = PEMCertificatesMock::getPrivateKey(
            XMLSecurityKey::RSA_SHA256,
            PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY
        );

        $unsignedMessage = MessageFactory::fromXML($authnRequest->documentElement);
        $this->assertEquals('2.0', $unsignedMessage->getVersion());
        $unsignedMessage->setSigningKey($privateKey);
        $unsignedMessage->setCertificates(
            [PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)]
        );

        $signedMessage = MessageFactory::fromXML($unsignedMessage->toXML());
        $signature = $signedMessage->getSignature();

        $this->assertInstanceOf(Signature::class, $signature);
        $this->assertEquals($privateKey->getAlgorithm(), $signature->getAlgorithm());
    }


    /**
     * @group Message
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
    Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">https://gateway.stepup.org/saml20/sp/metadata</saml:Issuer>
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
        $this->assertEquals('https://gateway.stepup.org/saml20/sp/metadata', $issuer->getContent());
    }


    /**
     * @group Message
     */
    public function testConvertIssuerToXML(): void
    {
        $status = new Status(new StatusCode());

        // first, try with common Issuer objects (Format=entity)
        $issuer = new Issuer('https://gateway.stepup.org/saml20/sp/metadata');

        $response = new Response($status, $issuer);
        $xml = $response->toXML();
        $xml_issuer = XMLUtils::xpQuery($xml, './saml_assertion:Issuer');
        $xml_issuer = $xml_issuer[0];

        $this->assertFalse($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->getContent(), $xml_issuer->textContent);

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
        $xml_issuer = XMLUtils::xpQuery($xml, './saml_assertion:Issuer');
        $xml_issuer = $xml_issuer[0];
        $this->assertInstanceOf(DOMElement::class, $xml_issuer);

        $this->assertTrue($xml_issuer->hasAttributes());
        $this->assertEquals($issuer->getContent(), $xml_issuer->textContent);
        $this->assertEquals($issuer->getNameQualifier(), $xml_issuer->getAttribute('NameQualifier'));
        $this->assertEquals($issuer->getSPNameQualifier(), $xml_issuer->getAttribute('SPNameQualifier'));
        $this->assertEquals($issuer->getSPProvidedID(), $xml_issuer->getAttribute('SPProvidedID'));

        // finally, make sure we can skip the Issuer by setting it to null
        $response = new Response($status);
        $xml = $response->toXML();

        $this->assertEmpty(XMLUtils::xpQuery($xml, './saml_assertion:Issuer'));
    }


    /**
     * @group Message
     */
    public function testCorrectSignatureMethodCanBeExtractedFromResponse(): void
    {
        $response = new DOMDocument();
        $response->load(__DIR__ . '../../../Response/response.xml');

        $privateKey = PEMCertificatesMock::getPrivateKey(
            XMLSecurityKey::RSA_SHA256,
            PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY
        );

        $unsignedMessage = MessageFactory::fromXML($response->documentElement);
        $unsignedMessage->setSigningKey($privateKey);
        $unsignedMessage->setCertificates(
            [PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)]
        );

        $signedMessage = MessageFactory::fromXML($unsignedMessage->toXML());

        $signature = $signedMessage->getSignature();
        $this->assertInstanceOf(Signature::class, $signature);
        $this->assertEquals($privateKey->getAlgorithm(), $signature->getAlgorithm());
    }


    /**
     * @group Message
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
    <myns:myextElt xmlns:myns="urn:mynamespace"  att="value3">example1</myns:myextElt>
    <myns:myextElt xmlns:myns="urn:mynamespace" att="value5" />
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

        $this->assertInstanceOf(Chunk::class, $exts[0]);
        $this->assertInstanceOf(Chunk::class, $exts[1]);
        $this->assertEquals("myextElt", $exts[0]->getLocalName());
        $this->assertEquals("example1", $exts[0]->getXML()->textContent);
        $this->assertEquals("myextElt", $exts[1]->getLocalName());
    }


    /**
     * @group Message
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
        MessageFactory::fromXML($document->documentElement);
    }


    /**
     * @group Message
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
        $this->expectException(AssertionFailedException::class);
        MessageFactory::fromXML($document->documentElement);
    }


    /**
     * @group Message
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
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage(
            'Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>.'
        );
        MessageFactory::fromXML($document->documentElement);
    }


    /**
     * Tests AQ message type and some getters/setters.
     * @group Message
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
        $issuer = $message->getIssuer();

        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('https://example.org/', $issuer->getContent());
        $this->assertEquals('aaf23196-1773-2113-474a-fe114412ab72', $message->getId());

        $document->documentElement->setAttribute('ID', 'somethingNEW');
        $message = MessageFactory::fromXML($document->documentElement);
        $issuer = $message->getIssuer();

        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('https://example.org/', $issuer->getContent());
        $this->assertEquals('somethingNEW', $message->getId());
        $this->assertEquals(Constants::CONSENT_PRIOR, $message->getConsent());

        $messageElement = $message->toXML();
        $xp = XMLUtils::xpQuery($messageElement, '.');

        /** @psalm-var \DOMElement $query */
        $query = $xp[0];
        $this->assertEquals('somethingNEW', $query->getAttribute('ID'));
    }


    /**
     * @group Message
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
        MessageFactory::fromXML($document->documentElement);
    }
}
