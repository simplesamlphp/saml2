<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\EncryptedAttribute;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

/**
 * Class \SAML2\XML\saml\AttributeTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Attribute
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AttributeTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = Attribute::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Attribute.xml'
        );
    }


    // marshalling


    /**
     * Test creating an Attribute from scratch.
     */
    public function testMarshalling(): void
    {
        $attr1 = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr2');
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($attribute)
        );
    }


    // unmarshalling


    /**
     * Test creating of an Attribute from XML.
     */
    public function testUnmarshalling(): void
    {
        $attribute = Attribute::fromXML($this->xmlRepresentation->documentElement);

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
        $document = $this->xmlRepresentation;
        $document->documentElement->removeAttribute('Name');

        $this->expectException(MissingAttributeException::class);

        Attribute::fromXML($document->documentElement);
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        $attribute = Attribute::fromXML($this->xmlRepresentation->documentElement);
        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));
        /** @psalm-var \SimpleSAML\SAML2\XML\saml\EncryptedAttribute $encattr */
        $encattr = EncryptedAttribute::fromUnencryptedElement($attribute, $pubkey);
        $str = strval($encattr);
        $doc = DOMDocumentFactory::fromString($str);
        $encattr = EncryptedAttribute::fromXML($doc->documentElement);
        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));
        $attr = $encattr->decrypt($privkey);
        $this->assertEquals(strval($attribute), strval($attr));
    }
}
