<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\SAML2\SignedElementTestTrait;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ds\KeyInfo;
use SimpleSAML\SAML2\XML\ds\KeyName;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for the md:SPSSODescriptor element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\SPSSODescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSSODescriptor
 * @package simplesamlphp/saml2
 */
final class SPSSODescriptorTest extends TestCase
{
    use SignedElementTestTrait;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_SPSSODescriptor.xml'
        );
        $this->testedClass = SPSSODescriptor::class;
    }


    // test marshalling


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
        $attrcs1 = new AttributeConsumingService(
            0,
            [new ServiceName('en', 'Academic Journals R US')],
            [$reqAttr],
            true
        );
        $attrcs2 = new AttributeConsumingService(
            1,
            [new ServiceName('en', 'Academic Journals R US')],
            [$reqAttr]
        );
        $extensions = new Extensions([
            new PublicationInfo(
                'http://publisher.ra/',
                Utils::xsDateTimeToTimestamp('2020-02-03T13:46:24Z'),
                null,
                ['en' => 'http://publisher.ra/policy.txt']
            )
        ]);
        $kd = new KeyDescriptor(new KeyInfo([new KeyName('ServiceProvider.com SSO Key')]), 'signing');
        $org = new Organization(
            [new OrganizationName('en', 'Identity Providers R US')],
            [new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.')],
            ['en' => 'https://IdentityProvider.com']
        );
        $contact = new ContactPerson(
            'other',
            null,
            null,
            null,
            null,
            ['john.doe@test.company']
        );
        $ars = new ArtifactResolutionService(0, 'binding1', 'location1');
        $mnids = new ManageNameIDService('binding1', 'location1');

        $spssod = new SPSSODescriptor(
            [$acs1, $acs2],
            ['urn:oasis:names:tc:SAML:2.0:protocol'],
            true,
            false,
            [$attrcs1, $attrcs2],
            'someID',
            Utils::xsDateTimeToTimestamp('2010-02-01T12:34:56Z'),
            'PT9000S',
            $extensions,
            'https://error.url/',
            [$kd],
            $org,
            [$contact],
            [$ars],
            [$slo1, $slo2],
            [$mnids],
            ['urn:oasis:names:tc:SAML:2.0:nameid-format:transient']
        );

        $this->assertCount(2, $spssod->getAssertionConsumerService());
        $this->assertInstanceOf(AssertionConsumerService::class, $spssod->getAssertionConsumerService()[0]);
        $this->assertInstanceOf(AssertionConsumerService::class, $spssod->getAssertionConsumerService()[1]);
        $this->assertTrue($spssod->getAuthnRequestsSigned());
        $this->assertFalse($spssod->getWantAssertionsSigned());
        $this->assertCount(2, $spssod->getAttributeConsumingService());
        $this->assertInstanceOf(AttributeConsumingService::class, $spssod->getAttributeConsumingService()[0]);
        $this->assertInstanceOf(AttributeConsumingService::class, $spssod->getAttributeConsumingService()[1]);

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
        $this->expectException(AssertionFailedException::class);
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
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AssertionConsumerService endpoints must be an instance of AssertionConsumerService.'
        );

        /** @psalm-suppress InvalidArgument */
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
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AttributeConsumingService endpoints must be an instance of AttributeConsumingService.'
        );

        /** @psalm-suppress InvalidArgument */
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


    // test unmarshalling


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
        $this->assertFalse($spssod->getWantAssertionsSigned());
        $this->assertCount(2, $spssod->getAttributeConsumingService());
        $this->assertInstanceOf(AttributeConsumingService::class, $spssod->getAttributeConsumingService()[0]);
        $this->assertInstanceOf(AttributeConsumingService::class, $spssod->getAttributeConsumingService()[1]);
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

        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($acseps->item(1));

        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($acseps->item(0));

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one AssertionConsumerService must be specified.');

        SPSSODescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if AuthnRequestsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanAuthnRequestsSigned(): void
    {
        $this->document->documentElement->setAttribute('AuthnRequestsSigned', 'not a boolean');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'AuthnRequestsSigned\' attribute of md:SPSSODescriptor must be boolean.');

        SPSSODescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if WantAssertionsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanWantAssertionsSigned(): void
    {
        $this->document->documentElement->setAttribute('WantAssertionsSigned', 'not a boolean');

        $this->expectException(AssertionFailedException::class);
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
  <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
    Location="https://ServiceProvider.com/SAML/SSO/Artifact" index="0" isDefault="true"/>
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
     * Test that creating an SPSSODescriptor from XML fails when more than one AttributeConsumingService is set to be
     * the default.
     */
    public function testUnmarshallingTwoDefaultACS(): void
    {
        $acs = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'AttributeConsumingService');
        /** @psalm-suppress PossiblyNullReference */
        $acs->item(1)->setAttribute('isDefault', 'true');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Only one md:AttributeConsumingService can be set as default.');

        SPSSODescriptor::fromXML($this->document->documentElement);
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
