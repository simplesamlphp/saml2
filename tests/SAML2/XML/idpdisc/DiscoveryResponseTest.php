<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\idpdisc;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\idpdisc\DiscoveryResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse
 * @covers \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class DiscoveryEndpointTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-idp-discovery.xsd';

        $this->testedClass = DiscoveryResponse::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/idpdisc_DiscoveryResponse.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a DiscoveryResponse from scratch.
     */
    public function testMarshalling(): void
    {
        $discoResponse = new DiscoveryResponse(43, C::BINDING_HTTP_POST, C::LOCATION_A, false);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoResponse),
        );
    }


    /**
     * Test that creating a DiscoveryResponse from scratch without specifying isDefault works.
     */
    public function testMarshallingWithoutIsDefault(): void
    {
        $discoResponse = new DiscoveryResponse(43, C::BINDING_HTTP_POST, C::LOCATION_A);
        $this->xmlRepresentation->documentElement->removeAttribute('isDefault');
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoResponse),
        );
        $this->assertNull($discoResponse->getIsDefault());
    }


    // test unmarshalling


    /**
     * Test creating a DiscoveryResponse from XML.
     */
    public function testUnmarshalling(): void
    {
        $discoResponse = DiscoveryResponse::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($discoResponse),
        );
    }
}
