<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdattr;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdattr\EntityAttributes
 * @covers \SimpleSAML\SAML2\XML\mdattr\AbstractMdattrElement
 * @package simplesamlphp/saml2
 */
final class EntityAttributesTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = EntityAttributes::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
        $xml = $entityAttributes->toXML($document->documentElement);

        $xpCache = XPath::getXPath($xml);
        $entityAttributesElements = XPath::xpQuery(
            $xml,
            '/root/*[local-name()=\'EntityAttributes\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:attribute\']',
            $xpCache
        );
        $this->assertCount(1, $entityAttributesElements);
        $entityAttributesElement = $entityAttributesElements[0];

        $xpCache = XPath::getXPath($entityAttributesElement);
        $attributeElements = XPath::xpQuery(
            $entityAttributesElement,
            './*[local-name()=\'Attribute\' and namespace-uri()=\'urn:oasis:names:tc:SAML:2.0:assertion\']',
            $xpCache
        );
        $this->assertCount(2, $attributeElements);
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $entityAttributes = EntityAttributes::fromXML($this->xmlRepresentation->documentElement);
        $this->assertCount(4, $entityAttributes->getChildren());

        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[0]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[1]);
        $this->assertInstanceOf(Chunk::class, $entityAttributes->getChildren()[2]);
        $this->assertInstanceOf(Attribute::class, $entityAttributes->getChildren()[3]);

        $this->assertEquals('Assertion', $entityAttributes->getChildren()[0]->getLocalName());
        $this->assertEquals(
            '1984-08-26T10:01:30.000Z',
            $entityAttributes->getChildren()[0]->getXML()->getAttribute('IssueInstant')
        );
        $this->assertEquals('attrib1', $entityAttributes->getChildren()[1]->getName());
        $this->assertEquals(
            Constants::NAMEFORMAT_URI,
            $entityAttributes->getChildren()[1]->getNameFormat()
        );
        $this->assertCount(0, $entityAttributes->getChildren()[1]->getAttributeValues());
        $this->assertEquals('Assertion', $entityAttributes->getChildren()[2]->getLocalName());
        $this->assertEquals(
            '1984-08-26T10:01:30.000Z',
            $entityAttributes->getChildren()[2]->getXML()->getAttribute('IssueInstant')
        );
        $this->assertEquals('urn:simplesamlphp:v1:simplesamlphp', $entityAttributes->getChildren()[3]->getName());
        $this->assertEquals(
            Constants::NAMEFORMAT_URI,
            $entityAttributes->getChildren()[3]->getNameFormat()
        );
        $this->assertCount(3, $entityAttributes->getChildren()[3]->getAttributeValues());
    }
}
