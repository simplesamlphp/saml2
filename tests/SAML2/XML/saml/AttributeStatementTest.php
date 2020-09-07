<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\XML\saml\AttributeStatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AttributeStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AttributeStatementTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AttributeStatement.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshallingAttributes(): void
    {
        $attrStatement = new AttributeStatement(
            [
                new Attribute('urn:ServiceID', null, null, [new AttributeValue('1')])
            ]
        );

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(1, $attributes);

        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());

        $this->assertEmpty($attrStatement->getEncryptedAttributes());
        $this->assertFalse($attrStatement->hasEncryptedAttributes());
    }


    /**
     * @return void
     */
    public function testMarshallingMissingAttributesThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);

        new AttributeStatement([], []);
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $attrStatement = AttributeStatement::fromXML($this->document->documentElement);

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());
        $this->assertEquals('urn:EntityConcernedID', $attributes[1]->getName());
        $this->assertEquals('urn:EntityConcernedSubID', $attributes[2]->getName());

        $this->assertEmpty($attrStatement->getEncryptedAttributes());
        $this->assertFalse($attrStatement->hasEncryptedAttributes());
    }


    /**
     * @return void
     */
    public function testUnmarshallingMissingAttributesThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:AttributeStatement xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
</saml:AttributeStatement>
XML
        );

        $this->expectException(AssertionFailedException::class);
        AttributeStatement::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AttributeStatement::fromXML($this->document->documentElement))))
        );
    }
}
