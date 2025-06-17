<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{AnyURIListValue, SAMLAnyURIValue, KeyTypesValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\md\{
    AbstractMdElement,
    AbstractMetadataDocument,
    AbstractRoleDescriptor,
    AbstractRoleDescriptorType,
    AbstractSignedMdElement,
    ArtifactResolutionService,
    AssertionIDRequestService,
    AttributeProfile,
    IDPSSODescriptor,
    KeyDescriptor,
    KeyTypesEnum,
    ManageNameIDService,
    NameIDFormat,
    NameIDMappingService,
    SingleLogoutService,
    SingleSignOnService,
};
use SimpleSAML\SAML2\XML\saml\{Attribute, AttributeValue};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\{BooleanValue, IDValue, UnsignedShortValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\{KeyInfo, KeyName};

use function dirname;
use function strval;

/**
 * Tests for IDPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(IDPSSODescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class IDPSSODescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = IDPSSODescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_IDPSSODescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an IDPSSODescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $idpssod = new IDPSSODescriptor(
            ID: IDValue::fromString('phpunit'),
            singleSignOnService: [
                new SingleSignOnService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_REDIRECT),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
                new SingleSignOnService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
            ],
            protocolSupportEnumeration: AnyURIListValue::fromString(C::NS_SAMLP),
            wantAuthnRequestsSigned: BooleanValue::fromBoolean(true),
            nameIDMappingService: [
                new NameIDMappingService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_REDIRECT),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
                new NameIDMappingService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
            ],
            assertionIDRequestService: [
                new AssertionIDRequestService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_REDIRECT),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
                new AssertionIDRequestService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
            ],
            attributeProfile: [
                new AttributeProfile(
                    SAMLAnyURIValue::fromString('urn:attribute:profile1'),
                ),
                new AttributeProfile(
                    SAMLAnyURIValue::fromString('urn:attribute:profile2'),
                ),
            ],
            attribute: [
                new Attribute(
                    SAMLStringValue::fromString('urn:oid:1.3.6.1.4.1.5923.1.1.1.6'),
                    SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                    SAMLStringValue::fromString('eduPersonPrincipalName'),
                ),
                new Attribute(
                    SAMLStringValue::fromString('urn:oid:1.3.6.1.4.1.5923.1.1.1.1'),
                    SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                    SAMLStringValue::fromString('eduPersonAffiliation'),
                    [
                        new AttributeValue('member'),
                        new AttributeValue('student'),
                        new AttributeValue('faculty'),
                        new AttributeValue('employee'),
                        new AttributeValue('staff'),
                    ],
                ),
            ],
            keyDescriptor: [
                new KeyDescriptor(
                    new KeyInfo([
                        new KeyName(
                            SAMLStringValue::fromString('IdentityProvider.com SSO Key'),
                        ),
                    ]),
                    KeyTypesValue::fromEnum(KeyTypesEnum::SIGNING),
                ),
            ],
            artifactResolutionService: [
                new ArtifactResolutionService(
                    UnsignedShortValue::fromInteger(0),
                    SAMLAnyURIValue::fromString(C::BINDING_SOAP),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/Artifact'),
                    BooleanValue::fromBoolean(true),
                ),
            ],
            singleLogoutService: [
                new SingleLogoutService(
                    SAMLAnyURIValue::fromString(C::BINDING_SOAP),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SLO/SOAP'),
                ),
                new SingleLogoutService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_REDIRECT),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SLO/Browser'),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SLO/Response'),
                ),
            ],
            manageNameIDService: [
                new ManageNameIDService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/SSO/Browser'),
                ),
            ],
            nameIDFormat: [
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_X509_SUBJECT_NAME),
                ),
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_PERSISTENT),
                ),
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($idpssod),
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if no SingleSignOnService endpoints are provided.
     */
    public function testMarshallingWithEmptySingleSignOnService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one SingleSignOnService must be specified.');
        new IDPSSODescriptor([], AnyURIListValue::fromString(C::NS_SAMLP));
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if no protocol is passed.
     */
    public function testMarshallingWithoutProtocolSupportThrowsException(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'SAML v2.0 entities MUST include the SAML protocol namespace URI '
            . 'in their protocolSupportEnumeration attribute',
        );

        new IDPSSODescriptor(
            [
                new SingleSignOnService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString(C::LOCATION_A),
                ),
            ],
            AnyURIListValue::fromString(''),
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch works if no optional arguments are provided.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $idpssod = new IDPSSODescriptor(
            [
                new SingleSignOnService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
                    SAMLAnyURIValue::fromString(C::LOCATION_A),
                ),
                new SingleSignOnService(
                    SAMLAnyURIValue::fromString(C::BINDING_HTTP_REDIRECT),
                    SAMLAnyURIValue::fromString(C::LOCATION_B),
                ),
            ],
            AnyURIListValue::fromArray([C::NS_SAMLP, C::PROTOCOL]),
        );
        $this->assertNull($idpssod->wantAuthnRequestsSigned());
        $this->assertEquals([], $idpssod->getNameIDMappingService());
        $this->assertEquals([], $idpssod->getAssertionIDRequestService());
        $this->assertEquals([], $idpssod->getSupportedAttribute());
    }


    // test unmarshalling


    /**
     * Test that creating an IDPSSODescriptor from XML fails if no SingleSignOnService endpoint is provided.
     */
    public function testUnmarshallingWithoutSingleSignOnService(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $ssoServiceEps = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'SingleSignOnService');
        $xmlRepresentation->documentElement->removeChild($ssoServiceEps->item(1));
        $xmlRepresentation->documentElement->removeChild($ssoServiceEps->item(0));

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one SingleSignOnService must be specified.');

        IDPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if an empty AttributeProfile is provided.
     */
    public function testUnmarshallingWithEmptyAttributeProfile(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $attrProfiles = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AttributeProfile');
        $attrProfiles->item(0)->textContent = '';

        $this->expectException(ProtocolViolationException::class);

        IDPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML works if no optional elements are provided.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:IDPSSODescriptor xmlns:md="{$mdns}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
  <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
      Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
</md:IDPSSODescriptor>
XML
            ,
        );
        $idpssod = IDPSSODescriptor::fromXML($document->documentElement);
        $this->assertCount(1, $idpssod->getSingleSignOnService());
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnService()[0]);
        $this->assertNull($idpssod->wantAuthnRequestsSigned());
        $this->assertEquals([], $idpssod->getNameIDMappingService());
        $this->assertEquals([], $idpssod->getAssertionIDRequestService());
        $this->assertEquals([], $idpssod->getAttributeProfile());
        $this->assertEquals([], $idpssod->getSupportedAttribute());
    }
}
