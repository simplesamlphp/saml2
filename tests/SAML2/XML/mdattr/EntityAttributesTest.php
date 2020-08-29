<?php

declare(strict_types=1);

namespace SAML2\XML\mdattr;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\Chunk;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdattr\EntityAttributes
 * @package simplesamlphp/saml2
 */
final class EntityAttributesTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $attribute1 = new Attribute(
            'urn:simplesamlphp:v1:simplesamlphp',
            Constants::NAMEFORMAT_URI,
            null,
            [
                new AttributeValue('FirstValue'),
                new AttributeValue('SecondValue'),
            ]
        );

        $attribute2 = new Attribute(
            'foo',
            'urn:simplesamlphp:v1',
            null,
            [
                new AttributeValue('bar')
            ]
        );

        $entityAttributes = new EntityAttributes([$attribute1]);
        $entityAttributes->addChild($attribute2);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $entityAttributes->toXML($document->firstChild);

        $entityAttributesElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'EntityAttributes\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:attribute\']'
        );
        $this->assertCount(1, $entityAttributesElements);
        $entityAttributesElement = $entityAttributesElements[0];

        $attributeElements = Utils::xpQuery(
            $entityAttributesElement,
            './*[local-name()=\'Attribute\' and namespace-uri()=\'urn:oasis:names:tc:SAML:2.0:assertion\']'
        );
        $this->assertCount(2, $attributeElements);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdattr:EntityAttributes xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute">
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" IssueInstant="1984-08-26T10:01:30.000Z" Version="2.0"/>
    <saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="attrib1"/>
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" IssueInstant="1984-08-26T10:01:30.000Z" Version="2.0"/>
    <saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="attrib2"/>
    <saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="attrib3"/>
</mdattr:EntityAttributes>
XML
        );

        $entityAttributes = EntityAttributes::fromXML($document->firstChild);
        $this->assertCount(5, $entityAttributes->getChildren());

        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[0]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[1]);
        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[2]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[3]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[4]);

        $this->assertEquals('Assertion', $entityAttributes->getChildren()[0]->getLocalName());
        $this->assertEquals('1984-08-26T10:01:30.000Z', $entityAttributes->getChildren()[0]->getXML()->getAttribute('IssueInstant'));
        $this->assertEquals('attrib2', $entityAttributes->getChildren()[3]->getName());
    }


    /**
     * @return void
     */
    public function testUnmarshallingAttributes(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdattr:EntityAttributes xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute">
  <saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="urn:simplesamlphp:v1:simplesamlphp" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri">
    <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">is</saml:AttributeValue>
    <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">really</saml:AttributeValue>
    <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">cool</saml:AttributeValue>
  </saml:Attribute>
  <saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="foo" NameFormat="urn:simplesamlphp:v1">
    <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">bar</saml:AttributeValue>
  </saml:Attribute>
</mdattr:EntityAttributes>
XML
        );

        $entityAttributes = EntityAttributes::fromXML($document->firstChild);
        $this->assertCount(2, $entityAttributes->getChildren());

        $this->assertEquals('urn:simplesamlphp:v1:simplesamlphp', $entityAttributes->getChildren()[0]->getName());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:uri', $entityAttributes->getChildren()[0]->getNameFormat());
        $this->assertCount(3, $entityAttributes->getChildren()[0]->getAttributeValues());
        $this->assertEquals('foo', $entityAttributes->getChildren()[1]->getName());
        $this->assertEquals('urn:simplesamlphp:v1', $entityAttributes->getChildren()[1]->getNameFormat());
        $this->assertCount(1, $entityAttributes->getChildren()[1]->getAttributeValues());
    }
}
