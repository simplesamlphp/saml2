<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use SAML2\CertificatesMock;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\MissingElementException;
use SAML2\Exception\TooManyElementsException;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\saml\EncryptedID;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\xenc\CipherData;
use SAML2\XML\xenc\DataReference;
use SAML2\XML\xenc\EncryptedData;
use SAML2\XML\xenc\EncryptedKey;
use SAML2\XML\xenc\EncryptionMethod;
use SAML2\XML\xenc\ReferenceList;

/**
 * Class \SAML2\XML\samlp\LogoutRequestTest
 */
class LogoutRequestTest extends MockeryTestCase
{
    /** @var \DOMDocument $document */
    private $document;

    /** @var \DOMElement */
    private $logoutRequestElement;

    /** @var \DOMDocument $retrievalMethod */
    private $retrievalMethod;


    /**
     * Load a fixture.
     * @return void
     */
    public function setUp(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:EncryptedID>
    <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Type="http://www.w3.org/2001/04/xmlenc#Element">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
      <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <xenc:EncryptedKey>
          <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>
          <xenc:CipherData>
            <xenc:CipherValue>j7t37UjyQ9zgu+zcCDH8v0IaXP2aRSm/XuAW5p5dzeFKf9PZnh7n8977cmex6SCl9SQrJOlqw/GRa342MKFVEl2VmEY9Q+br0ypAZueLwe/z1x3NWzN1ZKwNteWrM7jMdoesjV55PWIWmnuBoDBebuKB7+zS83WN2plV/geSLDg=</xenc:CipherValue>
          </xenc:CipherData>
        </xenc:EncryptedKey>
      </ds:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>rwUZFd0oNzJnvqliCntg8IBx1rulZD4Dopz1LNzx2GbqMln4vxtHi+tzmM9iZ/70zO3n83YXk61JwRzEwvmu7OEZERkjL3cQAEDEws/s4Ibc16pR0irorZy1FYqi9DR1dzDLI2Hbfdrg5oHviyPXtw==</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedData>
  </saml:EncryptedID>
  <samlp:SessionIndex>SomeSessionIndex1</samlp:SessionIndex>
  <samlp:SessionIndex>SomeSessionIndex2</samlp:SessionIndex>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->document = $document;
        $this->logoutRequestElement = $document->documentElement;

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

        $nameIdElements = Utils::xpQuery($logoutRequestElement, './saml_assertion:NameID');
        $this->assertCount(1, $nameIdElements);
        $nameIdElements = $nameIdElements[0];
        $this->assertEquals('NameIDValue', $nameIdElements->textContent);

        $sessionIndexElements = Utils::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex');
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

        $sessionIndexElements = Utils::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex');
        $this->assertCount(2, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue1', $sessionIndexElements[0]->textContent);
        $this->assertEquals('SessionIndexValue2', $sessionIndexElements[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $issuer = $logoutRequest->getIssuer();

        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('TheIssuer', $issuer->getValue());

        $encid = $logoutRequest->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $encid);

        $this->assertEquals(['SomeSessionIndex1', 'SomeSessionIndex2'], $logoutRequest->getSessionIndexes());

        $identifier = $encid->decrypt(CertificatesMock::getPrivateKey());
        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('TheNameIDValue', $identifier->getValue());
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
            Utils::xpQuery($logoutRequestElement, './saml_assertion:EncryptedID/xenc:EncryptedData')
        );
    }


    /**
     * @return void
     */
    public function testPlainNameIDUnmarshalling(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z">
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
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage("Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>.");
        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
    }


    /**
     * @return void
     */
    public function testMultipleNameIDThrowsException(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">willem</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:NameID> in <samlp:LogoutRequest>.");
        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
    }


    /**
     * @return void
     */
    public function testGetNotOnOrAfter(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z" NotOnOrAfter="2018-11-28T19:33:12Z">
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
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z" NotOnOrAfter="2018-11-28T19:33:12Z" Reason="$reason">
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
<samlp:LogoutRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="SomeIDValue" Version="2.0" IssueInstant="2010-07-22T11:30:19Z">
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
