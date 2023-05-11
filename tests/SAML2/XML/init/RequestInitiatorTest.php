<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\init;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\init\RequestInitiator;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\init\RequestInitiatorTest
 *
 * @covers \SimpleSAML\SAML2\XML\init\RequestInitiator
 *
 * @package simplesamlphp/saml2
 */
final class RequestInitiatorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/sstc-request-initiation.xsd';

        $this->testedClass = RequestInitiator::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/init_RequestInitiator.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a RequestInitiator from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = $this->xmlRepresentation->createAttributeNS('urn:x-simplesamlphp:namespace', 'test:attr');
        $attr->value = 'value';

        $requestInitiator = new RequestInitiator(
            'https://simplesamlphp.org/some/endpoint',
            'https://simplesamlphp.org/other/endpoint',
            [$attr],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($requestInitiator),
        );
    }


    // test unmarshalling


    /**
     * Test creating a RequestInitiator from XML.
     */
    public function testUnmarshalling(): void
    {
        $requestInitiator = RequestInitiator::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($requestInitiator),
        );
    }


    /**
     * Test that creating a RequestInitiator from XML with an invalid Binding fails.
     */
    public function testUnmarshallingWithInvalidBinding(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Binding', C::BINDING_HTTP_POST);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'.",
        );

        RequestInitiator::fromXML($this->xmlRepresentation->documentElement);
    }
}
