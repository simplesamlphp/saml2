<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\Chunk;
use SimpleSAML\SAML2\XML\ds\KeyInfo;

/**
 * Class \SAML2\XML\xenc\EncryptedDataTest
 *
 * @covers \SAML2\XML\xenc\AbstractEncryptedType
 * @covers \SAML2\XML\xenc\EncryptedData
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class EncryptedDataTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;

    /**
     * @return void
     */
    public function setup(): void
    {
        $xencNamespace = Constants::NS_XENC;

        $this->encryptedData = DOMDocumentFactory::fromString(<<<XML
<xenc:EncryptedData xmlns:xenc="{$xencNamespace}"
    Id="MyID"
    Type="http://www.w3.org/2001/04/xmlenc#Element"
    MimeType="text/plain"
    Encoding="SomeEncoding">
  <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
  <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <xenc:EncryptedKey>
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
      <xenc:CipherData>
        <xenc:CipherValue>nxf0b...</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedKey>
  </ds:KeyInfo>
  <xenc:CipherData>
    <xenc:CipherValue>iaDc7...</xenc:CipherValue>
  </xenc:CipherData>
</xenc:EncryptedData>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $encryptedData = new EncryptedData(
            new CipherData('iaDc7...'),
            'MyID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'SomeEncoding',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo(
                [
                    new EncryptedKey(
                        new CipherData('nxf0b...'),
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        new EncryptionMethod('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256')
                    )
                ]
            )
        );

        $cipherData = $encryptedData->getCipherData();
        $this->assertEquals('iaDc7...', $cipherData->getCipherValue());

        $encryptionMethod = $encryptedData->getEncryptionMethod();
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#aes128-cbc', $encryptionMethod->getAlgorithm());

        $keyInfo = $encryptedData->getKeyInfo();
        $info = $keyInfo->getInfo();
        $this->assertCount(1, $info);

        $encKey = $info[0];
        $this->assertInstanceOf(EncryptedKey::class, $encKey);

        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $encryptedData->getType());
        $this->assertEquals('text/plain', $encryptedData->getMimeType());
        $this->assertEquals('MyID', $encryptedData->getID());
        $this->assertEquals('SomeEncoding', $encryptedData->getEncoding());

        $this->assertEquals(
            $this->encryptedData->saveXML($this->encryptedData->documentElement),
            strval($encryptedData)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $encryptedData = EncryptedData::fromXML($this->encryptedData->documentElement);

        $cipherData = $encryptedData->getCipherData();
        $this->assertEquals('iaDc7...', $cipherData->getCipherValue());

        $encryptionMethod = $encryptedData->getEncryptionMethod();
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#aes128-cbc', $encryptionMethod->getAlgorithm());

        $keyInfo = $encryptedData->getKeyInfo();
        $info = $keyInfo->getInfo();
        $this->assertCount(1, $info);

        $encKey = $info[0];
        $this->assertInstanceOf(EncryptedKey::class, $encKey);

        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $encryptedData->getType());
        $this->assertEquals('text/plain', $encryptedData->getMimeType());
        $this->assertEquals('MyID', $encryptedData->getID());
        $this->assertEquals('SomeEncoding', $encryptedData->getEncoding());

        $this->assertEquals(
            $this->encryptedData->saveXML($this->encryptedData->documentElement),
            strval($encryptedData)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->encryptedData->saveXML($this->encryptedData->documentElement),
            strval(unserialize(serialize(EncryptedData::fromXML($this->encryptedData->documentElement))))
        );
    }
}
