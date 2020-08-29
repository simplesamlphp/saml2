<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingAttributeException;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\XML\saml\AttributeTest
 *
 * @covers \SAML2\XML\saml\Attribute
 * @package simplesamlphp/saml2
 */
final class AttributeTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}"
    Name="TheName"
    NameFormat="TheNameFormat"
    FriendlyName="TheFriendlyName"
    test:attr1="testval1"
    test:attr2="testval2"
    xmlns:test="urn:test">
  <saml:AttributeValue>FirstValue</saml:AttributeValue>
  <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );
    }


    // marshalling


    /**
     * Test creating an Attribute from scratch.
     */
    public function testMarshalling(): void
    {
        $attr1 = $this->document->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->document->createAttributeNS('urn:test', 'test:attr2');
        $attr2->value = 'testval2';

        $attribute = new Attribute(
            'TheName',
            'TheNameFormat',
            'TheFriendlyName',
            [
                new AttributeValue('FirstValue'),
                new AttributeValue('SecondValue')
            ],
            [$attr1, $attr2]
        );

        $this->assertEquals('TheName', $attribute->getName());
        $this->assertEquals('TheNameFormat', $attribute->getNameFormat());
        $this->assertEquals('TheFriendlyName', $attribute->getFriendlyName());

        $this->assertEquals('testval1', $attribute->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $attribute->getAttributeNS('urn:test', 'attr2'));

        $values = $attribute->getAttributeValues();
        $this->assertCount(2, $values);
        $this->assertEquals('FirstValue', $values[0]->getValue());
        $this->assertEquals('SecondValue', $values[1]->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($attribute)
        );
    }


    // unmarshalling


    /**
     * Test creating of an Attribute from XML.
     */
    public function testUnmarshalling(): void
    {
        $attribute = Attribute::fromXML($this->document->documentElement);

        $this->assertEquals('TheName', $attribute->getName());
        $this->assertEquals('TheNameFormat', $attribute->getNameFormat());
        $this->assertEquals('TheFriendlyName', $attribute->getFriendlyName());
        $this->assertCount(2, $attribute->getAttributeValues());
        $this->assertEquals('FirstValue', $attribute->getAttributeValues()[0]->getValue());
        $this->assertEquals('SecondValue', $attribute->getAttributeValues()[1]->getValue());

        $this->assertEquals(
            [
                '{urn:test}attr1' => [
                    'qualifiedName' => 'test:attr1',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval1'
                ],
                '{urn:test}attr2' => [
                    'qualifiedName' => 'test:attr2',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval2'
                ]
            ],
            $attribute->getAttributesNS()
        );
        $this->assertEquals('testval1', $attribute->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $attribute->getAttributeNS('urn:test', 'attr2'));
    }


    /**
     * Test that creating an Attribute from XML fails if no Name is provided.
     */
    public function testUnmarshallingWithoutName(): void
    {
        $document = $this->document;
        $document->documentElement->removeAttribute('Name');

        $this->expectException(MissingAttributeException::class);

        Attribute::fromXML($document->documentElement);
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        $attribute = Attribute::fromXML($this->document->documentElement);
        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));
        /** @psalm-var \SAML2\XML\saml\EncryptedAttribute $encattr */
        $encattr = EncryptedAttribute::fromUnencryptedElement($attribute, $pubkey);
        $str = strval($encattr);
        $doc = DOMDocumentFactory::fromString($str);
        $encattr = EncryptedAttribute::fromXML($doc->documentElement);
        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));
        $attr = $encattr->decrypt($privkey);
        $this->assertEquals(strval($attribute), strval($attr));
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Attribute::fromXML($this->document->documentElement))))
        );
    }
}
