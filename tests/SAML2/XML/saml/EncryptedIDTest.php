<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\xenc\CipherData;
use SAML2\XML\xenc\DataReference;
use SAML2\XML\xenc\EncryptedData;
use SAML2\XML\xenc\EncryptedKey;
use SAML2\XML\xenc\EncryptionMethod;
use SAML2\XML\xenc\ReferenceList;

/**
 * Class EncryptedIDTest
 *
 * @package simplesamlphp/saml2
 */
class EncryptedIDTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;

    /** @var \DOMDocument $retrievalMethod */
    private $retrievalMethod;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = BaseID::NS;
        $xencNamespace = Constants::NS_XENC;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:EncryptedID xmlns:saml="{$samlNamespace}">
  <xenc:EncryptedData
      xmlns:xenc="{$xencNamespace}"
      Id="Encrypted_DATA_ID"
      Type="http://www.w3.org/2001/04/xmlenc#Element"
      MimeType="key-type"
      Encoding="base64-encoded">
    <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
    <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <ds:RetrievalMethod URI="#Encrypted_KEY_ID" Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey"/>
    </ds:KeyInfo>
    <xenc:CipherData>
      <xenc:CipherValue>Nk4W4mx...</xenc:CipherValue>
    </xenc:CipherData>
  </xenc:EncryptedData>
  <xenc:EncryptedKey xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Id="Encrypted_KEY_ID" Recipient="some_ENTITY_ID">
    <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
    <xenc:CipherData>
      <xenc:CipherValue>PzA5X...</xenc:CipherValue>
    </xenc:CipherData>
    <xenc:ReferenceList>
      <xenc:DataReference URI="#Encrypted_DATA_ID"/>
    </xenc:ReferenceList>
    <xenc:CarriedKeyName>Name of the key</xenc:CarriedKeyName>
  </xenc:EncryptedKey>
</saml:EncryptedID>
XML
        );

        $this->retrievalMethod = DOMDocumentFactory::fromString(
            '<ds:RetrievalMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" URI="#Encrypted_KEY_ID" ' .
            'Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey"/>'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
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

        $ed = $eid->getEncryptedData();
        $this->assertEquals('Encrypted_DATA_ID', $ed->getID());
        $this->assertEquals('Nk4W4mx...', $ed->getCipherData()->getCipherValue());
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $ed->getType());
        $this->assertEquals('key-type', $ed->getMimeType());
        $this->assertEquals('base64-encoded', $ed->getEncoding());
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#aes128-cbc', $ed->getEncryptionMethod()->getAlgorithm());
        $this->assertInstanceOf(KeyInfo::class, $ed->getKeyInfo());

        $eks = $eid->getEncryptedKeys();
        $this->assertCount(1, $eks);
        $ek = $eks[0];
        $this->assertEquals('PzA5X...', $ek->getCipherData()->getCipherValue());
        $this->assertEquals('Encrypted_KEY_ID', $ek->getID());
        $this->assertNull($ek->getType());
        $this->assertNull($ek->getMimeType());
        $this->assertNull($ek->getEncoding());
        $this->assertEquals('some_ENTITY_ID', $ek->getRecipient());
        $this->assertEquals('Name of the key', $ek->getCarriedKeyName());
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#rsa-1_5', $ek->getEncryptionMethod()->getAlgorithm());
        $this->assertNull($ek->getKeyInfo());
        $rl = $ek->getReferenceList();
        $this->assertInstanceOf(ReferenceList::class, $rl);
        $this->assertCount(1, $rl->getDataReferences());
        $this->assertEmpty($rl->getKeyReferences());
        $this->assertEquals('#Encrypted_DATA_ID', $rl->getDataReferences()[0]->getURI());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($eid)
        );
    }



    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(EncryptedID::fromXML($this->document->documentElement))))
        );
    }
}
