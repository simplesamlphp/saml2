<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\BaseID;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

use function dirname;
use function strval;

/**
 * Class EncryptedIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedID
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class EncryptedIDTest extends TestCase
{
    use SerializableXMLTestTrait;


    /** @var \DOMDocument $retrievalMethod */
    private DOMDocument $retrievalMethod;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = EncryptedID::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_EncryptedID.xml'
        );

        $this->retrievalMethod = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(dirname(__FILE__)))))
            . '/vendor/simplesamlphp/xml-security/tests/resources/xml/ds_RetrievalMethod.xml'
        );
    }


    /**
     */
    public function tearDown(): void
    {
        ContainerSingleton::setContainer(new MockContainer());
    }


    // marshalling


    /**
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

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($eid)
        );
    }


    /**
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
        $xpCache = XPath::getXPath($eidElement);
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData', $xpCache);
        $this->assertCount(1, $eidElements);

        // Test ordering of EncryptedID contents
        /** @psalm-var \DOMElement[] $eidElements */
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData/following-sibling::*', $xpCache);
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
        /** \SimpleSAML\SAML2\XML\saml\AbstractSamlElement $encid */
        $encid = EncryptedID::fromUnencryptedElement($nameid, $pubkey);
        $str = strval($encid);

        $doc = DOMDocumentFactory::fromString($str);

        /** \SimpleSAML\XMLSecurity\XML\EncryptedElementInterface $encid */
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
        $container = $this->createMock(MockContainer::class);
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
}
