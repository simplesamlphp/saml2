<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Type\{
    AnyURIListValue,
    SAMLAnyURIValue,
    SAMLDateTimeValue,
    EmailAddressValue,
    KeyTypesValue,
    SAMLStringValue,
};
use SimpleSAML\SAML2\XML\md\{
    AbstractMdElement,
    AbstractMetadataDocument,
    AbstractRoleDescriptor,
    AbstractRoleDescriptorType,
    AbstractSignedMdElement,
    ArtifactResolutionService,
    AssertionConsumerService,
    AttributeConsumingService,
    ContactPerson,
    EmailAddress,
    Extensions,
    KeyDescriptor,
    KeyTypesEnum,
    ManageNameIDService,
    NameIDFormat,
    Organization,
    OrganizationDisplayName,
    OrganizationName,
    OrganizationURL,
    RequestedAttribute,
    ServiceName,
    SingleLogoutService,
    SPSSODescriptor,
};
use SimpleSAML\SAML2\XML\mdrpi\{PublicationInfo, UsagePolicy};
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\{BooleanValue, DurationValue, IDValue, LanguageValue, StringValue, UnsignedShortValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\{KeyInfo, KeyName};

use function dirname;
use function strval;

/**
 * Tests for the md:SPSSODescriptor element.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(SPSSODescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class SPSSODescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
            SAMLAnyURIValue::fromString(C::BINDING_SOAP),
            SAMLAnyURIValue::fromString('https://ServiceProvider.com/SAML/SLO/SOAP'),
        );
        $slo2 = new SingleLogoutService(
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_REDIRECT),
            SAMLAnyURIValue::fromString('https://ServiceProvider.com/SAML/SLO/Browser'),
            SAMLAnyURIValue::fromString('https://ServiceProvider.com/SAML/SLO/Response'),
        );
        $acs1 = new AssertionConsumerService(
            UnsignedShortValue::fromInteger(0),
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_ARTIFACT),
            SAMLAnyURIValue::fromString('https://ServiceProvider.com/SAML/SSO/Artifact'),
            BooleanValue::fromBoolean(true),
        );
        $acs2 = new AssertionConsumerService(
            UnsignedShortValue::fromInteger(1),
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
            SAMLAnyURIValue::fromString('https://ServiceProvider.com/SAML/SSO/POST'),
        );
        $reqAttr = new RequestedAttribute(
            Name: SAMLStringValue::fromString('urn:oid:1.3.6.1.4.1.5923.1.1.1.7'),
            NameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            FriendlyName: SAMLStringValue::fromString('eduPersonEntitlement'),
            AttributeValues: [
                new AttributeValue(
                    'https://ServiceProvider.com/entitlements/123456789',
                ),
            ],
        );
        $attrcs1 = new AttributeConsumingService(
            UnsignedShortValue::fromInteger(0),
            [
                new ServiceName(
                    LanguageValue::fromString('en'),
                    SAMLStringValue::fromString('Academic Journals R US'),
                ),
            ],
            [$reqAttr],
            BooleanValue::fromBoolean(true),
        );
        $attrcs2 = new AttributeConsumingService(
            UnsignedShortValue::fromInteger(1),
            [
                new ServiceName(
                    LanguageValue::fromString('en'),
                    SAMLStringValue::fromString('Academic Journals R US'),
                ),
            ],
            [$reqAttr],
        );
        $extensions = new Extensions([
            new PublicationInfo(
                publisher: SAMLStringValue::fromString('http://publisher.ra/'),
                creationInstant: SAMLDateTimeValue::fromString('2020-02-03T13:46:24Z'),
                usagePolicy: [
                    new UsagePolicy(
                        LanguageValue::fromString('en'),
                        SAMLAnyURIValue::fromString('http://publisher.ra/policy.txt'),
                    ),
                ],
            ),
        ]);
        $kd = new KeyDescriptor(
            new KeyInfo([
                new KeyName(
                    StringValue::fromString('ServiceProvider.com SSO Key'),
                ),
            ]),
            KeyTypesValue::fromEnum(KeyTypesEnum::SIGNING),
        );
        $org = new Organization(
            [
                new OrganizationName(
                    LanguageValue::fromString('en'),
                    SAMLStringValue::fromString('Identity Providers R US'),
                ),
            ],
            [
                new OrganizationDisplayName(
                    LanguageValue::fromString('en'),
                    SAMLStringValue::fromString('Identity Providers R US, a Division of Lerxst Corp.'),
                ),
            ],
            [
                new OrganizationURL(
                    LanguageValue::fromString('en'),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com'),
                ),
            ],
        );
        $contact = new ContactPerson(
            contactType: SAMLStringValue::fromString('other'),
            emailAddress: [
                new EmailAddress(
                    EmailAddressValue::fromString('john.doe@test.company'),
                ),
            ],
        );
        $ars = new ArtifactResolutionService(
            UnsignedShortValue::fromInteger(0),
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_ARTIFACT),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
        );
        $mnids = new ManageNameIDService(
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
            SAMLAnyURIValue::fromString(C::LOCATION_B),
        );

        $spssod = new SPSSODescriptor(
            [$acs1, $acs2],
            AnyURIListValue::fromString(C::NS_SAMLP),
            BooleanValue::fromBoolean(true),
            BooleanValue::fromBoolean(false),
            [$attrcs1, $attrcs2],
            IDValue::fromString('someID'),
            SAMLDateTimeValue::fromString('2010-02-01T12:34:56Z'),
            DurationValue::fromString('PT9000S'),
            $extensions,
            SAMLAnyURIValue::fromString('https://error.url/'),
            [$kd],
            $org,
            [$contact],
            [$ars],
            [$slo1, $slo2],
            [$mnids],
            [
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
                ),
            ],
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
            AnyURIListValue::fromString(C::NS_SAMLP),
        );
    }


    /**
     * Test that creating an SPSSODescriptor from scratch works without any optional arguments.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $spssod = new SPSSODescriptor(
            [
                new AssertionConsumerService(
                    UnsignedShortValue::fromInteger(0),
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString(C::LOCATION_A),
                ),
            ],
            AnyURIListValue::fromString(C::NS_SAMLP),
        );

        $this->assertNull($spssod->getAuthnRequestsSigned());
        $this->assertNull($spssod->getWantAssertionsSigned());
        $this->assertEmpty($spssod->getAttributeConsumingService());
    }


    // test unmarshalling


    /**
     * Test that creating an SPSSODescriptor from XML fails if no AssertionConsumerService is specified.
     */
    public function testUnmarshallingWithoutAssertionConsumerService(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $acseps = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AssertionConsumerService');

        $xmlRepresentation->documentElement->removeChild($acseps->item(1));
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

        $this->expectException(SchemaViolationException::class);
        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML fails if WantAssertionsSigned is not boolean.
     */
    public function testUnmarshallingWithNonBooleanWantAssertionsSigned(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('WantAssertionsSigned', 'not a boolean');

        $this->expectException(SchemaViolationException::class);
        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an SPSSODescriptor from XML without any optional elements works.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:SPSSODescriptor xmlns:md="{$mdns}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
  <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact"
    Location="https://ServiceProvider.com/SAML/SSO/Artifact" index="0" isDefault="true"/>
</md:SPSSODescriptor>
XML
            ,
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
        $acs->item(1)->setAttribute('isDefault', 'true');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'At most one <AttributeConsumingService> element can have the attribute isDefault set to true.',
        );

        SPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }
}
