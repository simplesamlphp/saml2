<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\Chunk;
use SimpleSAML\SAML2\XML\ds\KeyInfo;

/**
 * Class \SAML2\XML\xenc\EncryptedKeyTest
 *
 * @covers \SAML2\XML\xenc\AbstractEncryptedType
 * @covers \SAML2\XML\xenc\EncryptedKey
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class EncryptedKeyTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;

    /**
     * @return void
     */
    public function setup(): void
    {
        $xencNamespace = Constants::NS_XENC;

        $this->encryptedKey = DOMDocumentFactory::fromString(<<<XML
<xenc:EncryptedKey xmlns:xenc="http://www.w3.org/2001/04/xmlenc#"
     Id="Encrypted_KEY_ID"
     Type="http://www.w3.org/2001/04/xmlenc#Element"
     MimeType="text/plain"
     Encoding="someEncoding"
     Recipient="some_ENTITY_ID">
  <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
  <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <xenc:EncryptedKey>
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
      <xenc:CipherData>
        <xenc:CipherValue>nxf0b...</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedKey>
  </ds:KeyInfo>
  <xenc:CipherData>
    <xenc:CipherValue>PzA5X...</xenc:CipherValue>
  </xenc:CipherData>
  <xenc:ReferenceList>
    <xenc:DataReference URI="#Encrypted_DATA_ID"/>
  </xenc:ReferenceList>
  <xenc:CarriedKeyName>Name of the key</xenc:CarriedKeyName>
</xenc:EncryptedKey>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $encryptedKey = new EncryptedKey(
            new CipherData('PzA5X...'),
            'Encrypted_KEY_ID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'someEncoding',
            'some_ENTITY_ID',
            'Name of the key',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
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
            ),
            new ReferenceList([new DataReference('#Encrypted_DATA_ID')])
        );

        $cipherData = $encryptedKey->getCipherData();
        $this->assertEquals('PzA5X...', $cipherData->getCipherValue());

        $encryptionMethod = $encryptedKey->getEncryptionMethod();
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#rsa-1_5', $encryptionMethod->getAlgorithm());

        $keyInfo = $encryptedKey->getKeyInfo();
        $info = $keyInfo->getInfo();
        $this->assertCount(1, $info);

        $encKey = $info[0];
        $this->assertInstanceOf(EncryptedKey::class, $encKey);

        $referenceList = $encryptedKey->getReferenceList();
        $this->assertEmpty($referenceList->getKeyReferences());
        $dataRefs = $referenceList->getDataReferences();
        $this->assertCount(1, $dataRefs);
        $this->assertEquals('#Encrypted_DATA_ID', $dataRefs[0]->getURI());

        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $encryptedKey->getType());
        $this->assertEquals('someEncoding', $encryptedKey->getEncoding());
        $this->assertEquals('text/plain', $encryptedKey->getMimeType());
        $this->assertEquals('Encrypted_KEY_ID', $encryptedKey->getID());
        $this->assertEquals('some_ENTITY_ID', $encryptedKey->getRecipient());
        $this->assertEquals('Name of the key', $encryptedKey->getCarriedKeyName());

        $this->assertEquals(
            $this->encryptedKey->saveXML($this->encryptedKey->documentElement),
            strval($encryptedKey)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $encryptedKey = new EncryptedKey(
            new CipherData('PzA5X...'),
            'Encrypted_KEY_ID',
            'http://www.w3.org/2001/04/xmlenc#Element',
            'text/plain',
            'someEncoding',
            'some_ENTITY_ID',
            'Name of the key',
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
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
            ),
            new ReferenceList([new DataReference('#Encrypted_DATA_ID')])
        );

        // Marshall it to a \DOMElement
        $encryptedKeyElement = $encryptedKey->toXML();

        // Test for a ReferenceList
        $encryptedKeyElements = Utils::xpQuery($encryptedKeyElement, './xenc:ReferenceList');
        $this->assertCount(1, $encryptedKeyElements);

        // Test ordering of EncryptedKey contents
        $encryptedKeyElements = Utils::xpQuery(
            $encryptedKeyElement,
            './xenc:ReferenceList/following-sibling::*'
        );
        $this->assertCount(1, $encryptedKeyElements);
        $this->assertEquals('xenc:CarriedKeyName', $encryptedKeyElements[0]->tagName);
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $encryptedKey = EncryptedKey::fromXML($this->encryptedKey->documentElement);

        $cipherData = $encryptedKey->getCipherData();
        $this->assertEquals('PzA5X...', $cipherData->getCipherValue());

        $encryptionMethod = $encryptedKey->getEncryptionMethod();
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#rsa-1_5', $encryptionMethod->getAlgorithm());

        $keyInfo = $encryptedKey->getKeyInfo();
        $info = $keyInfo->getInfo();
        $this->assertCount(1, $info);

        $encKey = $info[0];
        $this->assertInstanceOf(EncryptedKey::class, $encKey);

        $referenceList = $encryptedKey->getReferenceList();
        $this->assertEmpty($referenceList->getKeyReferences());
        $dataRefs = $referenceList->getDataReferences();
        $this->assertCount(1, $dataRefs);
        $this->assertEquals('#Encrypted_DATA_ID', $dataRefs[0]->getURI());

        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $encryptedKey->getType());
        $this->assertEquals('someEncoding', $encryptedKey->getEncoding());
        $this->assertEquals('text/plain', $encryptedKey->getMimeType());
        $this->assertEquals('Encrypted_KEY_ID', $encryptedKey->getID());
        $this->assertEquals('some_ENTITY_ID', $encryptedKey->getRecipient());
        $this->assertEquals('Name of the key', $encryptedKey->getCarriedKeyName());

        $this->assertEquals(
            $this->encryptedKey->saveXML($this->encryptedKey->documentElement),
            strval($encryptedKey)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->encryptedKey->saveXML($this->encryptedKey->documentElement),
            strval(unserialize(serialize(EncryptedKey::fromXML($this->encryptedKey->documentElement))))
        );
    }
}
