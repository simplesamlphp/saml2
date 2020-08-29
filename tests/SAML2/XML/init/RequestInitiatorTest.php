<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\init;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\init\RequestInitiatorTest
 *
 * @covers \SimpleSAML\SAML2\XML\init\RequestInitiator
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class RequestInitiatorTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/init_RequestInitiator.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a RequestInitiator from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = $this->document->createAttributeNS('urn:test', 'test:attr');
        $attr->value = 'value';

        $requestInitiator = new RequestInitiator('https://whatever/', 'https://foo.bar/', [$attr]);

        $this->assertEquals('urn:oasis:names:tc:SAML:profiles:SSO:request-init', $requestInitiator->getBinding());
        $this->assertEquals('https://whatever/', $requestInitiator->getLocation());
        $this->assertEquals('https://foo.bar/', $requestInitiator->getResponseLocation());

        $this->assertTrue($requestInitiator->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $requestInitiator->getAttributeNS('urn:test', 'attr'));
        $this->assertFalse($requestInitiator->hasAttributeNS('urn:test', 'invalid'));
        $this->assertNull($requestInitiator->getAttributeNS('urn:test', 'invalid'));

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($requestInitiator));
    }


    // test unmarshalling


    /**
     * Test creating a RequestInitiator from XML.
     */
    public function testUnmarshalling(): void
    {
        $requestInitiator = RequestInitiator::fromXML($this->document->documentElement);

        $this->assertEquals($requestInitiator->getBinding(), 'urn:oasis:names:tc:SAML:profiles:SSO:request-init');
        $this->assertEquals($requestInitiator->getLocation(), 'https://whatever/');
        $this->assertEquals($requestInitiator->getResponseLocation(), 'https://foo.bar/');

        $this->assertTrue($requestInitiator->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $requestInitiator->getAttributeNS('urn:test', 'attr'));
        $this->assertFalse($requestInitiator->hasAttributeNS('urn:test', 'invalid'));
        $this->assertNull($requestInitiator->getAttributeNS('urn:test', 'invalid'));

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($requestInitiator));
    }


    /**
     * Test that creating a RequestInitiator from XML with an invalid Binding fails.
     */
    public function testUnmarshallingWithInvalidBinding(): void
    {
        $this->document->documentElement->setAttribute('Binding', 'urn:something');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'."
        );

        RequestInitiator::fromXML($this->document->documentElement);
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $requestInitiator = RequestInitiator::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($requestInitiator)))
        );
    }
}
