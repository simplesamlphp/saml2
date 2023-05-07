<?php

declare(strict_types=1);

namespace SAML2;

use DOMElement;
use Exception;
use PHPUnit\Framework\TestCase;
use SAML2\Constants as C;
use SAML2\LogoutRequest;
use SAML2\Utils\XPath;
use SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\LogoutRequestTest
 */
class LogoutRequestTest extends TestCase
{
    /**
     * @var \DOMElement
     */
    private DOMElement $logoutRequestElement;


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
    <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:dsig="http://www.w3.org/2000/09/xmldsig#" Type="http://www.w3.org/2001/04/xmlenc#Element">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
      <dsig:KeyInfo xmlns:dsig="http://www.w3.org/2000/09/xmldsig#">
        <xenc:EncryptedKey>
          <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>
          <xenc:CipherData>
            <xenc:CipherValue>j7t37UjyQ9zgu+zcCDH8v0IaXP2aRSm/XuAW5p5dzeFKf9PZnh7n8977cmex6SCl9SQrJOlqw/GRa342MKFVEl2VmEY9Q+br0ypAZueLwe/z1x3NWzN1ZKwNteWrM7jMdoesjV55PWIWmnuBoDBebuKB7+zS83WN2plV/geSLDg=</xenc:CipherValue>
          </xenc:CipherData>
        </xenc:EncryptedKey>
      </dsig:KeyInfo>
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
        $this->logoutRequestElement = $document->firstChild;
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID();
        $nameId->setValue('NameIDValue');

        $logoutRequest = new LogoutRequest();
        $logoutRequest->setNameID($nameId);
        $logoutRequest->setSessionIndex('SessionIndexValue');

        $logoutRequestElement = $logoutRequest->toUnsignedXML();
        $this->assertEquals('LogoutRequest', $logoutRequestElement->localName);
        $this->assertEquals(C::NS_SAMLP, $logoutRequestElement->namespaceURI);

        $xpCache = XPath::getXPath($logoutRequestElement);
        $nameIdElements = XPath::xpQuery($logoutRequestElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $nameIdElements);
        $nameIdElements = $nameIdElements[0];
        $this->assertEquals('NameIDValue', $nameIdElements->textContent);

        $sessionIndexElements = XPath::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex', $xpCache);
        $this->assertCount(1, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue', $sessionIndexElements[0]->textContent);

        $nameId = new NameID();
        $nameId->setValue('NameIDValue');
        $logoutRequest = new LogoutRequest();
        $logoutRequest->setNameID($nameId);
        $logoutRequest->setSessionIndexes(['SessionIndexValue1', 'SessionIndexValue2']);
        $logoutRequestElement = $logoutRequest->toUnsignedXML();

        $xpCache = XPath::getXPath($logoutRequestElement);
        $sessionIndexElements = XPath::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex', $xpCache);
        $this->assertCount(2, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue1', $sessionIndexElements[0]->textContent);
        $this->assertEquals('SessionIndexValue2', $sessionIndexElements[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertEquals('TheIssuer', $logoutRequest->getIssuer()->getValue());
        $this->assertTrue($logoutRequest->isNameIdEncrypted());

        $sessionIndexElements = $logoutRequest->getSessionIndexes();
        $this->assertCount(2, $sessionIndexElements);
        $this->assertEquals('SomeSessionIndex1', $sessionIndexElements[0]);
        $this->assertEquals('SomeSessionIndex2', $sessionIndexElements[1]);
        $this->assertEquals('SomeSessionIndex1', $logoutRequest->getSessionIndex());

        $logoutRequest->decryptNameId(CertificatesMock::getPrivateKey());

        $nameId = $logoutRequest->getNameId();
        $this->assertEquals('TheNameIDValue', $nameId->getValue());
    }


    /**
     * @return void
     */
    public function testEncryptedNameId(): void
    {
        $nameId = new NameID();
        $nameId->setValue('NameIdValue');

        $logoutRequest = new LogoutRequest();
        $logoutRequest->setNameID($nameId);
        $logoutRequest->encryptNameId(CertificatesMock::getPublicKey());

        $logoutRequestElement = $logoutRequest->toUnsignedXML();
        $xpCache = XPath::getXPath($logoutRequestElement);
        $this->assertCount(
            1,
            XPath::xpQuery($logoutRequestElement, './saml_assertion:EncryptedID/xenc:EncryptedData', $xpCache)
        );
    }


    /**
     * @return void
     */
    public function testDecryptingNameId(): void
    {
        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertTrue($logoutRequest->isNameIdEncrypted());

        $logoutRequest->decryptNameId(CertificatesMock::getPrivateKey());
        $nameId = $logoutRequest->getNameId();
        $this->assertEquals('TheNameIDValue', $nameId->getValue());
    }


    /**
     * @return void
     */
    public function testDecryptingNameIdForgotToDecryptThrowsException(): void
    {
        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertTrue($logoutRequest->isNameIdEncrypted());

        $this->expectException(Exception::class, "Attempted to retrieve encrypted NameID without decrypting it first.");
        $nameId = $logoutRequest->getNameId();
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
        $this->logoutRequestElement = $document->firstChild;

        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertEquals("frits", $logoutRequest->getNameId()->getValue());
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified", $logoutRequest->getNameId()->getFormat());

        $this->assertFalse($logoutRequest->isNameIdEncrypted());
        $this->assertNull($logoutRequest->decryptNameId(CertificatesMock::getPrivateKey()));
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
        $this->logoutRequestElement = $document->firstChild;

        $this->expectException(Exception::class, "Missing <saml:NameID> or <saml:EncryptedID> in <samlp:LogoutRequest>.");
        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
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
        $this->logoutRequestElement = $document->firstChild;

        $this->expectException(Exception::class, "More than one <saml:NameID> or <saml:EncryptedD> in <samlp:LogoutRequest>.");
        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
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
        $this->logoutRequestElement = $document->firstChild;

        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertEquals(1543433592, $logoutRequest->getNotOnOrAfter());
    }


    /**
     * @return void
     */
    public function testSetNotOnOrAfter(): void
    {
        $nameId = new NameID();
        $nameId->setValue('NameIDValue');
        $time = time();
        $nameId = new XML\saml\NameID();
        $nameId->setValue('NameIDValue');

        $logoutRequest = new LogoutRequest();
        $logoutRequest->setNameID($nameId);
        $logoutRequest->setNotOnOrAfter($time);
        $logoutRequestElement = $logoutRequest->toUnsignedXML();

        $logoutRequest2 = new LogoutRequest($logoutRequestElement);
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
        $this->logoutRequestElement = $document->firstChild;

        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertEquals($reason, $logoutRequest->getReason());
    }


    /**
     * @return void
     */
    public function testSetReason(): void
    {
        $reason = "urn:simplesamlphp:reason-test";
        $nameId = new XML\saml\NameID();
        $nameId->setValue('NameIDValue');

        $logoutRequest = new LogoutRequest();
        $logoutRequest->setNameID($nameId);
        $logoutRequest->setReason($reason);
        $logoutRequestElement = $logoutRequest->toUnsignedXML();

        $logoutRequest2 = new LogoutRequest($logoutRequestElement);
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
        $this->logoutRequestElement = $document->firstChild;

        $logoutRequest = new LogoutRequest($this->logoutRequestElement);
        $this->assertCount(0, $logoutRequest->getSessionIndexes());
        $this->assertNull($logoutRequest->getSessionIndex());
    }


    /**
     * @return void
     */
    public function testSetSessionIndicesVariants(): void
    {
        $logoutRequest = new LogoutRequest();
        $logoutRequest->setSessionIndexes(['SessionIndexValue1', 'SessionIndexValue2']);
        $this->assertCount(2, $logoutRequest->getSessionIndexes());
        $logoutRequest->setSessionIndex(null);
        $this->assertCount(0, $logoutRequest->getSessionIndexes());
        $logoutRequest->setSessionIndexes(['SessionIndexValue1', 'SessionIndexValue2']);
        $this->assertCount(2, $logoutRequest->getSessionIndexes());
        $logoutRequest->setSessionIndex('SessionIndexValue3');
        $this->assertCount(1, $logoutRequest->getSessionIndexes());
        $this->assertEquals('SessionIndexValue3', $logoutRequest->getSessionIndex());
    }
}
