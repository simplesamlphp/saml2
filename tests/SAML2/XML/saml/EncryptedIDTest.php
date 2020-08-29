<?php

declare(strict_types=1);

namespace \SimpleSAML\SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Compat\Ssp\Container;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\CustomBaseID;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\Chunk;
use SimpleSAML\SAML2\XML\ds\KeyInfo;
use SimpleSAML\SAML2\XML\xenc\CipherData;
use SimpleSAML\SAML2\XML\xenc\DataReference;
use SimpleSAML\SAML2\XML\xenc\EncryptedData;
use SimpleSAML\SAML2\XML\xenc\EncryptedKey;
use SimpleSAML\SAML2\XML\xenc\EncryptionMethod;
use SimpleSAML\SAML2\XML\xenc\ReferenceList;
use SimpleSAML\Configuration;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class EncryptedIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedID
 * @package simplesamlphp/saml2
 */
final class EncryptedIDTest extends TestCase
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


    /**
     * @return void
     */
    public function tearDown(): void
    {
        ContainerSingleton::setContainer(new MockContainer());
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
        $encMethod = $ed->getEncryptionMethod();
        $this->assertInstanceOf(EncryptionMethod::class, $encMethod);
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#aes128-cbc', $encMethod->getAlgorithm());
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
        $encMethod = $ek->getEncryptionMethod();
        $this->assertInstanceOf(EncryptionMethod::class, $encMethod);
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#rsa-1_5', $encMethod->getAlgorithm());
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
     * @return void
     */
    public function testMarshallingElementOrdering(): void
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

        $eidElement = $eid->toXML();

        // Test for an EncryptedID
        $eidElements = Utils::xpQuery($eidElement, './xenc:EncryptedData');
        $this->assertCount(1, $eidElements);

        // Test ordering of EncryptedID contents
        $eidElements = Utils::xpQuery($eidElement, './xenc:EncryptedData/following-sibling::*');
        $this->assertCount(1, $eidElements);
        $this->assertEquals('xenc:EncryptedKey', $eidElements[0]->tagName);
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        // test with a NameID
        $nameid = new NameID('value', 'name_qualifier');
        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));
        /** \SAML2\XML\saml\AbstractSamlElement $encid */
        $encid = EncryptedID::fromUnencryptedElement($nameid, $pubkey);
        $str = strval($encid);

        $doc = DOMDocumentFactory::fromString($str);

        /** \SAML2\XML\EncryptedElementInterface $encid */
        $encid = EncryptedID::fromXML($doc->documentElement);
        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));
        $id = $encid->decrypt($privkey);
        $this->assertEquals(strval($nameid), strval($id));

        // test with Issuer
        $issuer = new Issuer('entityID');
        $encid = EncryptedID::fromUnencryptedElement($issuer, $pubkey);
        $id = $encid->decrypt($privkey);
        $this->assertInstanceOf(Issuer::class, $id);
        $this->assertEquals(strval($issuer), strval($id));

        // test a custom BaseID without registering it
        $customid = new CustomBaseID(1.0, 'name_qualifier');
        $encid = EncryptedID::fromUnencryptedElement($customid, $pubkey);
        $id = $encid->decrypt($privkey);
        $this->assertInstanceOf(BaseID::class, $id);
        $this->assertEquals(strval($customid), strval($id));

        // test a custom BaseID with a registered handler
        $container = $this->createMock(Container::class);
        $container->method('getIdentifierHandler')->willReturn(CustomBaseID::class);
        ContainerSingleton::setContainer($container);

        $encid = EncryptedID::fromUnencryptedElement($customid, $pubkey);
        $id = $encid->decrypt($privkey);
        $this->assertInstanceOf(CustomBaseID::class, $id);
        $this->assertEquals(strval($customid), strval($id));

        // test with unsupported ID
        $attr = new Attribute('name');
        $encid = EncryptedID::fromUnencryptedElement($attr, $pubkey);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported encrypted identifier.');
        $encid->decrypt($privkey);
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
