<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AttributeConsumingService;
use SimpleSAML\SAML2\XML\md\RequestedAttribute;
use SimpleSAML\SAML2\XML\md\ServiceDescription;
use SimpleSAML\SAML2\XML\md\ServiceName;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for the AttributeConsumingService class.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AttributeConsumingService::class)]
#[CoversClass(AbstractMdElement::class)]
final class AttributeConsumingServiceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\RequestedAttribute */
    private static RequestedAttribute $requestedAttribute;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AttributeConsumingService::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AttributeConsumingService.xml',
        );

        self::$requestedAttribute = new RequestedAttribute(
            Name: 'urn:oid:1.3.6.1.4.1.5923.1.1.1.7',
            NameFormat: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
            FriendlyName: 'eduPersonEntitlement',
            AttributeValues: [new AttributeValue('https://ServiceProvider.com/entitlements/123456789')],
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
            [self::$requestedAttribute],
            true,
            [new ServiceDescription('en', 'Academic Journals R US and only us')],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($acs),
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
            [self::$requestedAttribute],
            false,
        );

        $xmlRepresentation = clone self::$xmlRepresentation;
        $descr = $xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'ServiceDescription',
        );

        /**
         * @psalm-suppress PossiblyNullPropertyFetch
         * @var \DOMElement $space
         */
        $space = $descr->item(0)->previousSibling;

        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($descr->item(0));
        $xmlRepresentation->documentElement->removeChild($space);
        $xmlRepresentation->documentElement->setAttribute('isDefault', 'false');
        $this->assertEquals(
            $xmlRepresentation->saveXML($xmlRepresentation->documentElement),
            strval($acs),
        );
    }


    /**
     * Test that creating an AssertionConsumerService from scratch with isDefault works.
     */
    public function testMarshallingWithoutIsDefault(): void
    {
        $acs = new AttributeConsumingService(
            index: 2,
            serviceName: [new ServiceName('en', 'Academic Journals R US')],
            requestedAttribute: [self::$requestedAttribute],
            serviceDescription: [new ServiceDescription('en', 'Academic Journals R US and only us')],
        );
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('isDefault');
        $this->assertEquals(
            $xmlRepresentation->saveXML($xmlRepresentation->documentElement),
            strval($acs),
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
            [self::$requestedAttribute],
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
            [],
        );
    }


    // test unmarshalling


    /**
     * Test that creating an AssertionConsumerService from XML fails if index is missing.
     */
    public function testUnmarshallingWithoutIndex(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('index');
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'index\' attribute on md:AttributeConsumingService.');
        AttributeConsumingService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML works if isDefault is missing.
     */
    public function testUnmarshallingWithoutIsDefault(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('isDefault');
        $acs = AttributeConsumingService::fromXML($xmlRepresentation->documentElement);
        $this->assertNull($acs->getIsDefault());
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if isDefault is not boolean
     */
    public function testUnmarshallingWithWrongIsDefault(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('isDefault', 'xxx');
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage("The 'isDefault' attribute of md:AttributeConsumingService must be a boolean.");
        AttributeConsumingService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if index is not numerical.
     */
    public function testUnmarshallingWithNonNumericIndex(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('index', 'x');
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'index\' attribute of md:AttributeConsumingService must be numerical.');
        AttributeConsumingService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XMl fails if no ServiceName was provided.
     */
    public function testUnmarshallingWithoutServiceName(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $name = $xmlRepresentation->documentElement->getElementsByTagNameNS(C::NS_MD, 'ServiceName');
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($name->item(0));
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing at least one ServiceName in AttributeConsumingService.');
        AttributeConsumingService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if no RequestedAttribute was provided.
     */
    public function testUnmarshallingWithoutRequestedAttributes(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $reqAttr = $xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'RequestedAttribute',
        );
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($reqAttr->item(0));
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing at least one RequestedAttribute in AttributeConsumingService.');
        AttributeConsumingService::fromXML($xmlRepresentation->documentElement);
    }
}
