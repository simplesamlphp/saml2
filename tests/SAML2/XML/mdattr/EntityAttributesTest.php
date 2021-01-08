<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdattr;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdattr\EntityAttributes
 * @covers \SimpleSAML\SAML2\XML\mdattr\AbstractMdattrElement
 * @package simplesamlphp/saml2
 */
final class EntityAttributesTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdattr_EntityAttributes.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $attribute1 = new Attribute(
            'attrib1',
            Constants::NAMEFORMAT_URI,
            null,
            []
        );

        $attribute2 = new Attribute(
            'foo',
            'urn:simplesamlphp:v1:simplesamlphp',
            null,
            [
                new AttributeValue('is'),
                new AttributeValue('really'),
                new AttributeValue('cool')
            ]
        );

        $entityAttributes = new EntityAttributes([$attribute1]);
        $entityAttributes->addChild($attribute2);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $entityAttributes->toXML($document->firstChild);

        $entityAttributesElements = XMLUtils::xpQuery(
            $xml,
            '/root/*[local-name()=\'EntityAttributes\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:attribute\']'
        );
        $this->assertCount(1, $entityAttributesElements);
        $entityAttributesElement = $entityAttributesElements[0];

        $attributeElements = XMLUtils::xpQuery(
            $entityAttributesElement,
            './*[local-name()=\'Attribute\' and namespace-uri()=\'urn:oasis:names:tc:SAML:2.0:assertion\']'
        );
        $this->assertCount(2, $attributeElements);
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $entityAttributes = EntityAttributes::fromXML($this->document->firstChild);
        $this->assertCount(4, $entityAttributes->getChildren());

        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[0]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[1]);
        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[2]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[3]);

        $this->assertEquals('Assertion', $entityAttributes->getChildren()[0]->getLocalName());
        $this->assertEquals('1984-08-26T10:01:30.000Z', $entityAttributes->getChildren()[0]->getXML()->getAttribute('IssueInstant'));
        $this->assertEquals('attrib1', $entityAttributes->getChildren()[1]->getName());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:uri', $entityAttributes->getChildren()[1]->getNameFormat());
        $this->assertCount(0, $entityAttributes->getChildren()[1]->getAttributeValues());
        $this->assertEquals('Assertion', $entityAttributes->getChildren()[2]->getLocalName());
        $this->assertEquals('1984-08-26T10:01:30.000Z', $entityAttributes->getChildren()[2]->getXML()->getAttribute('IssueInstant'));
        $this->assertEquals('urn:simplesamlphp:v1:simplesamlphp', $entityAttributes->getChildren()[3]->getName());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:uri', $entityAttributes->getChildren()[3]->getNameFormat());
        $this->assertCount(3, $entityAttributes->getChildren()[3]->getAttributeValues());
    }


    /**
     * Test serialization and unserialization of EntityAttributes elements.
     */
    public function testSerialization(): void
    {
        $ea = EntityAttributes::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($ea)))
        );
    }
}
