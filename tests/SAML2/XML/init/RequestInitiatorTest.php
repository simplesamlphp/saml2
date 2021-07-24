<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\init;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\init\RequestInitiator;
use SimpleSAML\XML\DOMDocumentFactory;

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
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = RequestInitiator::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/init_RequestInitiator.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a RequestInitiator from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr');
        $attr->value = 'value';

        $requestInitiator = new RequestInitiator('https://whatever/', 'https://foo.bar/', [$attr]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($requestInitiator)
        );
    }


    // test unmarshalling


    /**
     * Test creating a RequestInitiator from XML.
     */
    public function testUnmarshalling(): void
    {
        $requestInitiator = RequestInitiator::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals($requestInitiator->getBinding(), RequestInitiator::NS);
        $this->assertEquals($requestInitiator->getLocation(), 'https://whatever/');
        $this->assertEquals($requestInitiator->getResponseLocation(), 'https://foo.bar/');

        $this->assertTrue($requestInitiator->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $requestInitiator->getAttributeNS('urn:test', 'attr'));
        $this->assertFalse($requestInitiator->hasAttributeNS('urn:test', 'invalid'));
        $this->assertNull($requestInitiator->getAttributeNS('urn:test', 'invalid'));

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($requestInitiator)
        );
    }


    /**
     * Test that creating a RequestInitiator from XML with an invalid Binding fails.
     */
    public function testUnmarshallingWithInvalidBinding(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Binding', 'urn:something');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'."
        );

        RequestInitiator::fromXML($this->xmlRepresentation->documentElement);
    }
}
