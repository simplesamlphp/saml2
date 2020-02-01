<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\AttributeValue;

/**
 * Tests for the AttributeConsumingService class.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeConsumingServiceTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $samlns = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeConsumingService xmlns:md="{$mdns}" index="2" isDefault="true">
  <md:ServiceName xml:lang="en">Academic Journals R US</md:ServiceName>
  <md:ServiceDescription xml:lang="en">Academic Journals R US and only us</md:ServiceDescription>
  <md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri" FriendlyName="eduPersonEntitlement">
    <saml:AttributeValue xmlns:saml="{$samlns}" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">https://ServiceProvider.com/entitlements/123456789</saml:AttributeValue>
  </md:RequestedAttribute>
</md:AttributeConsumingService>
XML
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
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($acs));
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
        $descr = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'ServiceDescription');
        $space = $descr->item(0)->previousSibling;
        $this->document->documentElement->removeChild($descr->item(0));
        $this->document->documentElement->removeChild($space);
        $this->document->documentElement->setAttribute('isDefault', 'false');
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($acs));
    }


    /**
     * test that creating an AssertionConsumerService from scratch with an empty description fails.
     */
    public function testMarshallingWithEmptyDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service descriptions must be specified as ServiceDescription objects.');
        new AttributeConsumingService(
            2,
            [new ServiceName('en', 'Academic Journals R US')],
            [$this->getRequestedAttribute()],
            true,
            ['']
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
        $this->document->documentElement->removeAttribute('isDefault');
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($acs));
    }


    /**
     * Test that creating an AssertionConsumerService from scratch with no ServiceName fails.
     */
    public function testMarshallingWithEmptyServiceName(): void
    {
        $this->expectException(Exception::class);
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
        $this->expectException(Exception::class);
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
        $acs = AttributeConsumingService::fromXML($this->document->documentElement);
        $this->assertEquals(2, $acs->getIndex());
        $this->assertTrue($acs->getIsDefault());
        $svcNames = $acs->getServiceNames();
        $this->assertCount(1, $svcNames);
        $this->assertInstanceOf(ServiceName::class, $svcNames[0]);
        $svcDescr = $acs->getServiceDescriptions();
        $this->assertCount(1, $svcDescr);
        $this->assertInstanceOf(ServiceDescription::class, $svcDescr[0]);
        $reqAttr = $acs->getRequestedAttributes();
        $this->assertCount(1, $reqAttr);
        $this->assertInstanceOf(RequestedAttribute::class, $reqAttr[0]);
    }


    /**
     * Test that creating an AssertionConsumerService from XML does not require a description.
     */
    public function testUnmarshallingWithoutDescription(): void
    {
        $descr = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'ServiceDescription');
        $this->document->documentElement->removeChild($descr->item(0));
        $acs = AttributeConsumingService::fromXML($this->document->documentElement);
        $this->assertEmpty($acs->getServiceDescriptions());
    }


    /**
     * Test that creating an AssertionConsumerService from XML works if description is empty.
     */
    public function testUnmarshallingWithEmptyDescription(): void
    {
        $descr = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'ServiceDescription');
        $descr->item(0)->textContent = '';
        $acs = AttributeConsumingService::fromXML($this->document->documentElement);
        $svcDescr = $acs->getServiceDescriptions();
        $this->assertCount(1, $acs->getServiceDescriptions());
        $this->assertEmpty($svcDescr[0]->getValue());
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if index is missing.
     */
    public function testUnmarshallingWithoutIndex(): void
    {
        $this->document->documentElement->removeAttribute('index');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing \'index\' attribute from md:AttributeConsumingService.');
        AttributeConsumingService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML works if isDefault is missing.
     */
    public function testUnmarshallingWithoutIsDefault(): void
    {
        $this->document->documentElement->removeAttribute('isDefault');
        $acs = AttributeConsumingService::fromXML($this->document->documentElement);
        $this->assertNull($acs->getIsDefault());
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if isDefault is not boolean
     */
    public function testUnmarshallingWithWrongIsDefault(): void
    {
        $this->document->documentElement->setAttribute('isDefault', 'xxx');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The 'isDefault' attribute of md:AttributeConsumingService must be boolean.");
        AttributeConsumingService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if index is not numerical.
     */
    public function testUnmarshallingWithNonNumericIndex(): void
    {
        $this->document->documentElement->setAttribute('index', 'x');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The \'index\' attribute of md:AttributeConsumingService must be numerical.');
        AttributeConsumingService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XMl fails if no ServiceName was provided.
     */
    public function testUnmarshallingWithoutServiceName(): void
    {
        $name = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'ServiceName');
        $this->document->documentElement->removeChild($name->item(0));
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing at least one ServiceName in AttributeConsumingService.');
        AttributeConsumingService::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an AssertionConsumerService from XML fails if no RequestedAttribute was provided.
     */
    public function testUnmarshallingWithoutRequestedAttributes(): void
    {
        $reqAttr = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'RequestedAttribute');
        $this->document->documentElement->removeChild($reqAttr->item(0));
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing at least one RequestedAttribute in AttributeConsumingService.');
        AttributeConsumingService::fromXML($this->document->documentElement);
    }


    /**
     * Test that serialization works.
     */
    public function testSerialization(): void
    {
        $acs = AttributeConsumingService::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($acs)))
        );
    }
}
