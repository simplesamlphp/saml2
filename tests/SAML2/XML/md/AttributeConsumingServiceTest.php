<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AttributeConsumingService;
use SimpleSAML\SAML2\XML\md\RequestedAttribute;
use SimpleSAML\SAML2\XML\md\ServiceDescription;
use SimpleSAML\SAML2\XML\md\ServiceName;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Tests for the AttributeConsumingService class.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AttributeConsumingService
 * @package simplesamlphp/saml2
 */
final class AttributeConsumingServiceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AttributeConsumingService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_AttributeConsumingService.xml'
        );
    }


    /**
     * @return RequestedAttribute
     */
    protected function getRequestedAttribute(): RequestedAttribute
    {
        return new RequestedAttribute(
            'urn:oid:1.3.6.1.4.1.5923.1.1.1.7',
            null,
            'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
            'eduPersonEntitlement',
            [new AttributeValue('https://ServiceProvider.com/entitlements/123456789')]
        );
    }


    // test marshalling


    /**
     * Test creating an AssertionConsumerService from scratch.
     */
    public function testMarshalling(): void
    {
        $acs = new AttributeConsumingService(
            2,
            [new ServiceName('en', 'Academic Journals R US')],
            [$this->getRequestedAttribute()],
            true,
            [new ServiceDescription('en', 'Academic Journals R US and only us')]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($acs)
        );
    }


    /**
     * Test that creating an AssertionConsumerService from scratch without description works.
     */
    public function testMarshallingWithoutDescription(): void
    {
        $acs = new AttributeConsumingService(
            2,
            [new ServiceName('en', 'Academic Journals R US')],
            [$this->getRequestedAttribute()],
            false
        );

        $descr = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            Constants::NS_MD,
            'ServiceDescription'
        );

        /**
         * @psalm-suppress PossiblyNullPropertyFetch
         * @var \DOMElement $space
         */
        $space = $descr->item(0)->previousSibling;

        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($descr->item(0));
        $this->xmlRepresentation->documentElement->removeChild($space);
        $this->xmlRepresentation->documentElement->setAttribute('isDefault', 'false');
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($acs)
        );
    }


    /**
     * Test that creating an AssertionConsumerService from scratch with isDefault works.
     */
    public function testMarshallingWithoutIsDefault(): void
    {
        $acs = new AttributeConsumingService(
            2,
            [new ServiceName('en', 'Academic Journals R US')],
            [$this->getRequestedAttribute()],
            null,
            [new ServiceDescription('en', 'Academic Journals R US and only us')]
        );
        $this->xmlRepresentation->documentElement->removeAttribute('isDefault');
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($acs)
        );
    }


    /**
     * Test that creating an AssertionConsumerService from scratch with no ServiceName fails.
     */
    public function testMarshallingWithEmptyServiceName(): void
    {
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing at least one ServiceName in AttributeConsumingService.');
        new AttributeConsumingService(
            2,
            [],
            [$this->getRequestedAttribute()]
        );
    }


    /**
     * Test that creating an AssertionConsumerService from scratch with no RequestedAttribute fails.
     */
    public function testMarshallingWithEmptyRequestedAttributes(): void
    {
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing at least one RequestedAttribute in AttributeConsumingService.');
        new AttributeConsumingService(
            2,
            [new ServiceName('en', 'Academic Journals R US')],
            []
        );
    }


    // test unmarshalling


    /**
     * Test creating an AssertionConsumerService from XML.
     */
    public function testUnmarshalling(): void
    {
        $acs = AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(2, $acs->getIndex());
        $this->assertTrue($acs->getIsDefault());
        $svcNames = $acs->getServiceNames();
        $this->assertCount(1, $svcNames);
        $svcDescr = $acs->getServiceDescriptions();
        $this->assertCount(1, $svcDescr);
        $reqAttr = $acs->getRequestedAttributes();
        $this->assertCount(1, $reqAttr);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if index is missing.
     */
    public function testUnmarshallingWithoutIndex(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('index');
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'index\' attribute on md:AttributeConsumingService.');
        AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML works if isDefault is missing.
     */
    public function testUnmarshallingWithoutIsDefault(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('isDefault');
        $acs = AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
        $this->assertNull($acs->getIsDefault());
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if isDefault is not boolean
     */
    public function testUnmarshallingWithWrongIsDefault(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('isDefault', 'xxx');
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage("The 'isDefault' attribute of md:AttributeConsumingService must be boolean.");
        AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if index is not numerical.
     */
    public function testUnmarshallingWithNonNumericIndex(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('index', 'x');
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'index\' attribute of md:AttributeConsumingService must be numerical.');
        AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XMl fails if no ServiceName was provided.
     */
    public function testUnmarshallingWithoutServiceName(): void
    {
        $name = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'ServiceName');
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($name->item(0));
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing at least one ServiceName in AttributeConsumingService.');
        AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if no RequestedAttribute was provided.
     */
    public function testUnmarshallingWithoutRequestedAttributes(): void
    {
        $reqAttr = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            Constants::NS_MD,
            'RequestedAttribute'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($reqAttr->item(0));
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing at least one RequestedAttribute in AttributeConsumingService.');
        AttributeConsumingService::fromXML($this->xmlRepresentation->documentElement);
    }
}
