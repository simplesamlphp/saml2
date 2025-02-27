<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AttributeStatementTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AttributeStatement::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AttributeStatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AttributeStatement::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AttributeStatement.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $attrStatement = new AttributeStatement(
            [
                new Attribute(
                    name: SAMLStringValue::fromString('urn:ServiceID'),
                    attributeValue: [
                        new AttributeValue(1),
                    ],
                ),
                new Attribute(
                    name: SAMLStringValue::fromString('urn:EntityConcernedID'),
                    attributeValue: [
                        new AttributeValue(1),
                    ],
                ),
                new Attribute(
                    name: SAMLStringValue::fromString('urn:EntityConcernedSubID'),
                    attributeValue: [
                        new AttributeValue(1),
                    ],
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
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
    public function testUnmarshallingMissingAttributesThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AttributeStatement xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
</saml:AttributeStatement>
XML
            ,
        );

        $this->expectException(AssertionFailedException::class);
        AttributeStatement::fromXML($document->documentElement);
    }
}
