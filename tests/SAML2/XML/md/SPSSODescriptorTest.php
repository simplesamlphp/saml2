<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\SAML2\XML\md\AttributeConsumingService;
use SimpleSAML\SAML2\XML\md\ContactPerson;
use SimpleSAML\SAML2\XML\md\EmailAddress;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\ManageNameIDService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\SAML2\XML\md\OrganizationDisplayName;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
use SimpleSAML\SAML2\XML\md\RequestedAttribute;
use SimpleSAML\SAML2\XML\md\ServiceName;
use SimpleSAML\SAML2\XML\md\SingleLogoutService;
use SimpleSAML\SAML2\XML\md\SPSSODescriptor;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Tests for the md:SPSSODescriptor element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\SPSSODescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSSODescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptorType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSignedMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class SPSSODescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = SPSSODescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_SPSSODescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an SPSSODescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $slo1 = new SingleLogoutService(
            C::BINDING_SOAP,
            'https://ServiceProvider.com/SAML/SLO/SOAP',
        );
        $slo2 = new SingleLogoutService(
            C::BINDING_HTTP_REDIRECT,
            'https://ServiceProvider.com/SAML/SLO/Browser',
            'https://ServiceProvider.com/SAML/SLO/Response',
        );
        $acs1 = new AssertionConsumerService(
            0,
            C::BINDING_HTTP_ARTIFACT,
            'https://ServiceProvider.com/SAML/SSO/Artifact',
            true,
        );
        $acs2 = new AssertionConsumerService(
            1,
            C::BINDING_HTTP_POST,
            'https://ServiceProvider.com/SAML/SSO/POST',
        );
        $reqAttr = new RequestedAttribute(
            Name: 'urn:oid:1.3.6.1.4.1.5923.1.1.1.7',
            NameFormat: C::NAMEFORMAT_URI,
            FriendlyName: 'eduPersonEntitlement',
            AttributeValues: [new AttributeValue('https://ServiceProvider.com/entitlements/123456789')],
        );
        $attrcs1 = new AttributeConsumingService(
            0,
            [new ServiceName('en', 'Academic Journals R US')],
            [$reqAttr],
            true,
        );
        $attrcs2 = new AttributeConsumingService(
            1,
            [new ServiceName('en', 'Academic Journals R US')],
            [$reqAttr],
        );
        $extensions = new Extensions([
            new PublicationInfo(
                publisher: 'http://publisher.ra/',
                creationInstant: new DateTimeImmutable('2020-02-03T13:46:24Z'),
                usagePolicy: [new UsagePolicy('en', 'http://publisher.ra/policy.txt')],
            ),
        ]);
        $kd = new KeyDescriptor(new KeyInfo([new KeyName('ServiceProvider.com SSO Key')]), 'signing');
        $org = new Organization(
            [new OrganizationName('en', 'Identity Providers R US')],
            [new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.')],
            [new OrganizationURL('en', 'https://IdentityProvider.com')],
        );
        $contact = new ContactPerson(
            contactType: 'other',
            emailAddress: [new EmailAddress('john.doe@test.company')],
        );
        $ars = new ArtifactResolutionService(0, C::BINDING_HTTP_ARTIFACT, C::LOCATION_A);
        $mnids = new ManageNameIDService(C::BINDING_HTTP_POST, C::LOCATION_B);

        $spssod = new SPSSODescriptor(
            [$acs1, $acs2],
            [C::NS_SAMLP],
            true,
            false,
            [$attrcs1, $attrcs2],
            'someID',
            new DateTimeImmutable('2010-02-01T12:34:56Z'),
            'PT9000S',
            $extensions,
            'https://error.url/',
            [$kd],
            $org,
            [$contact],
            [$ars],
            [$slo1, $slo2],
            [$mnids],
            [new NameIDFormat(C::NAMEID_TRANSIENT)],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($spssod),
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
            [C::NS_SAMLP],
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch fails with an AssertionConsumerService of the wrong class.
     */
    public function testMarshallingWithWrongAssertionConsumerService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AssertionConsumerService endpoints must be an instance of AssertionConsumerService.',
        );

        /** @psalm-suppress InvalidArgument */
        new SPSSODescriptor(
            [new ArtifactResolutionService(0, C::BINDING_HTTP_POST, C::LOCATION_A)],
            [C::NS_SAMLP],
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch fails with an AttributeConsumingService of the wrong class.
     */
    public function testMarshallingWithWrongAttributeConsumingService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AttributeConsumingService endpoints must be an instance of AttributeConsumingService.',
        );

        /** @psalm-suppress InvalidArgument */
        new SPSSODescriptor(
            assertionConsumerService: [new AssertionConsumerService(0, C::BINDING_HTTP_POST, C::LOCATION_A)],
            protocolSupportEnumeration: [C::NS_SAMLP],
            authnRequestsSigned: true,
            attributeConsumingService: [new AssertionConsumerService(0, C::BINDING_HTTP_POST, C::LOCATION_B)],
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch works without any optional arguments.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $spssod = new SPSSODescriptor(
            [new AssertionConsumerService(0, C::BINDING_HTTP_POST, C::LOCATION_A)],
            [C::NS_SAMLP],
        );
        $this->assertNull($spssod->getAuthnRequestsSigned());
        $this->assertNull($spssod->getWantAssertionsSigned());
        $this->assertEmpty($spssod->getAttributeConsumingService());
    }


    // test unmarshalling


    /**
     * Test creating an SPSSODescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $spssod = SPSSODescriptor::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($spssod),
        );
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if no AssertionConsumerService is specified.
     */
    public function testUnmarshallingWithoutAssertionConsumerService(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $acseps = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AssertionConsumerService');

        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($acseps->item(1));

        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($acseps->item(0));

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one AssertionConsumerService must be specified.');

        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if AuthnRequestsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanAuthnRequestsSigned(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('AuthnRequestsSigned', 'not a boolean');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'AuthnRequestsSigned\' attribute of md:SPSSODescriptor must be a boolean.');

        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if WantAssertionsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanWantAssertionsSigned(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('WantAssertionsSigned', 'not a boolean');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'WantAssertionsSigned\' attribute of md:SPSSODescriptor must be a boolean.'
        );

        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML without any optional elements works.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = C::NS_MD;
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
        $this->assertEmpty($spssod->getAttributeConsumingService());
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails when more than one AttributeConsumingService is set to be
     * the default.
     */
    public function testUnmarshallingTwoDefaultACS(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $acs = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AttributeConsumingService');
        /** @psalm-suppress PossiblyNullReference */
        $acs->item(1)->setAttribute('isDefault', 'true');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Only one md:AttributeConsumingService can be set as default.');

        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }
}
