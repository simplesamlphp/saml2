<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\AttributeStatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AttributeStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AttributeStatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AttributeStatement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AttributeStatement.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshallingAttributes(): void
    {
        $attrStatement = new AttributeStatement(
            [
                new Attribute(
                    name: 'urn:ServiceID',
                    attributeValue: [new AttributeValue('1')],
                ),
                new Attribute(
                    name: 'urn:EntityConcernedID',
                    attributeValue: [new AttributeValue('1')],
                ),
                new Attribute(
                    name: 'urn:EntityConcernedSubID',
                    attributeValue: [new AttributeValue('1')],
                ),
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($attrStatement),
        );
    }


    /**
     */
    public function testMarshallingMissingAttributesThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);

        new AttributeStatement([], []);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $attrStatement = AttributeStatement::fromXML($this->xmlRepresentation->documentElement);

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());
        $this->assertEquals('urn:EntityConcernedID', $attributes[1]->getName());
        $this->assertEquals('urn:EntityConcernedSubID', $attributes[2]->getName());

        $this->assertEmpty($attrStatement->getEncryptedAttributes());
        $this->assertFalse($attrStatement->hasEncryptedAttributes());
    }


    /**
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
}
