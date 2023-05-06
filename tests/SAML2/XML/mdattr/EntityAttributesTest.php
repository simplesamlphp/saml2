<?php

declare(strict_types=1);

namespace SAML2\XML\mdattr;

use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;
use SAML2\XML\mdattr\EntityAttributes;
use SAML2\Utils\XPath;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 */
class EntityAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $attribute1 = new Attribute();
        $attribute1->setName('urn:simplesamlphp:v1:simplesamlphp');
        $attribute1->setNameFormat('urn:oasis:names:tc:SAML:2.0:attrname-format:uri');
        $attribute1->setAttributeValue([
             new AttributeValue('FirstValue'),
             new AttributeValue('SecondValue'),
        ]);
        $attribute2 = new Attribute();
        $attribute2->setName('foo');
        $attribute2->setNameFormat('urn:simplesamlphp:v1');
        $attribute2->setAttributeValue([
             new AttributeValue('bar'),
        ]);

        $entityAttributes = new EntityAttributes();
        $entityAttributes->setChildren([$attribute1, $attribute2]);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $entityAttributes->toXML($document->firstChild);

        $xpCache = XPath::getXPath($xml);
        $entityAttributesElements = XPath::xpQuery(
            $xml,
            '/root/*[local-name()=\'EntityAttributes\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:attribute\']',
            $xpCache,
        );
        $this->assertCount(1, $entityAttributesElements);
        $entityAttributesElement = $entityAttributesElements[0];

        $xpCache = XPath::getXPath($entityAttributesElement);
        $attributeElements = XPath::xpQuery(
            $entityAttributesElement,
            './*[local-name()=\'Attribute\' and namespace-uri()=\'urn:oasis:names:tc:SAML:2.0:assertion\']',
            $xpCache,
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

        $entityAttributes = new EntityAttributes($document->firstChild);
        $this->assertCount(5, $entityAttributes->getChildren());

        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[0]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[1]);
        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[2]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[3]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[4]);

        $this->assertEquals('Assertion', $entityAttributes->getChildren()[0]->getLocalName());
        $this->assertEquals(
            '1984-08-26T10:01:30.000Z',
            $entityAttributes->getChildren()[0]->getXML()->getAttribute('IssueInstant')
        );
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

        $entityAttributes = new EntityAttributes($document->firstChild);
        $this->assertCount(2, $entityAttributes->getChildren());

        $this->assertEquals('urn:simplesamlphp:v1:simplesamlphp', $entityAttributes->getChildren()[0]->getName());
        $this->assertEquals(
            'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
            $entityAttributes->getChildren()[0]->getNameFormat()
        );
        $this->assertCount(3, $entityAttributes->getChildren()[0]->getAttributeValue());
        $this->assertEquals('foo', $entityAttributes->getChildren()[1]->getName());
        $this->assertEquals('urn:simplesamlphp:v1', $entityAttributes->getChildren()[1]->getNameFormat());
        $this->assertCount(1, $entityAttributes->getChildren()[1]->getAttributeValue());
    }
}
