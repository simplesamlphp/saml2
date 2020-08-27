<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Exception\MissingAttributeException;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\md\IndexedEndpointTypeTest
 *
 * @covers \SAML2\XML\md\IndexedEndpointType
 * @package simplesamlphp/saml2
 */
final class IndexedEndpointTypeTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdNamespace = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:AssertionConsumerService xmlns:md="{$mdNamespace}" Binding="urn:something"
    Location="https://whatever/" index="42" isDefault="false" />
XML
        );
    }


    // test marshalling


    /**
     * Test creating an IndexedEndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $idxep = new AssertionConsumerService(42, 'urn:something', 'https://whatever/', false);

        $this->assertEquals(42, $idxep->getIndex());
        $this->assertFalse($idxep->getIsDefault());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($idxep));
    }


    /**
     * Test that creating an IndexedEndpointType from scratch without specifying isDefault works.
     */
    public function testMarshallingWithoutIsDefault(): void
    {
        $idxep = new AssertionConsumerService(42, 'urn:something', 'https://whatever/');
        $this->document->documentElement->removeAttribute('isDefault');
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($idxep));
        $this->assertNull($idxep->getIsDefault());
    }


    // test unmarshalling


    /**
     * Test creating an IndexedEndpointType from XML.
     */
    public function testUnmarshalling(): void
    {
        $idxep = AssertionConsumerService::fromXML($this->document->documentElement);
        $this->assertEquals(42, $idxep->getIndex());
        $this->assertFalse($idxep->getIsDefault());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($idxep));
    }


    /**
     * Test that creating an EndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AssertionConsumerService. Expected: ArtifactResolutionService.'
        );
        ArtifactResolutionService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without an index fails.
     */
    public function testUnmarshallingWithoutIndex(): void
    {
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'index\' attribute on md:AssertionConsumerService');
        $this->document->documentElement->removeAttribute('index');
        AssertionConsumerService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML with a non-numeric index fails.
     */
    public function testUnmarshallingWithWrongIndex(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'index\' attribute of md:AssertionConsumerService must be numerical.');
        $this->document->documentElement->setAttribute('index', 'value');
        AssertionConsumerService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without isDefault works.
     */
    public function testUnmarshallingWithoutIsDefault(): void
    {
        $this->document->documentElement->removeAttribute('isDefault');
        AssertionConsumerService::fromXML($this->document->documentElement);
        $this->assertTrue(true);
    }


    /**
     * Test that creating an IndexedEndpointType from XML with isDefault of a non-boolean value fails.
     */
    public function testUnmarshallingWithWrongIsDefault(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'isDefault\' attribute of md:AssertionConsumerService must be boolean.');
        $this->document->documentElement->setAttribute('isDefault', 'non-bool');
        AssertionConsumerService::fromXML($this->document->documentElement);
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $ep = AssertionConsumerService::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($ep)))
        );
    }
}
