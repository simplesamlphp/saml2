<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\md\IndexedEndpointTypeTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class IndexedEndpointTypeTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AssertionConsumerService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AssertionConsumerService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an IndexedEndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $idxep = new AssertionConsumerService(42, C::BINDING_HTTP_POST, C::LOCATION_A, false);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($idxep),
        );
    }


    /**
     * Test that creating an IndexedEndpointType from scratch without specifying isDefault works.
     */
    public function testMarshallingWithoutIsDefault(): void
    {
        $idxep = new AssertionConsumerService(42, C::BINDING_HTTP_POST, C::LOCATION_A);
        $this->xmlRepresentation->documentElement->removeAttribute('isDefault');
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($idxep),
        );
        $this->assertNull($idxep->getIsDefault());
    }


    // test unmarshalling


    /**
     * Test creating an IndexedEndpointType from XML.
     */
    public function testUnmarshalling(): void
    {
        $idxep = AssertionConsumerService::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($idxep),
        );
    }


    /**
     * Test that creating an EndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AssertionConsumerService. Expected: ArtifactResolutionService.',
        );
        ArtifactResolutionService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without an index fails.
     */
    public function testUnmarshallingWithoutIndex(): void
    {
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'index\' attribute on md:AssertionConsumerService');
        $this->xmlRepresentation->documentElement->removeAttribute('index');
        AssertionConsumerService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML with a non-numeric index fails.
     */
    public function testUnmarshallingWithWrongIndex(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'index\' attribute of md:AssertionConsumerService must be numerical.');
        $this->xmlRepresentation->documentElement->setAttribute('index', 'value');
        AssertionConsumerService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without isDefault works.
     */
    public function testUnmarshallingWithoutIsDefault(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('isDefault');
        AssertionConsumerService::fromXML($this->xmlRepresentation->documentElement);
        $this->assertTrue(true);
    }


    /**
     * Test that creating an IndexedEndpointType from XML with isDefault of a non-boolean value fails.
     */
    public function testUnmarshallingWithWrongIsDefault(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'isDefault\' attribute of md:AssertionConsumerService must be a boolean.');
        $this->xmlRepresentation->documentElement->setAttribute('isDefault', 'non-bool');
        AssertionConsumerService::fromXML($this->xmlRepresentation->documentElement);
    }
}
