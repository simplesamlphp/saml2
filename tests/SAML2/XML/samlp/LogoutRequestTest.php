<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMDocument;
use DOMElement;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

/**
 * Class \SAML2\XML\samlp\LogoutRequestTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\LogoutRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class LogoutRequestTest extends MockeryTestCase
{
    /** @var \DOMDocument $document */
    private DOMDocument $document;

    /** @var \DOMElement */
    private DOMElement $logoutRequestElement;

    /** @var \DOMDocument $retrievalMethod */
    private DOMDocument $retrievalMethod;


    /**
     * Load a fixture.
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_LogoutRequest.xml'
        );

        $this->retrievalMethod = DOMDocumentFactory::fromString(
            '<ds:RetrievalMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" URI="#Encrypted_KEY_ID" ' .
            'Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey"/>'
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID('NameIDValue');

        $logoutRequest = new LogoutRequest($nameId, null, null, ['SessionIndexValue']);

        $logoutRequestElement = $logoutRequest->toXML();
        $this->assertEquals('LogoutRequest', $logoutRequestElement->localName);
        $this->assertEquals(Constants::NS_SAMLP, $logoutRequestElement->namespaceURI);

        $nameIdElements = XMLUtils::xpQuery($logoutRequestElement, './saml_assertion:NameID');
        $this->assertCount(1, $nameIdElements);
        $nameIdElements = $nameIdElements[0];
        $this->assertEquals('NameIDValue', $nameIdElements->textContent);

        $sessionIndexElements = XMLUtils::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex');
        $this->assertCount(1, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue', $sessionIndexElements[0]->textContent);

        $nameId = new NameID('NameIDValue');
        $logoutRequest = new LogoutRequest(
            $nameId,
            null,
            null,
            ['SessionIndexValue1', 'SessionIndexValue2']
        );
        $logoutRequestElement = $logoutRequest->toXML();

        $sessionIndexElements = XMLUtils::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex');
        $this->assertCount(2, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue1', $sessionIndexElements[0]->textContent);
        $this->assertEquals('SessionIndexValue2', $sessionIndexElements[1]->textContent);
    }


    /**
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $nameId = new NameID('NameIDValue');

        $logoutRequest = new LogoutRequest($nameId, null, null, ['SessionIndexValue']);

        $logoutRequestElement = $logoutRequest->toXML();

        // Test for a NameID
        $logoutRequestElements = XMLUtils::xpQuery($logoutRequestElement, './saml_assertion:NameID');
        $this->assertCount(1, $logoutRequestElements);

        // Test ordering of LogoutRequest contents
        $logoutRequestElements = XMLUtils::xpQuery($logoutRequestElement, './saml_assertion:NameID/following-sibling::*');

        $this->assertCount(1, $logoutRequestElements);
        $this->assertEquals('samlp:SessionIndex', $logoutRequestElements[0]->tagName);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $logoutRequest = LogoutRequest::fromXML($this->document->documentElement);
        $issuer = $logoutRequest->getIssuer();

        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('TheIssuer', $issuer->getValue());

        $encid = $logoutRequest->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $encid);

        $this->assertEquals(['SomeSessionIndex1', 'SomeSessionIndex2'], $logoutRequest->getSessionIndexes());

        $identifier = $encid->decrypt(
            PEMCertificatesMock::getPrivateKey(
                XMLSecurityKey::RSA_OAEP_MGF1P,
                PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY
            )
        );
        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('very secret', $identifier->getValue());
    }


    /**
     * @return void
     */
    public function testEncryptedNameId(): void
    {
        $ed = new EncryptedData(
            new CipherData('Nk4W4mx...'),
            'Encrypted_DATA_ID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            "key-type",
            'base64-encoded',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo([new Chunk($this->retrievalMethod->documentElement)])
        );

        $ek = new EncryptedKey(
            new CipherData('PzA5X...'),
            'Encrypted_KEY_ID',
            null,
            null,
            null,
            'some_ENTITY_ID',
            'Name of the key',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
            null,
            new ReferenceList(
                [new DataReference('#Encrypted_DATA_ID')]
            )
        );
        $eid = new EncryptedID($ed, [$ek]);

        $logoutRequest = new LogoutRequest($eid);
        $logoutRequestElement = $logoutRequest->toXML();
        $this->assertCount(
            1,
            XMLUtils::xpQuery($logoutRequestElement, './saml_assertion:EncryptedID/xenc:EncryptedData')
        );
    }


    /**
     * @return void
     */
    public function testPlainNameIDUnmarshalling(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $identifier = $logoutRequest->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals("frits", $identifier->getValue());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified', $identifier->getFormat());
    }


    /**
     * @return void
     */
    public function testMissingNameIDThrowsException(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage(
            "Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>."
        );
        LogoutRequest::fromXML($this->logoutRequestElement);
    }


    /**
     * @return void
     */
    public function testMultipleNameIDThrowsException(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">willem</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:NameID> in <samlp:LogoutRequest>.");
        LogoutRequest::fromXML($this->logoutRequestElement);
    }


    /**
     * @return void
     */
    public function testGetNotOnOrAfter(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z"
    NotOnOrAfter="2018-11-28T19:33:12Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $this->assertEquals(1543433592, $logoutRequest->getNotOnOrAfter());
    }


    /**
     * @return void
     */
    public function testSetNotOnOrAfter(): void
    {
        $nameId = new NameID('NameIDValue');
        $time = time();

        $logoutRequest = new LogoutRequest($nameId, $time);
        $logoutRequestElement = $logoutRequest->toXML();

        $logoutRequest2 = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals($time, $logoutRequest2->getNotOnOrAfter());
    }

    /**
     * @return void
     */
    public function testGetReason(): void
    {
        $reason = "urn:simplesamlphp:reason-test";
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z"
    NotOnOrAfter="2018-11-28T19:33:12Z"
    Reason="$reason">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $this->assertEquals($reason, $logoutRequest->getReason());
    }


    /**
     * @return void
     */
    public function testSetReason(): void
    {
        $reason = "urn:simplesamlphp:reason-test";
        $nameId = new NameID('NameIDValue');

        $logoutRequest = new LogoutRequest($nameId, null, $reason);
        $logoutRequestElement = $logoutRequest->toXML();

        $logoutRequest2 = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals($reason, $logoutRequest2->getReason());
    }



    /**
     * @return void
     */
    public function testWithOutSessionIndices(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $this->assertCount(0, $logoutRequest->getSessionIndexes());
    }


    /**
     * @return void
     */
    public function testSetSessionIndicesVariants(): void
    {
        $nameId = new NameID('test');
        $sessionIndexes = [
            'SessionIndexValue1',
            'SessionIndexValue2'
        ];

        $logoutRequest = new LogoutRequest($nameId, null, null, $sessionIndexes);

        $this->assertCount(2, $logoutRequest->getSessionIndexes());
        $this->assertEquals('SessionIndexValue1', $logoutRequest->getSessionIndexes()[0]);
        $this->assertEquals('SessionIndexValue2', $logoutRequest->getSessionIndexes()[1]);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(LogoutRequest::fromXML($this->document->documentElement))))
        );
    }
}
