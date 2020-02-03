<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\AbstractXMLElement;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\ds\KeyName;
use SAML2\XML\saml\AttributeValue;

/**
 * Tests for the md:SPSSODescriptor element.
 *
 * @package simplesamlphp/saml2
 */
class SPSSODescriptorTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $dsns = XMLSecurityDSig::XMLDSIGNS;
        $samlns = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:SPSSODescriptor xmlns:md="{$mdns}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" AuthnRequestsSigned="true">
  <md:KeyDescriptor use="signing">
    <ds:KeyInfo xmlns:ds="{$dsns}">
      <ds:KeyName>ServiceProvider.com SSO Key</ds:KeyName>
    </ds:KeyInfo>
  </md:KeyDescriptor>
  <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://ServiceProvider.com/SAML/SLO/SOAP"/>
  <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://ServiceProvider.com/SAML/SLO/Browser" ResponseLocation="https://ServiceProvider.com/SAML/SLO/Response"/>
  <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
  <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact" Location="https://ServiceProvider.com/SAML/SSO/Artifact" index="0" isDefault="true"/>
  <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://ServiceProvider.com/SAML/SSO/POST" index="1"/>
  <md:AttributeConsumingService index="0">
    <md:ServiceName xml:lang="en">Academic Journals R US</md:ServiceName>
    <md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri" FriendlyName="eduPersonEntitlement">
      <saml:AttributeValue xmlns:saml="{$samlns}" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string">https://ServiceProvider.com/entitlements/123456789</saml:AttributeValue>
    </md:RequestedAttribute>
  </md:AttributeConsumingService>
</md:SPSSODescriptor>
XML
        );


    }


    /**
     * Test creating an SPSSODescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $slo1 = new SingleLogoutService(
            'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
            'https://ServiceProvider.com/SAML/SLO/SOAP'
        );
        $slo2 = new SingleLogoutService(
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'https://ServiceProvider.com/SAML/SLO/Browser',
            'https://ServiceProvider.com/SAML/SLO/Response'
        );
        $acs1 = new AssertionConsumerService(
            0,
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
            'https://ServiceProvider.com/SAML/SSO/Artifact',
            true
        );
        $acs2 = new AssertionConsumerService(
            1,
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'https://ServiceProvider.com/SAML/SSO/POST'
        );
        $reqAttr = new RequestedAttribute(
            'urn:oid:1.3.6.1.4.1.5923.1.1.1.7',
            null,
            'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
            'eduPersonEntitlement',
            [new AttributeValue('https://ServiceProvider.com/entitlements/123456789')]
        );
        $attrcs = new AttributeConsumingService(
            0,
            [new ServiceName('en', 'Academic Journals R US')],
            [$reqAttr]
        );
        $kd = new KeyDescriptor(new KeyInfo([new KeyName('ServiceProvider.com SSO Key')]), 'signing');
        $spssod = new SPSSODescriptor(
            [$acs1, $acs2],
            ['urn:oasis:names:tc:SAML:2.0:protocol'],
            true,
            null,
            [$attrcs],
            null,
            null,
            null,
            null,
            null,
            [$kd],
            null,
            null,
            null,
            [$slo1, $slo2],
            null,
            ['urn:oasis:names:tc:SAML:2.0:nameid-format:transient']
        );
        $this->assertCount(2, $spssod->getAssertionConsumerService());
        $this->assertInstanceOf(AssertionConsumerService::class, $spssod->getAssertionConsumerService()[0]);
        $this->assertInstanceOf(AssertionConsumerService::class, $spssod->getAssertionConsumerService()[1]);
        $this->assertTrue($spssod->getAuthnRequestsSigned());
        $this->assertNull($spssod->getWantAssertionsSigned());
        $this->assertCount(1, $spssod->getAttributeConsumingService());
        $this->assertInstanceOf(AttributeConsumingService::class, $spssod->getAttributeConsumingService()[0]);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($spssod)
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch fails without an AssertionConsumerService.
     */
    public function testMarshallingWithoutAssertionConsumerService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one AssertionConsumerService must be specified.');
        new SPSSODescriptor(
            [],
            ['urn:oasis:names:tc:SAML:2.0:protocol']
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch fails with an AssertionConsumerService of the wrong class.
     */
    public function testMarshallingWithWrongAssertionConsumerService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'All md:AssertionConsumerService endpoints must be an instance of AssertionConsumerService.'
        );
        new SPSSODescriptor(
            [new ArtifactResolutionService(0, 'x', 'x')],
            ['x']
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch fails with an AttributeConsumingService of the wrong class.
     */
    public function testMarshallingWithWrongAttributeConsumingService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'All md:AttributeConsumingService endpoints must be an instance of AttributeConsumingService.'
        );
        new SPSSODescriptor(
            [new AssertionConsumerService(0, 'x', 'x')],
            ['x'],
            true,
            null,
            [new AssertionConsumerService(0, 'x', 'x')]
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch works without any optional arguments.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $spssod = new SPSSODescriptor(
            [new AssertionConsumerService(0, 'x', 'x')],
            ['x']
        );
        $this->assertNull($spssod->getAuthnRequestsSigned());
        $this->assertNull($spssod->getWantAssertionsSigned());
        $this->assertIsArray($spssod->getAttributeConsumingService());
        $this->assertEmpty($spssod->getAttributeConsumingService());
    }


    /**
     * Test creating an SPSSODescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $spssod = SPSSODescriptor::fromXML($this->document->documentElement);
        $this->assertCount(2, $spssod->getAssertionConsumerService());
        $this->assertInstanceOf(AssertionConsumerService::class, $spssod->getAssertionConsumerService()[0]);
        $this->assertInstanceOf(AssertionConsumerService::class, $spssod->getAssertionConsumerService()[1]);
        $this->assertTrue($spssod->getAuthnRequestsSigned());
        $this->assertNull($spssod->getWantAssertionsSigned());
        $this->assertCount(1, $spssod->getAttributeConsumingService());
        $this->assertInstanceOf(AttributeConsumingService::class, $spssod->getAttributeConsumingService()[0]);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($spssod)
        );
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if no AssertionConsumerService is specified.
     */
    public function testUnmarshallingWithoutAssertionConsumerService(): void
    {
        $acseps = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'AssertionConsumerService');
        $this->document->documentElement->removeChild($acseps->item(1));
        $this->document->documentElement->removeChild($acseps->item(0));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one AssertionConsumerService must be specified.');
        SPSSODescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if AuthnRequestsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanAuthnRequestsSigned(): void
    {
        $this->document->documentElement->setAttribute('AuthnRequestsSigned', 'not a boolean');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'AuthnRequestsSigned\' attribute of md:SPSSODescriptor must be boolean.');
        SPSSODescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if WantAssertionsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanWantAssertionsSigned(): void
    {
        $this->document->documentElement->setAttribute('WantAssertionsSigned', 'not a boolean');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'WantAssertionsSigned\' attribute of md:SPSSODescriptor must be boolean.');
        SPSSODescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML without any optional elements works.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:SPSSODescriptor xmlns:md="{$mdns}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
  <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact" Location="https://ServiceProvider.com/SAML/SSO/Artifact" index="0" isDefault="true"/>
</md:SPSSODescriptor>
XML
        );
        $spssod = SPSSODescriptor::fromXML($document->documentElement);
        $this->assertNull($spssod->getAuthnRequestsSigned());
        $this->assertNull($spssod->getWantAssertionsSigned());
        $this->assertIsArray($spssod->getAttributeConsumingService());
        $this->assertEmpty($spssod->getAttributeConsumingService());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(SPSSODescriptor::fromXML($this->document->documentElement))))
        );
    }
}
