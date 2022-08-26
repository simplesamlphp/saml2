<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\RequestedAttribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Test for the RequestedAttribute metadata element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\RequestedAttribute
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class RequestedAttributeTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = RequestedAttribute::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_RequestedAttribute.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a RequestedAttribute object from scratch
     */
    public function testMarshalling(): void
    {
        $ra = new RequestedAttribute(
            'attr',
            true,
            C::NAMEFORMAT_URI,
            'Attribute',
            [new AttributeValue('value1')]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ra)
        );
    }


    /**
     * Test that creating a RequestedAttribute object from scratch works if no optional arguments are received.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $ra = new RequestedAttribute('attr');
        $this->assertEquals('attr', $ra->getName());
        $this->assertNull($ra->getIsRequired());
        $this->assertNull($ra->getNameFormat());
        $this->assertNull($ra->getFriendlyName());
        $this->assertEquals([], $ra->getAttributeValues());
    }


    // test unmarshalling


    /**
     * Test creating a RequestedAttribute object from XML
     */
    public function testUnmarshalling(): void
    {
        $ra = RequestedAttribute::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('attr', $ra->getName());
        $this->assertEquals(C::NAMEFORMAT_URI, $ra->getNameFormat());
        $this->assertEquals('Attribute', $ra->getFriendlyName());
        $this->assertEquals('value1', $ra->getAttributeValues()[0]->getValue());
        $this->assertTrue($ra->getIsRequired());
    }


    /**
     * Test that creating a RequestedAttribute object from XML works when isRequired is missing.
     */
    public function testUnmarshallingWithoutIsRequired(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('isRequired');
        $ra = RequestedAttribute::fromXML($this->xmlRepresentation->documentElement);
        $this->assertNull($ra->getIsRequired());
    }


    /**
     * Test that creating a RequestedAttribute object from XML fails when isRequired is not boolean.
     */
    public function testUnmarshallingWithWrongIsRequired(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'isRequired\' attribute of md:RequestedAttribute must be boolean.');
        $this->xmlRepresentation->documentElement->setAttribute('isRequired', 'wrong');
        RequestedAttribute::fromXML($this->xmlRepresentation->documentElement);
    }
}
