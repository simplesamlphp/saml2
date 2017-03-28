<?php

namespace SAML2\XML\mdattr;

use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 */
class EntityAttributesTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $attribute1 = new Attribute();
        $attribute1->Name = 'urn:simplesamlphp:v1:simplesamlphp';
        $attribute1->NameFormat = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';
        $attribute1->AttributeValue = array(
             new AttributeValue('FirstValue'),
             new AttributeValue('SecondValue'),
        );
        $attribute2 = new Attribute();
        $attribute2->Name = 'foo';
        $attribute2->NameFormat = 'urn:simplesamlphp:v1';
        $attribute2->AttributeValue = array(
             new AttributeValue('bar'),
        );

        $entityAttributes = new EntityAttributes();
        $entityAttributes->children[] = $attribute1;
        $entityAttributes->children[] = $attribute2;

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

    public function testUnmarshalling()
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
        $this->assertCount(5, $entityAttributes->children);

        $this->assertInstanceOf('SAML2\XML\Chunk', $entityAttributes->children[0]);
        $this->assertInstanceOf('SAML2\XML\saml\Attribute', $entityAttributes->children[1]);
        $this->assertInstanceOf('SAML2\XML\Chunk', $entityAttributes->children[2]);
        $this->assertInstanceOf('SAML2\XML\saml\Attribute', $entityAttributes->children[3]);
        $this->assertInstanceOf('SAML2\XML\saml\Attribute', $entityAttributes->children[4]);

        $this->assertEquals('Assertion', $entityAttributes->children[0]->localName);
        $this->assertEquals('1984-08-26T10:01:30.000Z', $entityAttributes->children[0]->xml->getAttribute('IssueInstant'));
        $this->assertEquals('attrib2', $entityAttributes->children[3]->Name);
    }

    public function testUnmarshallingAttributes()
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
        $this->assertCount(2, $entityAttributes->children);

        $this->assertEquals('urn:simplesamlphp:v1:simplesamlphp', $entityAttributes->children[0]->Name);
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:uri', $entityAttributes->children[0]->NameFormat);
        $this->assertCount(3, $entityAttributes->children[0]->AttributeValue);
        $this->assertEquals('foo', $entityAttributes->children[1]->Name);
        $this->assertEquals('urn:simplesamlphp:v1', $entityAttributes->children[1]->NameFormat);
        $this->assertCount(1, $entityAttributes->children[1]->AttributeValue);
    }
}
