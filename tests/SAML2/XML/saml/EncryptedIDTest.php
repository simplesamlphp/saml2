<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Compat\Ssp\Container;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\CustomBaseID;
use SimpleSAML\SAML2\XML\ds\KeyInfo;
use SimpleSAML\SAML2\XML\xenc\CipherData;
use SimpleSAML\SAML2\XML\xenc\DataReference;
use SimpleSAML\SAML2\XML\xenc\EncryptedData;
use SimpleSAML\SAML2\XML\xenc\EncryptedKey;
use SimpleSAML\SAML2\XML\xenc\EncryptionMethod;
use SimpleSAML\SAML2\XML\xenc\ReferenceList;
use SimpleSAML\TestUtils\PEMCertificatesMock;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class EncryptedIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedID
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
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
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_EncryptedID.xml'
        );

        $this->retrievalMethod = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ds_RetrievalMethod.xml'
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
        $eidElements = XMLUtils::xpQuery($eidElement, './xenc:EncryptedData');
        $this->assertCount(1, $eidElements);

        // Test ordering of EncryptedID contents
        $eidElements = XMLUtils::xpQuery($eidElement, './xenc:EncryptedData/following-sibling::*');
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
